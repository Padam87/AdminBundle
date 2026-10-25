<?php

namespace Padam87\AdminBundle\DependencyInjection;

use Padam87\AdminBundle\Config\Action\Action;
use Padam87\AdminBundle\Config\Action\ActionBehavior;
use Padam87\AdminBundle\Config\Action\ActionVariant;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * The default actions: behavior, variant, icon (a name, see icon_class), the method of a submitted action, the confirmation.
     */
    private static array $actionConfig = [
        Action::CREATE => ['behavior' => ActionBehavior::Navigate, 'variant' => ActionVariant::Success, 'icon' => 'plus'],
        Action::EDIT => ['behavior' => ActionBehavior::Navigate, 'variant' => ActionVariant::Secondary, 'icon' => 'pen'],
        Action::DELETE => ['behavior' => ActionBehavior::Submit, 'variant' => ActionVariant::Danger, 'icon' => 'trash', 'method' => 'DELETE'],
        Action::BATCH_DELETE => ['behavior' => ActionBehavior::Submit, 'variant' => ActionVariant::Danger, 'icon' => 'trash'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('padam87_admin');

        $treeBuilder->getRootNode()
            ->children()
                // the class of an icon by its name, eg. Tabler Icons: 'ti ti-%s'
                ->scalarNode('icon_class')->defaultValue('bi bi-%s')->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode(Action::INDEX)->defaultValue('admin_template/index.html.twig')->end()
                        ->scalarNode(Action::CREATE)->defaultValue('admin_template/create.html.twig')->end()
                        ->scalarNode(Action::EDIT)->defaultValue('admin_template/edit.html.twig')->end()
                    ->end()
                ->end()
                ->arrayNode('translations')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('domains')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('messages')->end()
                                ->scalarNode('action')->defaultValue('messages')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('actions')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->append($this->actionConfig(Action::CREATE))
                        ->append($this->actionConfig(Action::EDIT))
                        ->append($this->actionConfig(Action::DELETE))
                        ->append($this->actionConfig(Action::BATCH_DELETE))
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function actionConfig(string $action): NodeDefinition
    {
        $defaults = self::$actionConfig[$action];

        $treeBuilder = new TreeBuilder($action);
        $node = $treeBuilder->getRootNode();

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->enumNode('behavior')
                    ->values(array_map(fn (ActionBehavior $behavior) => $behavior->value, ActionBehavior::cases()))
                    ->defaultValue($defaults['behavior']->value)
                ->end()
                ->enumNode('variant')
                    ->values(array_map(fn (ActionVariant $variant) => $variant->value, ActionVariant::cases()))
                    ->defaultValue($defaults['variant']->value)
                ->end()
                ->scalarNode('method')->defaultValue($defaults['method'] ?? null)->end()
                // the name of the icon (see icon_class)
                ->scalarNode('icon')->defaultValue($defaults['icon'])->end()
                ->scalarNode('confirmation')->defaultNull()->end()
            ->end()
        ;

        return $node;
    }
}
