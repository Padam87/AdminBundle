<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use Twig\Template;

class DateTimeColumn extends Column
{
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
}
