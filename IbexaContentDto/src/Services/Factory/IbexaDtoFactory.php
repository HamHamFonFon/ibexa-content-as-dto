<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Services\Factory;

use Kaliop\IbexaContentDto\Entity\ContentDtoInterface;
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
     * @param ContentDtoInterface $dto
     * @param $content
     * @param $location
     * @param string $currentLanguage
     *
     * @return ContentDtoInterface
     */
    public static function hydrateDto(
        ContentDtoInterface $dto,
                            $content,
                            $location,
        string              $currentLanguage
    ): ContentDtoInterface
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