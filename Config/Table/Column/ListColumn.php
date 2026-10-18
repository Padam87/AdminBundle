<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use JMS\Serializer\Annotation as Serializer;
use Twig\Template;

#[Serializer\ExclusionPolicy('all')]
class ListColumn extends Column
{
    #[Serializer\Expose]
    #[Serializer\SerializedName('ordered')]
    private bool $ordered = false;

    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/list.' . $format . '.twig';
    }

    /**
     * Renderer of the column in admin lists (eg. the admin-list Vue component of the application).
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('type')]
    public function getListType(): string
    {
        return 'list';
    }

    public function isOrdered(): bool
    {
        return $this->ordered;
    }

    public function setOrdered(bool $ordered): self
    {
        $this->ordered = $ordered;

        return $this;
    }
}
