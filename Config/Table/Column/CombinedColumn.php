<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use JMS\Serializer\Annotation as Serializer;
use Twig\Template;

#[Serializer\ExclusionPolicy('all')]
class CombinedColumn extends Column
{
    private array $parts = [];
    #[Serializer\Expose]
    #[Serializer\SerializedName('asTable')]
    private bool $asTable = true;

    public function __construct(string $property)
    {
        parent::__construct($property);

        $this->getter = function (object|array $entity): object|array {
            return $entity;
        };

        $this->sortable = false;
    }

    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/combined.html.twig';
    }

    public function getParts(): array
    {
        return $this->parts;
    }

    public function setParts(array $parts): self
    {
        $this->parts = $parts;

        return $this;
    }

    /**
     * @return Column[]
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('parts')]
    public function getPartList(): array
    {
        return array_values($this->parts);
    }

    public function addPart(Column $part): self
    {
        $this->parts[$part->getProperty()] = $part;

        return $this;
    }

    public function isCombined(): bool
    {
        return true;
    }

    /**
     * Renderer of the column in admin lists (eg. the admin-list Vue component of the application).
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('type')]
    public function getListType(): string
    {
        return 'combined';
    }

    public function isAsTable(): bool
    {
        return $this->asTable;
    }

    public function setAsTable(bool $asTable): self
    {
        $this->asTable = $asTable;

        return $this;
    }
}
