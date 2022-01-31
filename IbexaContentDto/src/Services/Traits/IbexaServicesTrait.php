<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Services\Traits;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\LanguageService;


/**
 *
 */
trait IbexaServicesTrait
{
    protected LocationService $locationService;
    protected ContentService $contentService;
    protected ContentTypeService $contentTypeService;
    protected LanguageService $languageService;
    protected SearchService $searchService;

    /**
     * @required
     * @param LocationService $locationService
     *
     * @return void
     */
    public function injectLocationService(LocationService $locationService): void
    {
        $this->locationService = $this->locationService ?: $locationService;
    }


    /**
     * @required
     * @param ContentService $contentService
     */
    public function injectContentService(ContentService $contentService): void
    {
        $this->contentService = $this->contentService ?: $contentService;
    }


    /**
     * @required
     *
     * @param ContentTypeService $contentTypeService
     */
    public function injectContentTypeService(ContentTypeService $contentTypeService): void
    {
        $this->contentTypeService = $this->contentTypeService ?: $contentTypeService;
    }

    /**
     * @required
     * @param LanguageService $languageService
     *
     * @return void
     */
    public function injectLanguageService(LanguageService $languageService): void
    {
        $this->languageService = $this->languageService ?: $languageService;
    }

    /**
     * @required
     * @param SearchService $searchService
     *
     * @return void
     */
    public function injectSearchService(SearchService $searchService): void
    {
        $this->searhService = $this->searchService ?: $searchService;
    }
}