<?php

namespace Padam87\AdminBundle\Listing;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface;
use Padam87\AdminBundle\Config\AdminConfig;
use Padam87\AdminBundle\Config\Table\Table;

/**
 * Application hook of the list builder, eg. for user selected columns and saved views.
 */
interface ListCustomizerInterface
{
    /**
     * Names of the shown columns (they are shown in table order), null for all columns.
     *
     * @return string[]|null
     */
    public function getSelectedColumns(AdminConfig $config, Table $table): ?array;

    /**
     * Additional data of the list, merged into the data built by ListBuilder::build.
     */
    public function getListData(AdminConfig $config, Table $table, SlidingPaginationInterface $pagination): array;
}
