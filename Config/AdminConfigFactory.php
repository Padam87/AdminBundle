<?php

namespace Padam87\AdminBundle\Config;

use Padam87\AdminBundle\Config\Action\Action;
use Padam87\AdminBundle\Config\Action\SubmittedAction;
use Symfony\Component\Routing\Annotation\Route as RouteAnnotation;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class AdminConfigFactory
{
    public function __construct(
        private array $config,
        private NameConverterInterface $nameConverter
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
                    ->setTitle('admin.action.create.%entity%')
                    ->setRouteName($config->getRouteNameForAction(Action::CREATE))
                    ->setControl(new HtmlElement('a', ['class' => 'btn btn-success btn-icon']))
                    ->setIcon(new HtmlElement('i', ['class' => 'bi bi-plus']))
            )
            ->addAction(
                Action::create(Action::EDIT, Action::TYPE_ENTITY)
                    ->setTitle('admin.action.edit')
                    ->setRouteName($config->getRouteNameForAction(Action::EDIT))
                    ->setRouteParameters(fn($entity) => ['id' => $entity->getId()])
                    ->setControl(new HtmlElement('a', ['class' => 'btn btn-secondary']))
                    ->setIcon(new HtmlElement('i', ['class' => 'bi bi-pen']))
            )
            ->addAction(
                SubmittedAction::create(Action::DELETE, Action::TYPE_ENTITY)
                    ->setTitle('admin.action.delete')
                    ->setMethod('DELETE')
                    ->setRouteName($config->getRouteNameForAction(Action::DELETE))
                    ->setRouteParameters(fn($entity) => ['id' => $entity->getId()])
                    ->setControl(new HtmlElement('button', ['class' => 'btn btn-danger', 'type' => 'submit']))
                    ->setIcon(new HtmlElement('i', ['class' => 'bi bi-trash']))
            )
            ->addAction(
                Action::create(Action::BATCH_DELETE, Action::TYPE_BATCH)
                    ->setTitle('admin.action.batch_delete')
                    ->setRouteName($config->getRouteNameForAction(Action::BATCH_DELETE))
                    ->setControl(new HtmlElement('button', ['class' => 'btn btn-danger btn-sm btn-icon', 'type' => 'submit', 'form' => 'batch']))
                    ->setIcon(new HtmlElement('i', ['class' => 'bi bi-trash']))
            )
        ;

        return $config;
    }

    protected function getBaseRoute(string $controllerFqcn): RouteAnnotation
    {
        $class = new \ReflectionClass($controllerFqcn);
        $attribute = $class->getAttributes(RouteAnnotation::class, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

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
                methods: ['POST'],
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
                requirements: [
                    '_method' => 'DELETE',
                ],
                methods: ['POST'],
            )
        );

        $collection->addPrefix($global->getPath());
        $collection->addNamePrefix($global->getName());
        $collection->addDefaults($global->getDefaults());
        $collection->addOptions($global->getOptions());
        $collection->addRequirements($global->getRequirements());

        return $collection;
    }
}
