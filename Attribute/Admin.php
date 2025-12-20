<?php

namespace Padam87\AdminBundle\Attribute;

#[\Attribute]
class Admin
{
    public function __construct(public string $entityFqcn)
    {
    }
}
