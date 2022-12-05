<?php

namespace Padam87\AdminBundle\Config;

class HtmlElement
{
    public function __construct(private string $tagName, private array $attributes = [])
    {
    }

    public static function fromConfiguration(array $config): static
    {
        return new static($config['element'], $config['attributes']);
    }

    public function getTagName(): string
    {
        return $this->tagName;
    }

    public function setTagName(string $tagName): self
    {
        $this->tagName = $tagName;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }
}
