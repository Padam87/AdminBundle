<?php

namespace Padam87\AdminBundle\Config\Action;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Translation\TranslatableMessage;
use Twig\Template;

#[Serializer\ExclusionPolicy('all')]
class Action
{
    public const INDEX = 'index';
    public const CREATE = 'create';
    public const EDIT = 'edit';
    public const DELETE = 'delete';
    public const BATCH_DELETE = 'batchDelete';

    public const TYPE_GLOBAL = 'global';
    public const TYPE_ENTITY = 'entity';
    public const TYPE_BATCH = 'batch';
    public const TYPE_TABLE = 'table';

    private string $routeName;

    private array|\Closure $routeParameters = [];

    private ?\Closure $condition = null;

    private null|string|TranslatableMessage $title = null;

    private ActionBehavior $behavior = ActionBehavior::Navigate;

    private ActionVariant $variant = ActionVariant::Secondary;

    // the method of a submitted action (default POST), eg. DELETE
    private ?string $method = null;

    // the name of the icon (see IconClass)
    private ?string $icon = null;

    private null|string|TranslatableMessage $confirmation = null;

    public function __construct(private string $name,  private string $type)
    {
    }

    public static function create(string $name,  string $type): static
    {
        return new static($name, $type);
    }

    public function getTemplate(): Template|string
    {
        return '@Padam87Admin/action/action.html.twig';
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('name')]
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

    public function getBehavior(): ActionBehavior
    {
        return $this->behavior;
    }

    public function setBehavior(ActionBehavior $behavior): self
    {
        $this->behavior = $behavior;

        return $this;
    }

    public function getVariant(): ActionVariant
    {
        return $this->variant;
    }

    public function setVariant(ActionVariant $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method ?? ($this->getBehavior() === ActionBehavior::Submit ? 'POST' : 'GET');
    }

    public function setMethod(?string $method): self
    {
        $this->method = $method === null ? null : strtoupper($method);

        return $this;
    }

    public function getConfirmation(): TranslatableMessage|string|null
    {
        return $this->confirmation;
    }

    public function setConfirmation(TranslatableMessage|string|null $confirmation): self
    {
        $this->confirmation = $confirmation;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string|null $icon the name of the icon (see IconClass)
     */
    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }
}
