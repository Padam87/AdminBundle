<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Template;

#[Serializer\ExclusionPolicy('all')]
class Column
{
    protected ?string $title = null;

    #[Serializer\Expose]
    #[Serializer\SerializedName('attrs')]
    protected array $headerAttributes = [];

    protected array $cellAttributes = [];

    protected \Closure $getter;

    #[Serializer\Expose]
    #[Serializer\SerializedName('filters')]
    protected array $filters = [];

    protected bool|string $sortable = true;

    /**
     * Serialization groups of the list value, the value is serialized by JMS as is (entities included) when set.
     */
    protected ?array $listSerializationGroups = null;

    public static function create(string $property): static
    {
        return new static($property);
    }

    public function __construct(protected string $property)
    {
        $this->getter = (new ValueAccessor($this->property))(...);
    }

    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/scalar.html.twig';
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('name')]
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

    /**
     * Value of the column in serialized lists (normalized by Padam87\AdminBundle\Listing\ValueNormalizer).
     */
    public function getListValue(object|array $entity): mixed
    {
        return $this->getValue($entity);
    }

    /**
     * Additional data of a cell in serialized lists (eg. a link).
     */
    public function getListMeta(object|array $entity, UrlGeneratorInterface $urlGenerator): array
    {
        return [];
    }

    public function getListSerializationGroups(): ?array
    {
        return $this->listSerializationGroups;
    }

    public function setListSerializationGroups(?array $listSerializationGroups): self
    {
        $this->listSerializationGroups = $listSerializationGroups;

        return $this;
    }

    /**
     * Renderer of the column in admin lists (eg. the admin-list Vue component of the application).
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('type')]
    public function getListType(): string
    {
        return 'text';
    }
}
