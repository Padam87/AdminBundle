<?php

namespace Padam87\AdminBundle\Attribute;

#[\Attribute]
class Admin
{
    public function __construct(
        public string $entityFqcn,
        public ?string $dataFormFqcn = null,
        public array $dataFormOptions = [],
        public ?string $filterFormFqcn = null,
        public array $filterFormOptions = [],
    ) {
    }
}
