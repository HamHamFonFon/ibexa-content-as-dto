<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Services\Traits;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;

/**
 *
 */
trait IbexaServicesTrait
{
    protected LocationService $locationService;
    protected ContentService $contentService;
    protected ContentTypeService $contentTypeService;

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
     * @param ContentTypeService $contentService
     */
    public function injectContentTypeService(ContentTypeService $contentTypeService): void
    {
        $this->contentTypeService = $this->contentTypeService ?: $contentTypeService;
    }
}