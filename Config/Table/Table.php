<?php

namespace Padam87\AdminBundle\Config\Table;

use Padam87\AdminBundle\Config\Table\Column\Column;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class Table
{
    private ?string $labelFormat;

    private int $itemsPerPage = 10;

    private ?string $queryAlias;

    private array $columns = [];

    private ?FormBuilderInterface $filters = null;

    /**
     * @var FilterSet[]
     */
    private array $filterSets = [];

    private array $paginatorOptions = [];

    public function getLabelFormat(): ?string
    {
        return $this->labelFormat;
    }

    public function setLabelFormat(?string $labelFormat): self
    {
        $this->labelFormat = $labelFormat;

        return $this;
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function setItemsPerPage(int $itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    public function getQueryAlias(): ?string
    {
        return $this->queryAlias;
    }

    public function setQueryAlias(?string $queryAlias): self
    {
        $this->queryAlias = $queryAlias;

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function addColumn(Column $column): self
    {
        if ($column->getTitle() === null) {
            $column->setTitle(str_replace('%name%', (new CamelCaseToSnakeCaseNameConverter())->normalize($column->getProperty()), $this->labelFormat));
        }

        if ($column->getSortable() === true) {
            $column->setSortable($this->getQueryAlias() . '.' . $column->getProperty());
        }

        $this->columns[$column->getProperty()] = $column;

        return $this;
    }

    public function getFilters(): ?FormBuilderInterface
    {
        return $this->filters;
    }

    public function setFilters(?FormBuilderInterface $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function addFilterSet(FilterSet $set): self
    {
        if ($set->getName() === null) {
            $set->setName(str_replace('%name%', 'filter_set.' . $set->getKey(), $this->labelFormat));
        }

        $this->filterSets[$set->getKey()] = $set;

        return $this;
    }

    /**
     * @return FilterSet[]
     */
    public function getFilterSets(): array
    {
        return $this->filterSets;
    }

    public function getPaginatorOptions(): array
    {
        return $this->paginatorOptions;
    }

    public function setPaginatorOptions(array $paginatorOptions): self
    {
        $this->paginatorOptions = $paginatorOptions;

        return $this;
    }
}
