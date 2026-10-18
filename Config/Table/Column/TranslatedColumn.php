<?php

namespace Padam87\AdminBundle\Config\Table\Column;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;
use Twig\Template;

#[Serializer\ExclusionPolicy('all')]
class TranslatedColumn extends Column
{
    private array $translationParameters = [];

    private ?string $translationDomain = null;

    public function getTemplate(string $format = 'html'): Template|string
    {
        return '@Padam87Admin/table/column/translated.html.twig';
    }

    /**
     * Translated by the list value normalizer, translatable values (eg. enums) keep their own parameters and domain.
     */
    public function getListValue(object|array $entity): mixed
    {
        $value = $this->getValue($entity);

        return match (true) {
            $value === null, $value instanceof TranslatableInterface => $value,
            default => new TranslatableMessage((string) $value, $this->translationParameters, $this->translationDomain),
        };
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
