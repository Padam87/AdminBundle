<?php

namespace Padam87\AdminBundle\DependencyInjection;

use Padam87\AdminBundle\Config\Action\Action;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
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
            ->end()
        ;

        return $treeBuilder;
    }
}
