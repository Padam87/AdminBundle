<?php

namespace Padam87\AdminBundle\Listing;

use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use Knp\Bundle\PaginatorBundle\Helper\Processor;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface;
use Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationRuntime;
use Padam87\AdminBundle\Config\Action\Action;
use Padam87\AdminBundle\Config\AdminConfig;
use Padam87\AdminBundle\Config\IconClass;
use Padam87\AdminBundle\Config\Table\Column\Column;
use Padam87\AdminBundle\Config\Table\Column\CombinedColumn;
use Padam87\AdminBundle\Config\Table\Column\TableColumn;
use Padam87\AdminBundle\Config\Table\Column\TwigColumn;
use Padam87\AdminBundle\Config\Table\FilterSet;
use Padam87\AdminBundle\Config\Table\Table;
use Padam87\AdminBundle\Serializer\ListSerializer;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Converts an admin list (config + table + pagination) to data for a JS rendered list.
 *
 * Columns and actions are serialized by JMS (metadata on the column / action classes, see ListSerializer),
 * rows carry the normalized column values only (Column::getListValue, see ValueNormalizer).
 */
class ListBuilder
{
    /**
     * Marker block, index templates defining it render a serialized list.
     */
    public const BLOCK = 'admin_list';

    public function __construct(
        private Environment $twig,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private PropertyAccessorInterface $propertyAccessor,
        private ValueNormalizer $normalizer,
        private ArrayTransformerInterface $serializer,
        private SerializationContextFactoryInterface $contextFactory,
        private Processor $processor,
        private PaginationRuntime $paginationRuntime,
        private RequestStack $requestStack,
        private IconClass $iconClass,
        private ?ListCustomizerInterface $customizer = null,
    ) {
    }

    public function supports(AdminConfig $config): bool
    {
        return $this->twig->load($config->getTemplates()[Action::INDEX])->hasBlock(self::BLOCK);
    }

    /**
     * @param FormView|null $filters view of the filter form
     */
    public function build(AdminConfig $config, Table $table, SlidingPaginationInterface $pagination, ?FormView $filters = null): array
    {
        $paginationData = $pagination->getPaginationData();

        return [
            'title' => $this->translator->trans('list.title', ['%entity%' => $this->trans($config->getPluralName())], 'padam87_admin'),
            'info' => $this->translator->trans('list.info', [
                '%currentPage%' => $paginationData['current'],
                '%pageCount%' => $paginationData['pageCount'],
                '%itemCount%' => $paginationData['totalCount'],
            ], 'padam87_admin'),
            'pagination' => $this->pagination($pagination),
            'resetUrl' => $this->urlGenerator->generate($config->getRouteNameForAction(Action::INDEX), ['reset' => true]),
            'actions' => [
                Action::TYPE_GLOBAL => $this->actions($config, Action::TYPE_GLOBAL),
                Action::TYPE_BATCH => $this->actions($config, Action::TYPE_BATCH),
                Action::TYPE_TABLE => $this->actions($config, Action::TYPE_TABLE),
            ],
            'filters' => $filters !== null && count($filters->children) > 0 ? $this->filters($filters) : null,
            'filterSets' => $this->filterSets($config, $table),
            'columns' => $this->columns($config, $table, $pagination),
            'rows' => $this->rows($config, $table, $pagination),
            'batch' => count($config->getActions(Action::TYPE_BATCH)) > 0,
            ...($this->customizer?->getListData($config, $table, $pagination) ?? []),
        ];
    }

    /**
     * @return Column[]
     */
    public function shownColumns(AdminConfig $config, Table $table): array
    {
        $selected = $this->customizer?->getSelectedColumns($config, $table);

        if ($selected === null) {
            return $table->getColumns();
        }

        return array_filter($table->getColumns(), fn (string $name) => in_array($name, $selected, true), ARRAY_FILTER_USE_KEY);
    }

    public function columns(AdminConfig $config, Table $table, ?SlidingPaginationInterface $pagination = null): array
    {
        $context = $this->contextFactory->createSerializationContext();

        if ($pagination !== null) {
            $context->setAttribute(ListSerializer::PAGINATION, $pagination);
        }

        return $this->serializer->toArray(array_values($this->shownColumns($config, $table)), $context);
    }

    public function rows(AdminConfig $config, Table $table, iterable $entities): array
    {
        $columns = $this->shownColumns($config, $table);
        $rows = [];

        foreach ($entities as $entity) {
            $rows[] = $this->doRow($config, $columns, $entity);
        }

        return $rows;
    }

    public function row(AdminConfig $config, Table $table, object|array $entity): array
    {
        return $this->doRow($config, $this->shownColumns($config, $table), $entity);
    }

    private function doRow(AdminConfig $config, array $columns, object|array $entity): array
    {
        $realEntity = $this->realEntity($entity);

        return [
            'id' => $this->propertyAccessor->getValue($realEntity, 'id'),
            'cells' => array_values(array_map(fn (Column $column) => $this->cell($config, $column, $entity), $columns)),
            'actions' => $this->actions($config, Action::TYPE_ENTITY, $realEntity),
        ];
    }

    /**
     * Value of a column for one entity: { value, attrs?, meta? }, table columns have a list of rows (and tfoot) as value.
     */
    private function cell(AdminConfig $config, Column $column, object|array $entity): array
    {
        $cell = [];

        if ($column instanceof TwigColumn) {
            $template = $this->twig->load($column->getBlockTemplate() ?? $config->getTemplates()[Action::INDEX]);

            $cell['value'] = trim($template->renderBlock($column->getBlock(), [
                'config' => $config,
                'column' => $column,
                'entity' => $entity,
                'realEntity' => $this->realEntity($entity),
            ]));
        } elseif ($column instanceof TableColumn) {
            $rows = [];

            foreach (array_keys($this->list($column->getValue($entity))) as $i) {
                $rowEntity = $column->getValueAt($entity, $i);

                $rows[] = $rowEntity === null
                    ? array_fill(0, count($column->getParts()), null)
                    : array_values(array_map(fn (Column $part) => $this->cell($config, $part, $rowEntity), $column->getParts()));
            }

            $cell['value'] = $rows;

            if ($column->getTfoot() !== null) {
                $cell['tfoot'] = array_map(
                    fn (array $tfoot) => $tfoot['column'] ? $this->cell($config, $tfoot['column'], $entity) : null,
                    $column->getTfoot()
                );
            }
        } elseif ($column instanceof CombinedColumn) {
            $value = $column->getValue($entity);

            $cell['value'] = array_values(array_map(fn (Column $part) => $this->cell($config, $part, $value), $column->getParts()));
        } else {
            $cell['value'] = $this->normalizer->normalize($column->getListValue($entity), $column->getListSerializationGroups());

            if ([] !== $meta = $column->getListMeta($entity, $this->urlGenerator)) {
                $cell['meta'] = $meta;
            }
        }

        if ([] !== $attrs = $column->getCellAttributes($entity)) {
            $cell['attrs'] = $attrs;
        }

        return $cell;
    }

    /**
     * Filter sets by group: the url applies the set (the sets of the other groups are kept), active while it is applied.
     *
     * @return array<array<array<string, mixed>>>
     */
    private function filterSets(AdminConfig $config, Table $table): array
    {
        $sets = $this->requestStack->getCurrentRequest()?->query->all('sets') ?? [];
        $route = $config->getRouteNameForAction(Action::INDEX);

        return array_values(array_map(fn (array $group) => array_values(array_map(fn (FilterSet $set) => [
            'name' => $this->trans($set->getName()),
            'icon' => $set->getIcon() === null ? null : ($this->iconClass)($set->getIcon()),
            'url' => $this->urlGenerator->generate($route, ['sets' => array_merge($sets, [$set->getGroup() => $set->getKey()])]),
            'active' => ($sets[$set->getGroup()] ?? null) === $set->getKey(),
        ], $group)), $table->getFilterSets()));
    }

    private function actions(AdminConfig $config, string $type, ?object $entity = null): array
    {
        $context = $this->contextFactory->createSerializationContext()->setAttribute(ListSerializer::CONFIG, $config);

        if ($entity !== null) {
            $context->setAttribute(ListSerializer::ENTITY, $entity);
        }

        return $this->serializer->toArray(array_values($config->getActions($type)), $context);
    }

    /**
     * Serialized filter form, prepared for rendering outside of the form element:
     * widgets belong to the `filters` form, the label is the placeholder, labels and choices are translated.
     */
    private function filters(FormView $view): array
    {
        $this->prepareFilter($view);

        return $this->normalizer->normalize($view, ['Default']);
    }

    private function prepareFilter(FormView $view): void
    {
        $label = $view->vars['label'] ?? null;

        if ($label instanceof TranslatableInterface || (is_string($label) && $label !== '')) {
            $view->vars['label'] = $this->translateLabel($label, $view->vars['translation_domain'] ?? null);
        }

        if (!($view->vars['compound'] ?? false) || isset($view->vars['vue_component'])) {
            $view->vars['attr']['form'] = 'filters';

            if (is_string($view->vars['label'])) {
                $view->vars['attr']['placeholder'] = $view->vars['label'];
            }
        }

        $choiceDomain = $view->vars['choice_translation_domain'] ?? false;

        foreach (['choices', 'preferred_choices'] as $key) {
            if (isset($view->vars[$key])) {
                $this->translateChoices($view->vars[$key], $choiceDomain);
            }
        }

        foreach ($view->children as $child) {
            $this->prepareFilter($child);
        }
    }

    /**
     * @param array<ChoiceView|ChoiceGroupView> $choices
     */
    private function translateChoices(array $choices, string|false|null $domain): void
    {
        foreach ($choices as $choice) {
            if ($choice instanceof ChoiceGroupView) {
                $this->translateChoices($choice->choices, $domain);
            } elseif ($choice->label instanceof TranslatableInterface || ($domain !== false && is_string($choice->label))) {
                $choice->label = $this->translateLabel($choice->label, $domain);
            }
        }
    }

    private function translateLabel(TranslatableInterface|string $label, string|false|null $domain): string
    {
        return match (true) {
            $label instanceof TranslatableInterface => $label->trans($this->translator),
            $domain === false => $label,
            default => $this->translator->trans($label, [], $domain),
        };
    }

    /**
     * Data of the sliding pagination (same as the KNP pagination templates), with the urls of the linked pages.
     */
    private function pagination(SlidingPaginationInterface $pagination): array
    {
        $data = $this->processor->render($pagination);
        $pageCount = (int) $data['pageCount'];
        // KNP calculates these with ceil/floor
        $pagesInRange = array_map(intval(...), array_values($data['pagesInRange']));

        $linked = [1, 2, $pageCount - 1, $pageCount, $data['previous'] ?? null, $data['next'] ?? null, ...$pagesInRange];
        $linked = array_unique(array_filter($linked, fn (?int $page) => $page !== null && $page >= 1 && $page <= $pageCount));

        $urls = [];

        foreach ($linked as $page) {
            $urls[$page] = $this->urlGenerator->generate(
                $data['route'],
                $this->paginationRuntime->getQueryParams($data['query'], $page, $data['options'] ?? [])
            );
        }

        return [
            'current' => (int) $data['current'],
            'pageCount' => $pageCount,
            'startPage' => (int) $data['startPage'],
            'endPage' => (int) $data['endPage'],
            'pagesInRange' => $pagesInRange,
            'previous' => $data['previous'] ?? null,
            'next' => $data['next'] ?? null,
            'urls' => (object) $urls,
            'labels' => [
                'previous' => $this->translator->trans('label_previous', [], 'KnpPaginatorBundle'),
                'next' => $this->translator->trans('label_next', [], 'KnpPaginatorBundle'),
            ],
        ];
    }

    private function trans(TranslatableInterface|string|null $message): ?string
    {
        return match (true) {
            $message === null, $message === '' => null,
            $message instanceof TranslatableInterface => $message->trans($this->translator),
            default => $this->translator->trans($message),
        };
    }

    private function realEntity(object|array $entity): object|array
    {
        return is_array($entity) && isset($entity['__entity__']) ? $entity['__entity__'] : $entity;
    }

    private function list(mixed $value): array
    {
        return array_values(is_iterable($value) ? iterator_to_array($value) : (array) $value);
    }
}
