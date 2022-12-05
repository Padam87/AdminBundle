<?php

namespace Padam87\AdminBundle\DependencyInjection;

use Padam87\AdminBundle\Config\Action\Action;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private static $actionConfig = [
        Action::CREATE => [
            'control' => [
                'element' => 'a',
                'attributes' => [
                    'class' => 'btn btn-success',
                ],
            ],
            'icon' => [
                'element' => 'i',
                'attributes' => [
                    'class' => 'bi bi-plus',
                ],
            ],
        ],
        Action::EDIT => [
            'control' => [
                'element' => 'a',
                'attributes' => [
                    'class' => 'btn btn-secondary',
                ],
            ],
            'icon' => [
                'element' => 'i',
                'attributes' => [
                    'class' => 'bi bi-pen',
                ],
            ],
        ],
        Action::DELETE => [
            'control' => [
                'element' => 'button',
                'attributes' => [
                    'class' => 'btn btn-danger',
                    'type' => 'submit',
                ],
            ],
            'icon' => [
                'element' => 'i',
                'attributes' => [
                    'class' => 'bi bi-trash',
                ],
            ],
        ],
        Action::BATCH_DELETE => [
            'control' => [
                'element' => 'button',
                'attributes' => [
                    'class' => 'btn btn-danger',
                    'type' => 'submit',
                    'form' => 'batch',
                ],
            ],
            'icon' => [
                'element' => 'i',
                'attributes' => [
                    'class' => 'bi bi-trash',
                ],
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('padam87_admin');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode(Action::INDEX)->defaultValue('admin_template/index.html.twig')->end()
                        ->scalarNode(Action::CREATE)->defaultValue('admin_template/create.html.twig')->end()
                        ->scalarNode(Action::EDIT)->defaultValue('admin_template/edit.html.twig')->end()
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
        $treeBuilder = new TreeBuilder($action);
        $node = $treeBuilder->getRootNode();
        $node->addDefaultsIfNotSet();

        $current = $node->children();

        foreach (self::$actionConfig[$action] as $name => $value) {
            $this->addConfig($current, $name, $value);
        }

        return $node;
    }

    private function addConfig(NodeBuilder $current, $name, $value)
    {
        if (is_array($value)) {
            $node = $current->arrayNode($name)->addDefaultsIfNotSet();

            foreach ($value as $k => $v) {
                $this->addConfig($node->children(), $k, $v);
            }
        } else {
            $current->scalarNode($name)->defaultValue($value);
        }
    }
}
