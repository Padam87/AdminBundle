<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use JMS\Serializer\Annotation as Serializer;

/**
 * Column rendered by a twig block, its value is HTML in serialized lists (see Padam87\AdminBundle\Listing\ListBuilder).
 *
 * The block is rendered with `config`, `column`, `entity` (the row) and `realEntity` in its context.
 * By default the block is `column_<property>` of the index template.
 */
#[Serializer\ExclusionPolicy('all')]
class TwigColumn extends Column
{
    private ?string $block = null;

    /**
     * Template containing the block, the index template of the admin when null.
     */
    private ?string $template = null;

    public function getBlock(): string
    {
        return $this->block ?? str_replace(['.', '?'], ['_', ''], 'column_' . $this->property);
    }

    public function setBlock(?string $block): self
    {
        $this->block = $block;

        return $this;
    }

    public function getBlockTemplate(): ?string
    {
        return $this->template;
    }

    public function setBlockTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('type')]
    public function getListType(): string
    {
        return 'html';
    }
}
