<?php

namespace Padam87\AdminBundle\Config;

use Padam87\AdminBundle\Config\Action\Action;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Translation\TranslatableMessage;

class AdminConfig
{
    private array $actions = [];

    private array $templates;

    private array $viewVariables = [];

    private Route $baseRoute;

    private RouteCollection $routes;

    private ?HtmlElement $icon = null;

    private string|TranslatableMessage $singularName;

    private string|TranslatableMessage $pluralName;

    public function __construct(
        private string $entityFqcn,
        private ?string $dataFormFqcn = null,
        private ?array $dataFormOptions = null,
        private ?string $filterFormFqcn = null,
        private ?array $filterFormOptions = null,
    ) {
        $this->dataFormOptions ??= [
            'data_class' => $this->getEntityFqcn(),
        ];

        $this->filterFormFqcn ??= FormType::class;
        $this->filterFormOptions ??= [
            'method' => 'GET',
            'required' => false,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ];
    }

    public function getEntityFqcn(): string
    {
        return $this->entityFqcn;
    }

    public function getDataFormFqcn(): ?string
    {
        return $this->dataFormFqcn;
    }

    public function setDataFormFqcn(?string $dataFormFqcn): self
    {
        $this->dataFormFqcn = $dataFormFqcn;

        return $this;
    }

    public function getDataFormOptions(): array
    {
        return $this->dataFormOptions;
    }

    public function setDataFormOptions(array $dataFormOptions): self
    {
        $this->dataFormOptions = $dataFormOptions;

        return $this;
    }

    public function getFilterFormFqcn(): ?string
    {
        return $this->filterFormFqcn;
    }

    public function setFilterFormFqcn(?string $filterFormFqcn): self
    {
        $this->filterFormFqcn = $filterFormFqcn;

        return $this;
    }

    public function getFilterFormOptions(): array
    {
        return $this->filterFormOptions;
    }

    public function setFilterFormOptions(array $filterFormOptions): self
    {
        $this->filterFormOptions = $filterFormOptions;

        return $this;
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

        /** @var Route $route */
        foreach ($this->routes->all() as $routeName => $route) {
            if ($route->getDefault('_action') === $name) {
                $this->routes->remove($routeName);
            }
        }

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

    public function setTemplate(string $action, string $template): self
    {
        $this->templates[$action] = $template;

        return $this;
    }

    public function getViewVariables(): array
    {
        return $this->viewVariables;
    }

    public function setViewVariables(array $viewVariables): self
    {
        $this->viewVariables = $viewVariables;

        return $this;
    }

    public function addViewVariable(?string $action, string $name, mixed $value): self
    {
        $this->viewVariables[$action][$name] = $value;

        return $this;
    }

    public function getViewVariablesForAction(string $action): array
    {
        return array_merge(
            $this->viewVariables[null] ?? [],
            $this->viewVariables[$action] ?? [],
        );
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
