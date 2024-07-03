<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use Twig\Template;

class LinkColumn extends Column
{
    private ?string $url = null;

    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/link.' . $format . '.twig';
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
