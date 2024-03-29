<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Repository;

use ErrorException;
use Exception;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use Kaliop\IbexaContentDto\Entity\DtoInterface;
use Kaliop\IbexaContentDto\Repository\Query\GetSubItemsQueryHandler;
use Kaliop\IbexaContentDto\Services\Factory\IbexaDtoFactory;
use Kaliop\IbexaContentDto\Services\Factory\IbexaProperties;
use Kaliop\IbexaContentDto\Services\Iterators\DtoCollection;
use Kaliop\IbexaContentDto\Services\Traits\IbexaServicesTrait;
use Kaliop\IbexaContentDto\Services\Traits\SymfonyServicesTrait;
use ReflectionClass;
use ReflectionException;
use RuntimeException;


/**
 *
 */
abstract class AbstractContentRepository
{
    use IbexaServicesTrait, SymfonyServicesTrait;

    protected array $listRepositories;

    public const OFFSET = 0;
    public const LIMIT = 99999;

    /**
     * @param SiteAccess $siteAccess
     * @param IbexaProperties $ibexaProperties
     */
    public function __construct(
        private readonly SiteAccess      $siteAccess,
        private readonly IbexaProperties $ibexaProperties
    )
    { }

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
     * @return string
     */
    protected function getSiteAccessName(): string
    {
        return $this->siteAccess->name;
    }

    /**
     * @param string $repository
     *
     * @return void
     */
    public function addListRepository(string $repository): void
    {
        $this->listRepositories[] = $repository;
    }

    public function getRepositories(): ?array
    {
        return $this->listRepositories;
    }

    /**
     * @param Location $location
     * @param string|null $currentLanguage
     * @param bool|null $isChild
     * @return DtoInterface|null
     * @throws ReflectionException
     * @throws ErrorException
     */
    protected function buildDtoFromLocation(Location $location, ?string $currentLanguage, ?bool $isChild = false): ?DtoInterface
    {
        $dto = $this->buildDtoFromContent($location->getContent(), $currentLanguage, $isChild);
        if (!is_null($dto)) {
            $dto = $this->ibexaProperties->attachLocation($location);
        }

        return $dto;
    }

    /**
     * @param Content $content
     * @param string|null $currentLanguage
     * @param bool|null $isChild
     *
     * @return DtoInterface|null
     * @throws ReflectionException
     * @throws ErrorException
     */
    protected function buildDtoFromContent(Content $content, ?string $currentLanguage, ?bool $isChild = false): ?DtoInterface
    {
        set_error_handler(static function($severity, $errstr, $errfile, $errline) {
            if (0 === error_reporting()) {
                return false;
            }
            throw new ErrorException($errstr, 0, $severity, $errfile, $errline);
        });

        if (true === $isChild) {
            $contentTypeIdentifier = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId)->identifier;
            $strDto = $this->getChildDtoClassname($contentTypeIdentifier);
        } else {
            $strDto = $this->getContentDTO();
            $contentTypeIdentifier = $this->getContentTypeId();
        }

        if (is_null($strDto)) {
            return null;
        }

        $dto = new $strDto();
        $dto
            ->setContentTypeIdentifier($contentTypeIdentifier)
        ;

        $mainLocation = $this->locationService->loadLocation($content->contentInfo->mainLocationId);
        $currentLanguage = $currentLanguage ?? $this->languageService->getDefaultLanguageCode();

        try {
            $dto = (IbexaDtoFactory::hydrateDto($dto, $content, $currentLanguage));
            $dto = $this->ibexaProperties->attachContent($content);
            $dto = $this->ibexaProperties->attachLocation($mainLocation);
            $dto = $this->addNestedDto($dto, $currentLanguage);
        } catch (Exception $e) {}

        return $dto;
    }


    /**
     * Adding nested Dto of the parent content.
     * Most time, nested dto are Dto from ezObjectRelationList
     *
     * @param DtoInterface $parentDto
     * @param string $currentLanguage
     *
     * @return DtoInterface
     * @throws ReflectionException
     */
    private function addNestedDto(DtoInterface $parentDto, string $currentLanguage): DtoInterface
    {
        $listFields = $parentDto->listObjectRelationListFields();
        if (is_null($listFields)) {
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
            $values = $property->getValue($parentDto);
            if (empty($values)) {
                continue;
            }
            foreach ($values as $destinationContentId) {
                $nestedDto = null;
                try {
                    $content = $this->contentService->loadContent($destinationContentId);
                    if (!$content instanceof Content) {
                        continue;
                    }
                    $nestedDto = $this->buildDtoFromContent($content, $currentLanguage, true);
                } catch (ReflectionException | NotFoundException | UnauthorizedException | Exception $e) {}

                if ($nestedDto instanceof DtoInterface) {
                    $collectionDto->addSubDto($nestedDto);
                } else {
                    throw new RuntimeException('Error add nested DTO');
                }
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
        foreach ($this->getRepositories() as $childrenClass) {
            $childReflector = new ReflectionClass($childrenClass);
            if ($childReflector->implementsInterface(ContentRepositoryInterface::class)) {
                /**
                 * @todo : move this condition into IbxRepositoryPass::class ?
                 */
                if (!$childReflector->hasConstant('CONTENT_TYPE_ID')) {
                    throw new RuntimeException(sprintf('"%s" doesn\'t seem to have constant "%s" declared, please add it.', $childrenClass, 'CONTENT_TYPE_ID'));
                }

                if ($contentTypeId === $childReflector->getConstant('CONTENT_TYPE_ID')) {
                    return $childReflector->getMethod('getContentDTO')->invoke(new $childrenClass($this->siteAccess));
                }
            } else {
                $msg = sprintf('"%s" doesn\'t seem to implement interface "%s", please add it.', $childrenClass, ContentRepositoryInterface::class);
                throw new RuntimeException($msg);
            }
        }

        return null;
    }

    /**
     * @param Content $parentContent
     * @param array|null $contentTypeIdentifiers
     * @param array|null $sortClause
     * @param array|null $sectionsIds
     * @param int|null $offset
     * @param int|null $limit
     * @param array|null $excludedContentTypeIdentifiers
     *
     * @return DtoCollection
     * @throws ReflectionException|ErrorException
     */
    public function buildCollectionFromParent(Content $parentContent, ?array $contentTypeIdentifiers, ?array $sortClause, ?array $sectionsIds, ?int $offset, ?int $limit, ?array $excludedContentTypeIdentifiers): DtoCollection
    {
        $offset = $offset ?? self::OFFSET;
        $limit = $limit ?? self::LIMIT;

        $dtoCollection = new DtoCollection();

        $parentLocation = $this->locationService->loadLocation($parentContent->contentInfo->mainLocationId);
        $currentLanguage = $this->languageService->getDefaultLanguageCode();

        $generatorListLocation = function() use($parentLocation, $contentTypeIdentifiers, $sortClause, $currentLanguage, $sectionsIds, $offset, $limit, $excludedContentTypeIdentifiers) {
            $query = new GetSubItemsQueryHandler;
            $searchResult = $this->searchService->findLocations(
                $query(
                    $parentLocation->id,
                    $contentTypeIdentifiers,
                    $sortClause,
                    $sectionsIds,
                    $offset,
                    $limit,
                    $excludedContentTypeIdentifiers,
                    [$currentLanguage],
                    true
                )
            );

            foreach ($searchResult->searchHits as $searchHit) {
                $result = $searchHit->valueObject;
                yield $result->getContent();
            }
        };

        foreach ($generatorListLocation() as $content) {
            $dto = $this->buildDtoFromContent($content, $currentLanguage, true);
            if ($dto instanceof DtoInterface) {
                $dtoCollection->addSubDto($dto);
            }
        }

        return $dtoCollection;
    }
}