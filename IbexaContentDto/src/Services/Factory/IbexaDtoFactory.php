<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Services\Factory;

use Kaliop\IbexaContentDto\Entity\IbexaContentDtoInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 *
 */
final class IbexaDtoFactory
{
    private RouterInterface $router;

    /**
     *
     */
    public function __construct()
    {
        global $kernel;
        $this->router = $kernel->getContainer()->get('router');
    }

    /**
     * @param IbexaContentDtoInterface $dto
     * @param $content
     * @param $location
     * @param string $currentLanguage
     *
     * @return IbexaContentDtoInterface
     */
    public static function hydrateDto(
        IbexaContentDtoInterface $dto,
        $content,
        $location,
        string $currentLanguage
    ): IbexaContentDtoInterface
    {
        $self = new static;
        $router = $self->router;

        return $dto;
    }

    /**
     * @return mixed
     */
    private static function getValue(): mixed
    {

    }
}