<?php

namespace Padam87\AdminBundle\Config\Action;

use Padam87\AdminBundle\Config\HtmlElement;
use Symfony\Component\Translation\TranslatableMessage;
use Twig\Template;

class Action
{
    const INDEX = 'index';
    const CREATE = 'create';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const BATCH_DELETE = 'batchDelete';

    const TYPE_GLOBAL = 'global';
    const TYPE_ENTITY = 'entity';
    const TYPE_BATCH = 'batch';
    const TYPE_TABLE = 'table';

    private string $routeName;

    private array|\Closure $routeParameters = [];

    private ?\Closure $condition = null;

    private null|string|TranslatableMessage $title = null;

    private HtmlElement $control;

    private ?HtmlElement $icon = null;

    public function __construct(private string $name,  private string $type)
    {
        $this->control = new HtmlElement('a');
    }

    public static function create(string $name,  string $type): static
    {
        return new static($name, $type);
    }

    public function getTemplate(): Template|string
    {
        return '@Padam87Admin/action/action.html.twig';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function setRouteName(string $routeName): self
    {
        $this->routeName = $routeName;

        return $this;
    }

    public function getRouteParameters(?object $entity): array
    {
        if (is_callable($this->routeParameters)) {
            return ($this->routeParameters)($entity);
        }

        return $this->routeParameters;
    }

    public function setRouteParameters(array|\Closure $routeParameters): self
    {
        $this->routeParameters = $routeParameters;

        return $this;
    }

    public function isEnabledFor(?object $entity): bool
    {
        if (null === $this->condition) {
            return true;
        }

        return (bool) ($this->condition)($entity);
    }

    public function setCondition(?\Closure $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function getTitle(): TranslatableMessage|string|null
    {
        return $this->title;
    }

    public function setTitle(TranslatableMessage|string|null $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getControl(): HtmlElement
    {
        return $this->control;
    }

    public function setControl(HtmlElement $control): self
    {
        $this->control = $control;

        return $this;
    }

    public function getIcon(): ?HtmlElement
    {
        return $this->icon;
    }

    public function setIcon(?HtmlElement $icon): self
    {
        $this->icon = $icon;

        return $this;
    }
}
