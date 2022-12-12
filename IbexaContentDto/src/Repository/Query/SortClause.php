<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Repository\Query;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;

/**
 *
 */
final class SortClause
{

    /**
     * @param Location $location
     * @return SortClause\ContentId|SortClause\ContentName|SortClause\DateModified|SortClause\DatePublished|SortClause\Location\Depth|SortClause\Location\Id|SortClause\Location\Path|SortClause\Location\Priority|SortClause\SectionIdentifier
     */
    public function __invoke(Location $location): SortClause\Location\Priority|SortClause\DatePublished|SortClause\DateModified|SortClause\ContentName|SortClause\SectionIdentifier|SortClause\Location\Id|SortClause\Location\Path|SortClause\Location\Depth|SortClause\ContentId
    {
        $sortField = $location->sortField;
        $sortOrder = $location->sortOrder;

        $sortOrder = $sortOrder === Location::SORT_ORDER_DESC ? Query::SORT_DESC : Query::SORT_ASC;

        return match ($sortField) {
            Location::SORT_FIELD_PUBLISHED => new SortClause\DatePublished($sortOrder),
            Location::SORT_FIELD_MODIFIED => new SortClause\DateModified($sortOrder),
            Location::SORT_FIELD_SECTION => new SortClause\SectionIdentifier($sortOrder),
            Location::SORT_FIELD_DEPTH => new SortClause\Location\Depth($sortOrder),
            Location::SORT_FIELD_PRIORITY => new SortClause\Location\Priority($sortOrder),
            Location::SORT_FIELD_NAME => new SortClause\ContentName($sortOrder),
            Location::SORT_FIELD_NODE_ID => new SortClause\Location\Id($sortOrder),
            Location::SORT_FIELD_CONTENTOBJECT_ID => new SortClause\ContentId($sortOrder),
            default => new SortClause\Location\Path($sortOrder),
        };
    }
}