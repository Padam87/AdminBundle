<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Twig\Template;

#[Serializer\ExclusionPolicy('all')]
class TableColumn extends CombinedColumn
{
    private ?string $labelFormat = null;
    private ?array $tfoot = null;

    public function __construct(string $property)
    {
        $this->property = $property;
        $this->sortable = false;
        $this->getter = (new ValueAccessor($property))(...);
    }

    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/table.html.twig';
    }

    public function isTable(): true
    {
        return true;
    }

    public function getLabelFormat(): ?string
    {
        return $this->labelFormat;
    }

    public function setLabelFormat(?string $labelFormat): self
    {
        $this->labelFormat = $labelFormat;

        return $this;
    }

    public function getRowCount(object|array $entity): int
    {
        $value = $this->getValue($entity);
        $count = count($value);

        if ($this->tfoot !== null) {
            $count++;
        }

        return $count;
    }

    public function getValueAt(object|array $entity, int $at = 0)
    {
        $value = $this->getValue($entity);

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        $rows = array_values($value);

        return $rows[$at] ?? null;
    }

    public function addPart(Column $part): CombinedColumn
    {
        if (true === $part->getSortable()) {
            $part->setSortable(false);
        }

        if (null === $part->getTitle() && null !== $this->labelFormat) {
            $part->setTitle(str_replace('%name%', new CamelCaseToSnakeCaseNameConverter()->normalize($part->getProperty()), $this->labelFormat));
        }

        return parent::addPart($part);
    }

    public function getTfoot(): ?array
    {
        return $this->tfoot;
    }

    public function setTfoot(?array $tfoot): self
    {
        $this->tfoot = $tfoot;

        return $this;
    }

    /**
     * Footer cells of the table rows, columns are optional.
     *
     * @return array<array{colspan: int, column: ?Column}>|null
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('tfoot')]
    public function getTfootList(): ?array
    {
        return $this->tfoot === null ? null : array_values($this->tfoot);
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('type')]
    public function getListType(): string
    {
        return 'table';
    }
}
