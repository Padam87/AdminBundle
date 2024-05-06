<?php

namespace Padam87\AdminBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class LabelExtension extends AbstractTypeExtension
{
    private CamelCaseToSnakeCaseNameConverter $converter;

    public function __construct(CamelCaseToSnakeCaseNameConverter $converter)
    {
        $this->converter = $converter;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['name_underscored'] = $this->converter->normalize($view->vars['name']);

        if ($view->vars['label'] === null && $view->vars['label_format'] !== null) {
            $view->vars['label'] = str_replace(
                ['%name%', '%name_underscored%'],
                [$view->vars['name'], $view->vars['name_underscored']],
                (string) $view->vars['label_format']
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
