<?php

namespace AppBundle\DependencyInjection;


use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('app');

        $rootNode
            ->children()
            ->scalarNode('supervisor_hostname')->end()
            ->scalarNode('supervisor_port')->end()
            ->scalarNode('supervisor_username')->end()
            ->scalarNode('supervisor_password')->end()
        ;

        return $treeBuilder;
    }
}