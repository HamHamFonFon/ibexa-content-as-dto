<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Repository;

use Exception;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use Kaliop\IbexaContentDto\Entity\ContentDtoInterface;
use Kaliop\IbexaContentDto\Services\Factory\IbexaDtoFactory;
use Kaliop\IbexaContentDto\Services\Iterators\DtoCollection;
use Kaliop\IbexaContentDto\Services\Traits\IbexaServicesTrait;
use Kaliop\IbexaContentDto\Services\Traits\SymfonyServicesTraits;
use ReflectionClass;
use ReflectionException;
use ScemBundle\Repository\ezObjectsRepository\IbxRepositoryInterface;

/**
 *
 */
abstract class AbstractContentRepository
{
    use IbexaServicesTrait, SymfonyServicesTraits;

    private SiteAccess $siteAccess;

    /**
     * @param $siteAccess
     */
    public function __construct($siteAccess)
    {
        $this->siteAccess = $siteAccess;
    }

    abstract public function getContentTypeId(): string;
    abstract public function getContentDTO(): ?string;

    /**
     * @param string $contentRemoteId
     *
     * @return Content
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    protected function getContentByRemoteId(string $contentRemoteId): Content
    {
        return $this->contentService->loadContentByRemoteId($contentRemoteId);
    }

    /**
     * @param string $locationRemoteId
     *
     * @return Location
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    protected function getLocationByRemoteId(string $locationRemoteId): Location
    {
        return $this->locationService->loadLocationByRemoteId($locationRemoteId);
    }

    /**
     * @return mixed|string|null
     */
    protected function getSiteAccessName(): string
    {
        return $this->siteAccess->name;
    }

    /**
     * @param Content $content
     * @param string|null $currentLanguage
     * @param bool|null $isChild
     *
     * @return ContentDtoInterface
     * @throws ReflectionException
     */
    protected function buildDtoFromContent(Content $content, ?string $currentLanguage, ?bool $isChild = false): ?ContentDtoInterface
    {
        if (true === $isChild) {
            $contentTypeIdentifier = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId)->identifier;
            $strDto = $this->getChildDtoClassname($contentTypeIdentifier);
        } else {
            $strDto = $this->getContentDTO();
        }

        if (is_null($strDto)) {
            return null;
        }

        $dto = new $strDto();
        $dto
            ->setContentTypeIdentifier($this->getContentTypeId());

        $location = $this->locationService->loadLocation($content->contentInfo->mainLocationId);
        $currentLanguage = $currentLanguage ?? $this->languageService->getDefaultLanguageCode();

        $dto = IbexaDtoFactory::hydrateDto($dto, $content, $location, $currentLanguage);

        return $dto->addNestedDto($dto, $currentLanguage);
    }


    /**
     * Adding nested Dto of the parent content.
     * Most time, nested dto are Dto from ezObjectRelationList
     *
     * @param ContentDtoInterface $parentDto
     * @param string $currentLanguage
     *
     * @return void
     * @throws ReflectionException
     */
    private function addNestedDto(ContentDtoInterface $parentDto, string $currentLanguage): ContentDtoInterface
    {
        $listFields = $parentDto->listObjectRelationListFields();
        if (empty($listFields)) {
            return $parentDto;
        }

        /**
         * For each relation list, we get content from destinationContentId
         * And build a dto, added to Collection
         */
        foreach ($listFields as $field) {
            $reflexionClass = new ReflectionClass($parentDto);
            $property = $reflexionClass->getProperty($field);
            $property->setAccessible(true);

            $collectionDto = new DtoCollection();
            foreach ($property->getValue($parentDto) as $destinationContentId) {
                $content = $this->contentService->loadContent($destinationContentId);
                try {
                    $subDto = $this->buildDtoFromContent($content, $currentLanguage, true);
                    if ($subDto instanceof ContentDtoInterface) {
                        $collectionDto->addSubDto($subDto);
                    }
                } catch (ReflectionException  | NotFoundException | UnauthorizedException | Exception $e) {}
            }

            $property->setValue($parentDto, $collectionDto);
            $property->setAccessible(false);
        }

        return $parentDto;
    }

    /**
     * Get DTO Classname based on CONTENT_TYPE_ID const value
     *
     * @throws ReflectionException
     */
    private function getChildDtoClassname(string $contentTypeId): ?string
    {
        /**
         * List of class who extends AbstractIbxRepository
         */
        $repoChildrenClass = array_filter(get_declared_classes(), static function($class) {
            return is_subclass_of($class, self::class);
        });

        foreach ($repoChildrenClass as $childrenClass) {
            $childReflector = new ReflectionClass($childrenClass);
            if ($childReflector->implementsInterface(ContentRepositoryInterface::class)) {
                /**
                 * @todo : move this condition into IbxRepositoryPass::class ?
                 */
                if (!$childReflector->hasConstant('CONTENT_TYPE_ID')) {
                    throw new \RuntimeException(sprintf('"%s" doesn\'t seem to have constant "%s" declared, please add it.', $childrenClass, 'CONTENT_TYPE_ID'));
                }

                if ($contentTypeId === $childReflector->getConstant('CONTENT_TYPE_ID')) {
                    $reflectionMethod = $childReflector->getMethod('getContentDTO');
                    return $reflectionMethod->invoke(new $childrenClass($this->siteAccess));
                }
            }
        }

        return null;
    }
}