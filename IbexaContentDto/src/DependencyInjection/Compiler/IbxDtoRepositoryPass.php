<?php

namespace Kaliop\IbexaContentDto\DependencyInjection\Compiler;

use Kaliop\IbexaContentDto\Repository\AbstractContentRepository;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class IbxDtoRepositoryPass implements CompilerPassInterface
{
    public const TAG_DTO_REPOSITORY = 'dto_repository';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(AbstractContentRepository::class)) {
            return;
        }

        $taggedRepositoriesService = $container->findTaggedServiceIds(self::TAG_DTO_REPOSITORY);
        foreach ($taggedRepositoriesService as $id => $tags) {
            $dtoRepository = $container->findDefinition($id);
            foreach (array_keys($taggedRepositoriesService) as $childRepository) {
                $dtoRepository->addMethodCall('addListRepository', [$childRepository]);
            }
        }
    }
}