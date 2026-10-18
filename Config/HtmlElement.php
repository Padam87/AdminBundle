<?php

namespace Padam87\AdminBundle\Config;

use JMS\Serializer\Annotation as Serializer;

#[Serializer\ExclusionPolicy('all')]
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

    public function normalizeAttribute($array, string $prefix = ''): array {
        $result = [];

        foreach($array as $key=>$value) {
            if(is_array($value)) {
                $result += flatten($value, $prefix . '-'. $key );
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

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('tag')]
    public function getTagName(): string
    {
        return $this->tagName;
    }

    public function setTagName(string $tagName): self
    {
        $this->tagName = $tagName;

        return $this;
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('attrs')]
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
