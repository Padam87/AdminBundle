<?php

namespace Padam87\AdminBundle\Twig;

use Padam87\AdminBundle\Config\IconClass;
use Padam87\AdminBundle\Controller\AdminController;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminExtension extends AbstractExtension
{
    /**
     * @var AdminController[]
     */
    private array $controllers = [];

    public function __construct(iterable $controllers, private IconClass $iconClass)
    {
        foreach ($controllers as $controller) {
            $this->controllers[$controller::class] = $controller;
        }
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin', function (string $fqcn) {
                return $this->controllers[$fqcn];
            }),
            // the class of an icon by its name (see IconClass)
            new TwigFunction('admin_icon_class', $this->iconClass),
        ];
    }
}
