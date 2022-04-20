<?php

namespace Kaliop\IbexaContentDto;

use Kaliop\IbexaContentDto\DependencyInjection\Compiler\IbxDtoRepositoryPass;
use Kaliop\IbexaContentDto\Repository\ContentRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 *
 */
class IbexaContentDtoBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function build(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(ContentRepositoryInterface::class)
            ->addTag(IbxDtoRepositoryPass::TAG_DTO_REPOSITORY);
    }
}