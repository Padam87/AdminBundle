<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use Twig\Template;

class CombinedColumn extends Column
{
    private array $parts = [];

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

    public function addPart(Column $part): self
    {
        if ($part instanceof CombinedColumn) {
            throw new \LogicException('Multi level nesting of columns is not allowed.');
        }

        $this->parts[$part->getProperty()] = $part;

        return $this;
    }

    public function isCombined(): bool
    {
        return true;
    }
}
