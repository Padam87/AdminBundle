<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use Symfony\Component\PropertyAccess\PropertyAccessorBuilder;
use Twig\Template;

class Column
{
    protected string $property;

    protected ?string $title = null;

    protected array $headerAttributes = [];

    protected array $cellAttributes = [];

    protected \Closure $getter;

    protected array $filters = [];

    protected bool|string $sortable = true;

    public static function create(string $property): static
    {
        return new static($property);
    }

    public function __construct(string $property)
    {
        $this->property = $property;
        $this->getter = (new ValueAccessor($property))(...);
    }

    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/scalar.html.twig';
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function setProperty(string $property): self
    {
        $this->property = $property;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getHeaderAttributes(): array
    {
        return $this->headerAttributes;
    }

    public function setHeaderAttributes(array $headerAttributes): self
    {
        $this->headerAttributes = $headerAttributes;

        return $this;
    }

    public function getCellAttributes(object|array $entity): array
    {
        return array_filter(array_map(function (mixed $value) use ($entity) {
            if (is_callable($value)) {
                return $value($entity);
            }

            return $value;
        }, $this->cellAttributes));
    }

    public function setCellAttributes(array $cellAttributes): self
    {
        $this->cellAttributes = $cellAttributes;

        return $this;
    }

    public function getGetter(): \Closure
    {
        return $this->getter;
    }

    public function setGetter(\Closure $getter): self
    {
        $this->getter = $getter;

        return $this;
    }

    public function getValue(object|array $entity)
    {
        return ($this->getter)($entity);
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function getSortable(): bool|string
    {
        return $this->sortable;
    }

    public function setSortable(bool|string $sortable): self
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function isCombined(): bool
    {
        return false;
    }
}
