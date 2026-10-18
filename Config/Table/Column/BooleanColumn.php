<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use JMS\Serializer\Annotation as Serializer;
use Twig\Template;

class BooleanColumn extends Column
{
    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/boolean.' . $format . '.twig';
    }

    /**
     * Renderer of the column in admin lists (eg. the admin-list Vue component of the application).
     */
    public function getListValue(object|array $entity): bool
    {
        return (bool) $this->getValue($entity);
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('type')]
    public function getListType(): string
    {
        return 'boolean';
    }
}
