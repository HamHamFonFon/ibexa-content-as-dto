<?php

namespace Kaliop\IbexaContentDto\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 *
 */
class Configuration implements ConfigurationInterface
{

    public const TREE_ROOT_NODE = 'ibx_content_dto';

    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::TREE_ROOT_NODE);
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('dir_repository')
                ->end()
                ->scalarNode('dir_dto')
                ->end()
                ->scalarNode('content_type_groups')
                    ->defaultValue('Content')
                ->end()
            ->end()
        ->end()
        ;

        return $treeBuilder;
    }

}
