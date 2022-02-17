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
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('dir_repository')->isRequired()->end()
                ->scalarNode('dir_dto')->isRequired()->end()
                ->arrayNode('content_type_groups')->prototype('scalar')->treatNullLike(array())->end()
            ->end()
        ;

        return $treeBuilder;
    }

}
