<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Repository\Query;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;

final class QueryGetSubItems
{
    /**
     * @param Location $parentLocation
     * @param array $contentTypeIdentifiers
     * @param array|null $sortClauses
     * @param array|null $sectionIds
     * @param int $offset
     * @param int $limit
     * @param array|null $excludedContentTypeIdentifiers
     * @param array $languages
     * @param bool|null $visibility
     *
     * @return \Generator
     */
    public function __invoke(
        SearchService $searchService,
        Location $parentLocation,
        array $contentTypeIdentifiers,
        ?array $sortClauses,
        ?array $sectionIds,
        int $offset,
        int $limit,
        ?array $excludedContentTypeIdentifiers,
        array $languages,
        ?bool $visibility
    ): \Generator
    {
        $ibexaSortClause = new SortClause;

        $criterions = [];
        $criterions[] = new ParentLocationId($parentLocation->id);

        if ($visibility) {
            $criterions[] = new Visibility(Visibility::VISIBLE);
        }

        if (!empty($contentTypeIdentifiers)) {
            $criterions[] = new ContentTypeIdentifier($contentTypeIdentifiers);
        }

        if (!empty($excludedContentTypeIdentifiers)) {
            $criterions[] = new LogicalNot(new ContentTypeIdentifier($excludedContentTypeIdentifiers));
        }

        if ($sectionIds !== null) {
            $criterions[] = new Query\Criterion\SectionId($sectionIds);
        }

        if (!empty($languages)) {
            $criterions[] = new LanguageCode($languages);
        }

        $query = new LocationQuery();
        $query->filter = new LogicalAnd($criterions);
        $query->limit = $limit;
        $query->offset = $offset;
        $query->sortClauses = $sortClauses ?? $ibexaSortClause($parentLocation);

        $searchResult = $searchService->findLocations($query);

        foreach ($searchResult->searchHits as $searchHit) {
            $result = $searchHit->valueObject;
            yield $result->getContent();
        }
    }

}
