<?php

namespace Padam87\AdminBundle\Config\Table;

use Padam87\AdminBundle\Config\Table\Column\Column;
use Padam87\AdminBundle\Config\Table\Column\CombinedColumn;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class Table
{
    private ?string $labelFormat = null;

    private int $itemsPerPage = 10;

    private ?string $queryAlias = null;

    private array $columns = [];

    private ?FormBuilderInterface $filters = null;

    /**
     * @var FilterSet[][]
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

    public function prepareColumn(Column $column): Column
    {
        if ($column->getTitle() === null) {
            $column->setTitle(str_replace('%name%', (new CamelCaseToSnakeCaseNameConverter())->normalize($column->getProperty()), (string) $this->labelFormat));
        }

        if ($column->getSortable() === true) {
            $column->setSortable($this->getQueryAlias() . '.' . $column->getProperty());
        }

        if ($column instanceof CombinedColumn) {
            foreach ($column->getParts() as $part) {
                $this->prepareColumn($part);
            }
        }

        return $column;
    }

    public function addColumn(Column $column): self
    {
        $this->columns[$column->getProperty()] = $this->prepareColumn($column);

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
            $set->setName(str_replace('%name%', 'filter_set.' . $set->getKey(), (string) $this->labelFormat));
        }

        $this->filterSets[$set->getGroup()][$set->getKey()] = $set;

        return $this;
    }

    /**
     * @return FilterSet[][]
     */
    public function getFilterSets(): array
    {
        return $this->filterSets;
    }

    public function getFilterSet(string $key, string $group = FilterSet::DEFAULT_GROUP): ?FilterSet
    {
        return $this->filterSets[$group][$key];
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
