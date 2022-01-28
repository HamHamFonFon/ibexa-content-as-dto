<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Services\Factory;

use Kaliop\IbexaContentDto\Entity\DtoInterface;
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
     * @param DtoInterface $dto
     * @param $content
     * @param $location
     * @param string $currentLanguage
     *
     * @return DtoInterface
     */
    public static function hydrateDto(
        DtoInterface $dto,
                     $content,
                     $location,
        string       $currentLanguage
    ): DtoInterface
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