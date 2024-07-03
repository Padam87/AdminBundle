<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Twig\Template;

class RouteColumn extends Column
{
    private ?\Closure $routeCallback = null;

    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/route.' . $format . '.twig';
    }

    public function setRouteCallback(\Closure $routeCallback): self
    {
        $this->routeCallback = $routeCallback;

        return $this;
    }

    public function getRoute(object|array $entity): array|null
    {
        if (null === $this->routeCallback) {
            throw new \LogicException('A route callback must be provided.');
        }

        if (null === $params = ($this->routeCallback)($entity)) {
            return null;
        }

        $resolver = new OptionsResolver();
        $resolver
            ->setRequired(['name', 'parameters'])
            ->setDefault('reference_type', RouterInterface::ABSOLUTE_PATH)
            ->setAllowedValues('reference_type',
                [
                    RouterInterface::ABSOLUTE_URL,
                    RouterInterface::ABSOLUTE_PATH,
                    RouterInterface::RELATIVE_PATH,
                    RouterInterface::NETWORK_PATH,
                ]
            )
        ;

        return $resolver->resolve($params);
    }
}
