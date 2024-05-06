<?php

namespace Padam87\AdminBundle\Controller;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Padam87\AdminBundle\Config\Action\Action;
use Padam87\AdminBundle\Config\AdminConfig;
use Padam87\AdminBundle\Config\AdminConfigFactory;
use Padam87\AdminBundle\Config\Table\Table;
use Padam87\AdminBundle\Config\Table\TableFactory;
use Padam87\FormFilterBundle\Service\Filters;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AdminController extends AbstractController
{
    private AdminConfig $config;

    public static function getSubscribedServices(): array
    {
        return array_merge(
            [
                'knp_paginator' => PaginatorInterface::class,
                'translator' => TranslatorInterface::class,
                'event_dispatcher' => EventDispatcherInterface::class,
                'doctrine' => ManagerRegistry::class,
                AdminConfigFactory::class => AdminConfigFactory::class,
                TableFactory::class => TableFactory::class,
                Filters::class => Filters::class,
            ],
            parent::getSubscribedServices()
        );
    }

    public function init(AdminConfigFactory $configFactory): void
    {
        $config = $configFactory->create(static::class, $this->getEntityFqcn());
        $this->configure($config);
        $this->config = $config;
    }

    abstract protected function getEntityFqcn();

    public function getFormFqcn($entity): ?string
    {
        return null;
    }

    protected function configure(AdminConfig $config): void
    {
    }

    public function getConfig(): AdminConfig
    {
        return $this->config;
    }

    /*********************************/
    /*         LIST Methods          */
    /*********************************/

    public function __index(): Response
    {
        $table = $this->createTable();
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $filters = $this->createFilterForm($table);

        return $this->render(
            $this->config->getTemplates()[Action::INDEX],
            [
                'config' => $this->config,
                'table' => $table,
                'pagination' => $this->paginate($table, $filters),
                'filters' => $filters->createView(),
                'set' => $request->query->get('set'),
                ...$this->config->getViewVariablesForAction(Action::INDEX),
            ]
        );
    }

    public function createTable(): Table
    {
        $tableFactory = $this->container->get(TableFactory::class);

        $table = $tableFactory->create($this->config);
        $this->table($table);

        return $table;
    }

    protected function table(Table $table): void
    {
    }

    public function editFilterData(?array $data): array
    {
        return $data;
    }

    protected function createFilterForm(Table $table): ?FormInterface
    {
        if ($table->getFilters() === null) {
            return null;
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();

        $filters = $table->getFilters()->getForm();

        if ($request->query->has('set')) {
            $set = $table->getFilterSets()[$request->query->get('set')];
            $filters->setData($set->getData());
        } else {
            $filters->submit($this->editFilterData($request->query->all()));
        }

        return $filters;
    }

    protected function paginate(Table $table, ?FormInterface $filters = null, array $options = []): PaginationInterface
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        $qb = $this->createQueryBuilder($table);

        if ($filters !== null) {
            $this->container->get(Filters::class)->apply($qb, $filters);
        }

        return $this->container->get('knp_paginator')->paginate(
            $qb,
            $request->get('page', 1),
            $request->query->get('items', $table->getItemsPerPage()),
            array_merge($options, $table->getPaginatorOptions())
        );
    }

    protected function createQueryBuilder(Table $table): QueryBuilder
    {
        return $this->container->get('doctrine')->getManager()->getRepository($this->getEntityFqcn())
            ->createQueryBuilder($table->getQueryAlias());
    }

    /*********************************/
    /*     CREATE / EDIT Methods     */
    /*********************************/

    public function __create(): Response
    {
        $entity = $this->createEntity();

        if (true === $data = $this->upsert($entity)) {
            return $this->after(Action::CREATE, $entity);
        }

        return $this->render(
            $this->config->getTemplates()[Action::CREATE],
            [
                ...$data,
                'config' => $this->config,
                ...$this->config->getViewVariablesForAction(Action::CREATE),
            ]
        );
    }

    public function __edit(Request $request): Response
    {
        $id = $request->get('id');
        $em = $this->container->get('doctrine')->getManager();

        if (null === $entity = $em->find($this->getEntityFqcn(), $id)) {
            throw $this->createNotFoundException();
        }

        if (!$this->config->getAction(Action::EDIT)->isEnabledFor($entity)) {
            throw $this->createNotFoundException();
        }

        if (true === $data = $this->upsert($entity)) {
            return $this->after(Action::EDIT, $entity);
        }

        return $this->render(
            $this->config->getTemplates()[Action::EDIT],
            [
                ...$data,
                'config' => $this->config,
                ...$this->config->getViewVariablesForAction(Action::EDIT),
            ]
        );
    }

    protected function upsert($entity): array|bool
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        $form = $this->createDataForm($entity);

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->save($entity);

                return true;
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    protected function createEntity(): object
    {
        $fqcn = $this->getEntityFqcn();

        return new $fqcn();
    }

    protected function save($entity): void
    {
        $em = $this->container->get('doctrine')->getManager();
        $em->persist($entity);
        $em->flush();
    }

    protected function createDataForm($entity): FormInterface
    {
        if ($this->getFormFqcn($entity) === null) {
            throw new \LogicException('No data form specified');
        }

        return $this->createForm($this->getFormFqcn($entity), $entity, $this->getFormOptions($entity));
    }

    public function getFormOptions($entity): array
    {
        return [
            'data_class' => $this->getEntityFqcn(),
        ];
    }

    /*********************************/
    /*         DELETE Method         */
    /*********************************/

    public function __delete(Request $request): Response
    {
        $this->deleteEntity($request->get('id'));

        return $this->after(Action::DELETE);
    }

    public function __batchDelete(Request $request): Response
    {
        $selected = $request->request->all('selected');

        foreach ($selected as $id) {
            try {
                $this->deleteEntity($id);
            } catch (NotFoundHttpException) {
                continue;
            }
        }

        return $this->after(Action::BATCH_DELETE);
    }

    protected function deleteEntity($id)
    {
        $em = $this->container->get('doctrine')->getManager();

        if (null === $entity = $em->find($this->getEntityFqcn(), $id)) {
            throw $this->createNotFoundException();
        }

        if (!$this->config->getAction(Action::DELETE)->isEnabledFor($entity)) {
            throw $this->createNotFoundException();
        }

        try {
            $em->remove($entity);
            $em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            $this->addFlash(
                'danger',
                $this->container->get('translator')->trans(
                    'flash.delete.failure.%id%.%entity%',
                    [
                        '%id%' => $id,
                        '%entity%' => $entity instanceof \Stringable ? (string) $entity : '',
                    ],
                    'padam87_admin'
                )
            );
        }
    }

    protected function redirectToReferer(): RedirectResponse
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        return $this->redirect($request->headers->get('referer'));
    }

    protected function after(string $action, $entity = null): Response
    {
        return $this->redirectToRoute($this->config->getRouteNameForAction(Action::INDEX));
    }
}
