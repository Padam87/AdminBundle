<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use Twig\Template;

class TranslatedColumn extends Column
{
    private array $translationParameters = [];

    private ?string $translationDomain = null;

    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/translated.html.twig';
    }

    public function getTranslationParameters(): array
    {
        return $this->translationParameters;
    }

    public function setTranslationParameters(array $translationParameters): self
    {
        $this->translationParameters = $translationParameters;

        return $this;
    }

    public function getTranslationDomain(): ?string
    {
        return $this->translationDomain;
    }

    public function setTranslationDomain(?string $translationDomain): self
    {
        $this->translationDomain = $translationDomain;

        return $this;
    }
}
