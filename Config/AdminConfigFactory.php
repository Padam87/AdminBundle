<?php

namespace Padam87\AdminBundle\Config;

use Padam87\AdminBundle\Config\Action\Action;
use Padam87\AdminBundle\Config\Action\SubmittedAction;
use Symfony\Component\Routing\Attribute\Route as RouteAttribute;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminConfigFactory
{
    public function __construct(
        private array $config,
        private NameConverterInterface $nameConverter,
        private TranslatorInterface $translator
    ) {
    }

    public function create(string $controllerFqcn, string $entityFqcn): AdminConfig
    {
        $refl = new \ReflectionClass($entityFqcn);
        $name = $this->nameConverter->normalize($refl->getShortName());

        $config = new AdminConfig($entityFqcn);
        $config
            ->setSingularName(sprintf('%s.singular', $name))
            ->setPluralName(sprintf('%s.plural', $name))
            ->setTemplates($this->config['templates'])
            ->setBaseRoute($this->getBaseRoute($controllerFqcn))
            ->setRoutes($this->getRoutes($controllerFqcn))
            ->addAction(
                Action::create(Action::CREATE, Action::TYPE_GLOBAL)
                    ->setTitle(new TranslatableMessage('admin.action.create', ['%entity%' => $this->translator->trans($config->getSingularName())], $this->config['translations']['domains']['action']))
                    ->setRouteName($config->getRouteNameForAction(Action::CREATE))
                    ->setControl(HtmlElement::fromConfiguration($this->config['actions'][Action::CREATE]['control']))
                    ->setIcon(HtmlElement::fromConfiguration($this->config['actions'][Action::CREATE]['icon']))
            )
            ->addAction(
                Action::create(Action::EDIT, Action::TYPE_ENTITY)
                    ->setTitle(new TranslatableMessage('admin.action.edit', [], $this->config['translations']['domains']['action']))
                    ->setRouteName($config->getRouteNameForAction(Action::EDIT))
                    ->setRouteParameters(fn($entity): array => ['id' => $entity->getId()])
                    ->setControl(HtmlElement::fromConfiguration($this->config['actions'][Action::EDIT]['control']))
                    ->setIcon(HtmlElement::fromConfiguration($this->config['actions'][Action::EDIT]['icon']))
            )
            ->addAction(
                SubmittedAction::create(Action::DELETE, Action::TYPE_ENTITY)
                    ->setTitle(new TranslatableMessage('admin.action.delete', [], $this->config['translations']['domains']['action']))
                    ->setMethod('DELETE')
                    ->setRouteName($config->getRouteNameForAction(Action::DELETE))
                    ->setRouteParameters(fn($entity): array => ['id' => $entity->getId()])
                    ->setControl(HtmlElement::fromConfiguration($this->config['actions'][Action::DELETE]['control']))
                    ->setIcon(HtmlElement::fromConfiguration($this->config['actions'][Action::DELETE]['icon']))
            )
            ->addAction(
                Action::create(Action::BATCH_DELETE, Action::TYPE_BATCH)
                    ->setTitle(new TranslatableMessage('admin.action.batch_delete', [], $this->config['translations']['domains']['action']))
                    ->setRouteName($config->getRouteNameForAction(Action::BATCH_DELETE))
                    ->setControl(HtmlElement::fromConfiguration($this->config['actions'][Action::BATCH_DELETE]['control']))
                    ->setIcon(HtmlElement::fromConfiguration($this->config['actions'][Action::BATCH_DELETE]['icon']))
            )
        ;

        return $config;
    }

    protected function getBaseRoute(string $controllerFqcn): RouteAttribute
    {
        $class = new \ReflectionClass($controllerFqcn);
        $attribute = $class->getAttributes(RouteAttribute::class, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

        if ($attribute === null) {
            throw new \LogicException(
                sprintf(
                    'The admin controller "%s" is required to have a base #[Route] annotation on the class itself.',
                    $class->getName()
                )
            );
        }

        return $attribute->newInstance();
    }

    protected function getRoutes(string $controllerFqcn): RouteCollection
    {
        $global = $this->getBaseRoute($controllerFqcn);

        $collection = new RouteCollection();

        $collection->add(
            Action::INDEX,
            new Route(
                '/',
                defaults: [
                    '_controller' => $controllerFqcn.'::__'.Action::INDEX,
                    '_action' => Action::INDEX,
                ],
                methods: ['GET']
            )
        );

        $collection->add(
            Action::CREATE,
            new Route(
                '/create',
                defaults: [
                    '_controller' => $controllerFqcn.'::__'.Action::CREATE,
                    '_action' => Action::CREATE,
                ],
                methods: ['GET', 'POST']
            )
        );

        $collection->add(
            Action::EDIT,
            new Route(
                '/{id}/edit',
                defaults: [
                    '_controller' => $controllerFqcn.'::__'.Action::EDIT,
                    '_action' => Action::EDIT,
                ],
                methods: ['GET', 'POST']
            )
        );

        $collection->add(
            Action::DELETE,
            new Route(
                '/{id}/delete',
                defaults: [
                    '_controller' => $controllerFqcn.'::__'.Action::DELETE,
                    '_action' => Action::DELETE,
                ],
                requirements: [
                    '_method' => 'DELETE',
                ],
                methods: ['DELETE'],
            )
        );

        $collection->add(
            Action::BATCH_DELETE,
            new Route(
                '/batch-delete',
                defaults: [
                    '_controller' => $controllerFqcn.'::__'.Action::BATCH_DELETE,
                    '_action' => Action::BATCH_DELETE,
                ],
                methods: ['POST'],
            )
        );

        $collection->addPrefix($global->getPath());
        $collection->addNamePrefix($global->getName());
        $collection->addDefaults($global->getDefaults());
        $collection->addOptions($global->getOptions());
        $collection->addRequirements($global->getRequirements());
        $collection->setHost($global->getHost());

        return $collection;
    }
}
