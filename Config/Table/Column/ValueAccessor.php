<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorBuilder;

class ValueAccessor
{
    public const ENTITY_IN_ARRAY_MAGIC_KEY = '__entity__';

    private PropertyAccessor $accessor;

    public function __construct(private string $property)
    {
        $this->accessor = (new PropertyAccessorBuilder())->getPropertyAccessor();
    }

    public function __invoke(object|array $entity)
    {
        if (is_array($entity)
            && array_key_exists(self::ENTITY_IN_ARRAY_MAGIC_KEY, $entity)
            && $this->accessor->isReadable($entity[self::ENTITY_IN_ARRAY_MAGIC_KEY], $this->property))
        {
            return $this->accessor->getValue($entity[self::ENTITY_IN_ARRAY_MAGIC_KEY], $this->property);
        }

        return $this->accessor->getValue($entity, is_array($entity) ? '[' . $this->property . ']' : $this->property);
    }
}
