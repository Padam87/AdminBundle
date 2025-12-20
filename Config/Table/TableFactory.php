<?php

namespace Padam87\AdminBundle\Config\Table;

use Padam87\AdminBundle\Config\AdminConfig;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class TableFactory
{
    public function create(AdminConfig $config): Table
    {
        $refl = new \ReflectionClass($config->getEntityFqcn());

        $table = new Table();

        $table->setLabelFormat((new CamelCaseToSnakeCaseNameConverter())->normalize($refl->getShortName()) . '.%name%');
        $table->setQueryAlias(strtolower((string) preg_replace('/[a-z]/', '', $refl->getShortName())));

        return $table;
    }
}
