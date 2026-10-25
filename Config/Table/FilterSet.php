<?php

namespace Padam87\AdminBundle\Config\Table;

use Symfony\Component\Translation\TranslatableMessage;

class FilterSet
{
    public const DEFAULT_GROUP = 'default';

    private TranslatableMessage|string|null $name = null;

    // the name of the icon (see IconClass)
    private ?string $icon = null;

    private array $data = [];

    private string $group = self::DEFAULT_GROUP;

    public static function create(string $key): static
    {
        return new static($key);
    }

    public function __construct(private string $key)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getName(): TranslatableMessage|string|null
    {
        return $this->name;
    }

    public function setName(TranslatableMessage|string|null $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string|null $icon the name of the icon (see IconClass)
     */
    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): self
    {
        $this->group = $group;

        return $this;
    }
}
