<?php

namespace Padam87\AdminBundle\Config\Table;

use Padam87\AdminBundle\Config\HtmlElement;
use Symfony\Component\Translation\TranslatableMessage;

class FilterSet
{
    private TranslatableMessage|string|null $name = null;

    private ?HtmlElement $icon = null;

    private array $data = [];

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

    public function getIcon(): ?HtmlElement
    {
        return $this->icon;
    }

    public function setIcon(?HtmlElement $icon): self
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
}
