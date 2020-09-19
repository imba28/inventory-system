<?php


namespace App\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('app');
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('database')
                    ->children()
                        ->enumNode('driver')->values(['mysql', 'sqlite'])->defaultValue('mysql')->end()
                        ->scalarNode('host')->defaultValue('db')->end()
                        ->scalarNode('database')->isRequired()->end()
                        ->scalarNode('user')->isRequired()->end()
                        ->scalarNode('password')->defaultNull()->end()
                        ->integerNode('port')->defaultValue(3306)->end()
                        ->scalarNode('table_prefix')->defaultValue('v')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;

    }
}
