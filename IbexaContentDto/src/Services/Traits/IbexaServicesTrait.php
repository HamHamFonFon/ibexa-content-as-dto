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

    #[Required]
    public function injectLocationService(LocationService $locationService): void
    {
        $this->locationService = $this->locationService ?: $locationService;
    }

    #[Required]
    public function injectContentService(ContentService $contentService): void
    {
        $this->contentService = $this->contentService ?: $contentService;
    }

    #[Required]
    public function injectContentTypeService(ContentTypeService $contentTypeService): void
    {
        $this->contentTypeService = $this->contentTypeService ?: $contentTypeService;
    }

    #[Required]
    public function injectLanguageService(LanguageService $languageService): void
    {
        $this->languageService = $this->languageService ?: $languageService;
    }

    #[Required]
    public function injectSearchService(SearchService $searchService): void
    {
        $this->searhService = $this->searchService ?: $searchService;
    }
}
