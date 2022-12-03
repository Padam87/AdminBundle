<?php

namespace Padam87\AdminBundle\Config;

use Padam87\AdminBundle\Config\Action\Action;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Translation\TranslatableMessage;

class AdminConfig
{
    private array $actions = [];

    private array $templates;

    private Route $baseRoute;

    private RouteCollection $routes;

    private ?HtmlElement $icon = null;

    private string|TranslatableMessage $singularName;

    private string|TranslatableMessage $pluralName;

    public function __construct(private string $entityFqcn)
    {
    }

    public function getEntityFqcn(): string
    {
        return $this->entityFqcn;
    }

    public function getAction(string $name): ?Action
    {
        return $this->actions[$name] ?? null;
    }

    /**
     * @return Action[]
     */
    public function getActions(?string $type = null): array
    {
        if ($type === null) {
            return $this->actions;
        }

        $actions = [];

        /** @var Action $action */
        foreach ($this->actions as $action) {
            if ($action->getType() === $type) {
                $actions[] = $action;
            }
        }

        return $actions;
    }

    public function addAction(Action $action): self
    {
        $this->actions[$action->getName()] = $action;

        return $this;
    }

    public function removeAction(string $name): self
    {
        unset($this->actions[$name]);

        return $this;
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }

    public function setTemplates(array $templates): self
    {
        $this->templates = $templates;

        return $this;
    }

    public function getBaseRoute(): Route
    {
        return $this->baseRoute;
    }

    public function setBaseRoute(Route $baseRoute): self
    {
        $this->baseRoute = $baseRoute;

        return $this;
    }

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    public function getRouteNameForAction(string $action): ?string
    {
        /** @var Route $route */
        foreach ($this->routes->all() as $name => $route) {
            if ($route->getDefault('_action') === $action) {
                return $name;
            }

        }

        return null;
    }

    public function setRoutes(RouteCollection $routes): self
    {
        $this->routes = $routes;

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

    public function getSingularName(): TranslatableMessage|string
    {
        return $this->singularName;
    }

    public function setSingularName(TranslatableMessage|string $singularName): self
    {
        $this->singularName = $singularName;

        return $this;
    }

    public function getPluralName(): TranslatableMessage|string
    {
        return $this->pluralName;
    }

    public function setPluralName(TranslatableMessage|string $pluralName): self
    {
        $this->pluralName = $pluralName;

        return $this;
    }
}
