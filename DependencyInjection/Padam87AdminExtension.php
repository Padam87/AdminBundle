<?php

namespace Padam87\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Padam87\AdminBundle\Config\AdminConfigFactory;
use Padam87\AdminBundle\Controller\AdminController;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class Padam87AdminExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container->registerForAutoconfiguration(AdminController::class)
            ->addTag('padam87_admin.controller')
            ->addMethodCall(
                'init',
                [
                    $container->getDefinition(AdminConfigFactory::class),
                ]
            )
        ;

        $container->setParameter('padam87_admin.config', $config);
    }
}
