<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use Twig\Template;

class ListColumn extends Column
{
    private bool $ordered = false;

    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/list.' . $format . '.twig';
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
