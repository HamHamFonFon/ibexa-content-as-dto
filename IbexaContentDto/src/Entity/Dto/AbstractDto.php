<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Entity\Dto;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;

/**
 *
 */
abstract class AbstractDto
{
    private Content $content;
    private Location $mainLocation;
    private string $mainIbexaUrl;
    private string $contentTypeIdentifier;
    private string $contentRemoteId;
    private string $locationRemoteId;

    /**
     * @return Content
     */
    public function getContent(): Content
    {
        return $this->content;
    }

    /**
     * @param Content $content
     *
     * @return AbstractDto
     */
    public function setContent(Content $content): AbstractDto
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return Location
     */
    public function getMainLocation(): Location
    {
        return $this->mainLocation;
    }

    /**
     * @param Location $mainLocation
     * @return AbstractDto
     */
    public function setMainLocation(Location $mainLocation): AbstractDto
    {
        $this->mainLocation = $mainLocation;
        return $this;
    }

    /**
     * @return string
     */
    public function getMainIbexaUrl(): string
    {
        return $this->mainIbexaUrl;
    }

    /**
     * @param string $mainIbexaUrl
     * @return AbstractDto
     */
    public function setMainIbexaUrl(string $mainIbexaUrl): AbstractDto
    {
        $this->mainIbexaUrl = $mainIbexaUrl;
        return $this;
    }


    /**
     * @return string
     */
    public function getContentTypeIdentifier(): string
    {
        return $this->contentTypeIdentifier;
    }

    /**
     * @param string $contentTypeIdentifier
     *
     * @return AbstractDto
     */
    public function setContentTypeIdentifier(string $contentTypeIdentifier): AbstractDto
    {
        $this->contentTypeIdentifier = $contentTypeIdentifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentRemoteId(): string
    {
        return $this->contentRemoteId;
    }

    /**
     * @param string $contentRemoteId
     *
     * @return AbstractDto
     */
    public function setContentRemoteId(string $contentRemoteId): AbstractDto
    {
        $this->contentRemoteId = $contentRemoteId;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocationRemoteId(): string
    {
        return $this->locationRemoteId;
    }

    /**
     * @param string $locationRemoteId
     *
     * @return AbstractDto
     */
    public function setLocationRemoteId(string $locationRemoteId): AbstractDto
    {
        $this->locationRemoteId = $locationRemoteId;
        return $this;
    }

    public function isHidden(): bool
    {
        return $this->content->contentInfo->isHidden;
    }
}