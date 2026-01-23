<?php

namespace Padam87\AdminBundle\DependencyInjection;

use Padam87\AdminBundle\Attribute\Admin;
use Padam87\AdminBundle\Config\AdminConfig;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Padam87\AdminBundle\Config\AdminConfigFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

        $container->registerAttributeForAutoconfiguration(
            Admin::class,
            static function (ChildDefinition $definition, Admin $attribute, \ReflectionClass $reflector) use ($container): void {
                $refl = new \ReflectionClass($attribute->entityFqcn);
                $adminConfig = $container->setDefinition(
                    'padam87_admin.admin_config.' . $refl->getShortName(),
                    new Definition(AdminConfig::class)
                        ->setFactory([$container->getDefinition(AdminConfigFactory::class), 'create'])
                        ->setArguments([
                            $reflector->getName(),
                        ])
                );
                $adminConfig->addTag('padam87_admin.admin_config');

                $definition
                    ->addTag('padam87_admin.controller')
                    ->addMethodCall(
                        'setConfig',
                        [
                            $adminConfig,
                        ]
                    );
            }
        );

        $container->setParameter('padam87_admin.config', $config);
    }
}
