<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Template;

#[Serializer\ExclusionPolicy('all')]
class RouteColumn extends Column
{
    private ?\Closure $routeCallback = null;

    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/route.' . $format . '.twig';
    }

    /**
     * Renderer of the column in admin lists (eg. the admin-list Vue component of the application).
     */
    public function getListMeta(object|array $entity, UrlGeneratorInterface $urlGenerator): array
    {
        if (is_array($entity) && isset($entity[ValueAccessor::ENTITY_IN_ARRAY_MAGIC_KEY])) {
            $entity = $entity[ValueAccessor::ENTITY_IN_ARRAY_MAGIC_KEY];
        }

        if (null === $route = $this->getRoute($entity)) {
            return [];
        }

        return ['href' => $urlGenerator->generate($route['name'], $route['parameters'], $route['reference_type'])];
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('type')]
    public function getListType(): string
    {
        return 'route';
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
