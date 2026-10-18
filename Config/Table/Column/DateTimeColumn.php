<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use JMS\Serializer\Annotation as Serializer;
use Twig\Template;

#[Serializer\ExclusionPolicy('all')]
class DateTimeColumn extends Column
{
    #[Serializer\Expose]
    #[Serializer\SerializedName('format')]
    private string $format = 'Y-m-d H:i';

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/datetime.' . $format . '.twig';
    }

    /**
     * Renderer of the column in admin lists (eg. the admin-list Vue component of the application).
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('type')]
    public function getListType(): string
    {
        return 'datetime';
    }
}
