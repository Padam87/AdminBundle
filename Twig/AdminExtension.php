<?php

namespace Padam87\AdminBundle\Twig;

use Padam87\AdminBundle\Controller\AdminController;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminExtension extends AbstractExtension
{
    /**
     * @var AdminController[]
     */
    private array $controllers = [];

    public function __construct(iterable $controllers)
    {
        foreach ($controllers as $controller) {
            $this->controllers[$controller::class] = $controller;
        }
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('admin', function (string $fqcn) {
                return $this->controllers[$fqcn];
            }),
        ];
    }
}
