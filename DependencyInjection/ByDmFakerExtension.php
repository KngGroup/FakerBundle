<?php

namespace ByDm\FakerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ByDmFakerExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        
        foreach($config['fakers'] as $name => $faker) {
            $fakerDefinition = new Definition('%by_dm_faker.class%', array($faker['locale']));
            $fakerDefinition->setFactoryClass('%by_dm_faker.factory_class%')
                            ->setFactoryMethod('create');
            
            if (null !== $faker['seed']) {
                $fakerDefinition->addMethodCall('seed', array($faker['seed']));
            }
            
            $serviceName = 'by_dm_faker.' . $name;
            $container->setDefinition($serviceName, $fakerDefinition);

            if ($faker['use_populator']) {
                $populatorDefinition = new Definition(
                    '%by_dm_faker.populator_class%',
                    array(new Reference($serviceName), new Reference($faker['entity_manager']))
                );
                
                $container->setDefinition(
                    $serviceName . '_populator',
                    $populatorDefinition
                );
            }
        }
        
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
