<?php

namespace ByDm\FakerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('by_dm_faker');
        $rootNode
                 ->beforeNormalization()
                     ->ifTrue(function ($v) { return is_array($v) && !array_key_exists('fakers', $v); })
                     ->then(function ($v) {
                         $facker = array();
                         foreach($v as $key => $value) {
                             $facker[$key] = $value;
                             unset($v[$key]);
                         }
                         
                         $v['fakers'] = array('default' => $facker);
                         
                         return $v;
                     })
                 ->end()
                 ->append($this->getFakersSection());

        return $treeBuilder;
    }
    
    /**
     * Returns fakers section
     * 
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function getFakersSection()
    {
        $treeBuilder = new TreeBuilder();
        return $treeBuilder->root('fakers')
                            ->requiresAtLeastOneElement()
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('locale')
                                        ->defaultNull()
                                    ->end()
                                    ->integerNode('seed')
                                        ->defaultNull()
                                    ->end()
                                    ->booleanNode('use_populator')
                                        ->defaultTrue()
                                    ->end()
                                    ->scalarNode('entity_manager')
                                        ->defaultValue('doctrine.orm.default_entity_manager')
                                    ->end()
                                ->end()
                            ->end()
                ;
        
    }
}
