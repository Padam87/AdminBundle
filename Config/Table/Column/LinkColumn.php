<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use JMS\Serializer\Annotation as Serializer;
use Twig\Template;

#[Serializer\ExclusionPolicy('all')]
class LinkColumn extends Column
{
    #[Serializer\Expose]
    #[Serializer\SerializedName('url')]
    private ?string $url = null;

    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/link.' . $format . '.twig';
    }

    /**
     * Renderer of the column in admin lists (eg. the admin-list Vue component of the application).
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('type')]
    public function getListType(): string
    {
        return 'link';
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
