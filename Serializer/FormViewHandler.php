<?php

namespace Padam87\AdminBundle\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormView;

/**
 * Serializes form views (vars and children), eg. the filter form of serialized lists.
 */
class FormViewHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => ChoiceView::class,
                'method' => 'serializeChoiceView',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => FormView::class,
                'method' => 'serializeFormView',
            ],
        ];
    }

    public function serializeChoiceView(JsonSerializationVisitor $visitor, ChoiceView $view, array $type, Context $context): array
    {
        return [
            'value' => $view->value,
            'label' => $view->label,
            'attr' => $view->attr,
        ];
    }

    public function serializeFormView(JsonSerializationVisitor $visitor, FormView $view, array $type, Context $context): array
    {
        return [
            'vars' => $visitor->visitArray($view->vars, []),
            'children' => $visitor->visitArray($view->children, ['string', 'array']),
        ];
    }
}
