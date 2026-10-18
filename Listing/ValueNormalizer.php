<?php

namespace Padam87\AdminBundle\Listing;

use Doctrine\Persistence\ManagerRegistry;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Normalizes a column value (Column::getListValue) for serialized lists.
 *
 * - entities become references: { id, label }, label being the string representation shown in the list
 * - translatable messages are translated
 * - iterables are normalized item by item
 * - values with a JMS handler (money, decimal, DateTime, ...) and enums are serialized by JMS
 * - other Stringable objects become strings
 * - anything else is serialized by JMS
 *
 * When serialization groups are given the value is serialized by JMS as is, entities included.
 */
class ValueNormalizer
{
    /**
     * @var array<class-string, bool>
     */
    private array $entityClasses = [];

    /**
     * @var array<class-string, bool>
     */
    private array $handledClasses = [];

    public function __construct(
        private SerializerInterface $serializer,
        private SerializationContextFactoryInterface $contextFactory,
        private ManagerRegistry $registry,
        private TranslatorInterface $translator,
        private HandlerRegistryInterface $handlerRegistry,
    ) {
    }

    public function normalize(mixed $value, ?array $groups = null): mixed
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if ($groups !== null) {
            return $this->serialize($value, $groups);
        }

        if (is_iterable($value)) {
            $items = [];

            foreach ($value as $item) {
                $items[] = $this->normalize($item);
            }

            return $items;
        }

        if (is_object($value)) {
            if ($value instanceof TranslatableInterface) {
                return $value->trans($this->translator);
            }

            if ($this->isEntity($value)) {
                return $this->reference($value);
            }

            if ($value instanceof \UnitEnum || $this->isHandled($value)) {
                return $this->serialize($value);
            }

            if ($value instanceof \Stringable) {
                return (string) $value;
            }
        }

        return $this->serialize($value);
    }

    private function reference(object $entity): array
    {
        $manager = $this->registry->getManagerForClass($entity::class);
        $id = $manager->getClassMetadata($entity::class)->getIdentifierValues($entity);

        return [
            'id' => count($id) === 1 ? reset($id) : $id,
            'label' => $entity instanceof \Stringable ? (string) $entity : null,
        ];
    }

    private function isEntity(object $value): bool
    {
        // getManagerForClass resolves proxy class names and checks every entity manager
        return $this->entityClasses[$value::class] ??= $this->registry->getManagerForClass($value::class) !== null;
    }

    private function isHandled(object $value): bool
    {
        if (!isset($this->handledClasses[$value::class])) {
            $this->handledClasses[$value::class] = false;

            foreach ([$value::class, ...class_parents($value)] as $class) {
                if (null !== $this->handlerRegistry->getHandler(GraphNavigatorInterface::DIRECTION_SERIALIZATION, $class, 'json')) {
                    $this->handledClasses[$value::class] = true;

                    break;
                }
            }
        }

        return $this->handledClasses[$value::class];
    }

    private function serialize(mixed $value, ?array $groups = null): mixed
    {
        // contexts with the configured defaults (serialize_null, groups)
        $context = $this->contextFactory->createSerializationContext();

        if ($groups !== null) {
            $context->setGroups($groups);
        }

        return json_decode($this->serializer->serialize($value, 'json', $context), true);
    }
}
