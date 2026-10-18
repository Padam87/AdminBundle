<?php

namespace Padam87\AdminBundle\Twig;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface;
use Padam87\AdminBundle\Config\AdminConfig;
use Padam87\AdminBundle\Config\Table\Table;
use Padam87\AdminBundle\Listing\ListBuilder;
use Symfony\Component\Form\FormView;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Lazy loaded by twig, the list builder depends on twig itself.
 */
class ListRuntime implements RuntimeExtensionInterface
{
    public function __construct(private ListBuilder $builder)
    {
    }

    public function build(AdminConfig $config, Table $table, SlidingPaginationInterface $pagination, ?FormView $filters = null): array
    {
        return $this->builder->build($config, $table, $pagination, $filters);
    }
}
