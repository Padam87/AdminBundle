<?php

namespace Padam87\AdminBundle\Routing;

use Padam87\AdminBundle\Controller\AdminController;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

class AdminLoader extends Loader
{
    public function __construct(string $env = null, private iterable $controllers)
    {
        parent::__construct($env);
    }

    public function load(mixed $resource, string $type = null): mixed
    {
        $routes = new RouteCollection();

        /** @var AdminController $controller */
        foreach ($this->controllers as $controller) {
            $routes->addCollection($controller->getConfig()->getRoutes());
        }

        return $routes;
    }

    public function supports(mixed $resource, string $type = null): bool
    {
        return 'padam87_admin' === $type;
    }
}
