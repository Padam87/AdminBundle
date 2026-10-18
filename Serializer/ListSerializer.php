<?php

namespace Padam87\AdminBundle\Serializer;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Knp\Bundle\PaginatorBundle\Helper\Processor;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface;
use Padam87\AdminBundle\Config\Action\Action;
use Padam87\AdminBundle\Config\Action\SubmittedAction;
use Padam87\AdminBundle\Config\AdminConfig;
use Padam87\AdminBundle\Config\Table\Column\BooleanColumn;
use Padam87\AdminBundle\Config\Table\Column\Column;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Adds the context dependent fields of admin table columns and actions (the rest is in their JMS metadata).
 *
 * Context attributes:
 * - `admin_config` (AdminConfig): required for actions
 * - `admin_entity` (object): the row of entity actions, omitted for global, batch and table actions
 * - `admin_pagination` (SlidingPaginationInterface): sort links of the columns, omitted when not needed
 */
class ListSerializer implements EventSubscriberInterface
{
    public const CONFIG = 'admin_config';
    public const ENTITY = 'admin_entity';
    public const PAGINATION = 'admin_pagination';

    public function __construct(
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private PropertyAccessorInterface $propertyAccessor,
        private Processor $processor,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ['event' => 'serializer.post_serialize', 'interface' => Column::class, 'format' => 'json', 'method' => 'serializeColumn'],
            ['event' => 'serializer.post_serialize', 'interface' => Action::class, 'format' => 'json', 'method' => 'serializeAction'],
        ];
    }

    public function serializeColumn(ObjectEvent $event): void
    {
        /** @var Column $column */
        $column = $event->getObject();
        $visitor = $event->getVisitor();
        $context = $event->getContext();

        if (!$visitor instanceof SerializationVisitorInterface) {
            return;
        }

        $title = $this->translator->trans((string) $column->getTitle());
        $sort = null;

        if ($column->getSortable() && $context->hasAttribute(self::PAGINATION)) {
            /** @var SlidingPaginationInterface $pagination */
            $pagination = $context->getAttribute(self::PAGINATION);
            $sort = $this->processor->sortable($pagination, $title, $column->getSortable())['options'];
        }

        $this->add($visitor, 'title', $title);
        $this->add($visitor, 'sort', $sort);

        if ($column instanceof BooleanColumn) {
            $this->add($visitor, 'labels', [
                'true' => $this->translator->trans('boolean.true', [], 'padam87_admin'),
                'false' => $this->translator->trans('boolean.false', [], 'padam87_admin'),
            ]);
        }
    }

    public function serializeAction(ObjectEvent $event): void
    {
        /** @var Action $action */
        $action = $event->getObject();
        $visitor = $event->getVisitor();
        $context = $event->getContext();

        if (!$visitor instanceof SerializationVisitorInterface) {
            return;
        }

        /** @var AdminConfig $config */
        $config = $context->getAttribute(self::CONFIG);
        $entity = $context->hasAttribute(self::ENTITY) ? $context->getAttribute(self::ENTITY) : null;

        $enabled = $entity === null || $action->isEnabledFor($entity);
        $control = $action->getControl();
        $attrs = $control->getAttributes();
        $url = $this->urlGenerator->generate($action->getRouteName(), $action->getRouteParameters($entity));

        $attrs[$control->getTagName() === 'a' ? 'href' : 'formaction'] = $url;

        if (!$enabled) {
            $attrs['disabled'] = 'disabled';
            $attrs['class'] = trim(($attrs['class'] ?? '') . ' disabled');
        }

        $title = $action->getTitle();

        $this->add($visitor, 'enabled', $enabled);
        $this->add($visitor, 'tag', $control->getTagName());
        $this->add($visitor, 'attrs', $attrs);
        $this->add($visitor, 'title', match (true) {
            $title === null, $title === '' => null,
            $title instanceof TranslatableInterface => $title->trans($this->translator),
            default => $this->translator->trans($title),
        });
        $this->add($visitor, 'form', $action instanceof SubmittedAction ? [
            'action' => $entity === null
                ? null
                : $this->urlGenerator->generate($config->getRouteNameForAction($action->getName()), ['id' => $this->propertyAccessor->getValue($entity, 'id')]),
            'method' => $action->getMethod(),
        ] : null);
    }

    private function add(SerializationVisitorInterface $visitor, string $name, mixed $value): void
    {
        $visitor->visitProperty(new StaticPropertyMetadata('', $name, $value), $value);
    }
}
