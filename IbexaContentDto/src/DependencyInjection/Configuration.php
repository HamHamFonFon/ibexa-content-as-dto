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
                ->scalarNode('directory_repository')->end()
                ->scalarNode('directory_dto')->end()
                ->scalarNode('namespace')->end()
            ->end()
        ->end()
        ;

        return $treeBuilder;
    }

}
