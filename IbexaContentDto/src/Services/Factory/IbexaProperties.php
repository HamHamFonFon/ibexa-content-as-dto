<?php

namespace Kaliop\IbexaContentDto\Services\Factory;

use Kaliop\IbexaContentDto\Entity\DtoInterface;


final class IbexaProperties
{
    public function __construct(
        private readonly RouterInterface $router
    ) { }

    public function attachContent(DtoInterface $dto, Content $content, ?string $currentLanguage): DtoInterface
    {
        $dto->setContent($content);

        /** @todo : check multi positionnement */
        $name = $content->getName($currentLanguage);
        if (empty($name)) {
            $dto->setName('No translated name');
        } else {
            $dto->setName($name);
        }

        if (!empty($content->contentInfo->remoteId)) {
            $dto->setContentRemoteId($content->contentInfo->remoteId);
        }

        return $dto;
    }

    public function attachLocation(DtoInterface $dto, Location $location): DtoInterface
    {
        $dto
            ->setLocation($location)
            ->setUrl($this->router->generate('ez_urlalias', ['location' => $location]));

        if (!empty($location->remoteId)) {
            $dto->setLocationRemoteId($location->remoteId);
        }

        return $dto;
    }


}