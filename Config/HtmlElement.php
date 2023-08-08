<?php

namespace Padam87\AdminBundle\Config;

class HtmlElement
{
    private array $attributes = [];

    public function __construct(private string $tagName, array $attributes = [])
    {
        foreach ($attributes as $k => $v) {
            if (is_array($v)) {
                $this->attributes = array_merge($this->attributes, $this->normalizeAttribute($v, $k));
            } else {
                $this->attributes[$k] = $v;
            }
        }
    }

    function normalizeAttribute($array, $prefix = '') {
        $result = [];

        foreach($array as $key=>$value) {
            if(is_array($value)) {
                $result = $result + flatten($value, $prefix . '-'. $key );
            } else {
                $result[$prefix . '-'. $key ] = $value;
            }
        }
        return $result;
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
