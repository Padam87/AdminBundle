<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use Twig\Template;

class BooleanColumn extends Column
{
    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/boolean.' . $format . '.twig';
    }
}
