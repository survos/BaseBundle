<?php

namespace Survos\BaseBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $rootName = 'survos_admin';
        $treeBuilder = new TreeBuilder($rootName);
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->scalarNode('theme')
            ->info('the theme code for rendering templates')
            ->end()
            ->arrayNode('routes')
                ->normalizeKeys(false)
                // ->useAttributeAsKey('name', false)
                ->defaultValue(array())
                ->info('The list of entities to manage in the administration zone.')
                ->prototype('variable')
                ->end()
//            ->booleanNode('unicorns_are_real')->defaultTrue()->end()
//            ->integerNode('min_sunshine')->defaultValue(3)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
