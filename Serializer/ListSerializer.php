<?php

namespace Padam87\AdminBundle\Serializer;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Knp\Bundle\PaginatorBundle\Helper\Processor;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface;
use Padam87\AdminBundle\Config\Action\Action;
use Padam87\AdminBundle\Config\IconClass;
use Padam87\AdminBundle\Config\Table\Column\BooleanColumn;
use Padam87\AdminBundle\Config\Table\Column\Column;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Adds the context dependent fields of admin table columns and actions (the rest is in their JMS metadata).
 *
 * Context attributes:
 * - `admin_config` (AdminConfig): the admin of the actions
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
        private Processor $processor,
        private IconClass $iconClass,
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

    /**
     * An action: name, title, url, enabled, behavior, method, variant, icon (its class), confirmation.
     */
    public function serializeAction(ObjectEvent $event): void
    {
        /** @var Action $action */
        $action = $event->getObject();
        $visitor = $event->getVisitor();
        $context = $event->getContext();

        if (!$visitor instanceof SerializationVisitorInterface) {
            return;
        }

        $entity = $context->hasAttribute(self::ENTITY) ? $context->getAttribute(self::ENTITY) : null;

        $this->add($visitor, 'title', $this->trans($action->getTitle()));
        $this->add($visitor, 'url', $this->urlGenerator->generate($action->getRouteName(), $action->getRouteParameters($entity)));
        $this->add($visitor, 'enabled', $entity === null || $action->isEnabledFor($entity));
        $this->add($visitor, 'behavior', $action->getBehavior()->value);
        $this->add($visitor, 'method', $action->getMethod());
        $this->add($visitor, 'variant', $action->getVariant()->value);
        $this->add($visitor, 'icon', $action->getIcon() === null ? null : ($this->iconClass)($action->getIcon()));
        $this->add($visitor, 'confirmation', $this->trans($action->getConfirmation()));
    }

    private function trans(TranslatableInterface|string|null $message): ?string
    {
        return match (true) {
            $message === null, $message === '' => null,
            $message instanceof TranslatableInterface => $message->trans($this->translator),
            default => $this->translator->trans($message),
        };
    }

    private function add(SerializationVisitorInterface $visitor, string $name, mixed $value): void
    {
        $visitor->visitProperty(new StaticPropertyMetadata('', $name, $value), $value);
    }
}
