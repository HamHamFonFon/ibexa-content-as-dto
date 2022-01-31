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

    public function __invoke(Location $location)
    {
        $sortField = $location->sortField;
        $sortOrder = $location->sortOrder;

        $sortOrder = $sortOrder === Location::SORT_ORDER_DESC ? Query::SORT_DESC : Query::SORT_ASC;

        switch ($sortField) {
            case Location::SORT_FIELD_PUBLISHED:
                return new SortClause\DatePublished($sortOrder);
            case Location::SORT_FIELD_MODIFIED:
                return new SortClause\DateModified($sortOrder);
            case Location::SORT_FIELD_SECTION:
                return new SortClause\SectionIdentifier($sortOrder);
            case Location::SORT_FIELD_DEPTH:
                return new SortClause\Location\Depth($sortOrder);
            case Location::SORT_FIELD_PRIORITY:
                return new SortClause\Location\Priority($sortOrder);
            case Location::SORT_FIELD_NAME:
                return new SortClause\ContentName($sortOrder);
            case Location::SORT_FIELD_NODE_ID:
                return new SortClause\Location\Id($sortOrder);
            case Location::SORT_FIELD_CONTENTOBJECT_ID:
                return new SortClause\ContentId($sortOrder);
            case Location::SORT_FIELD_PATH:
            default:
                return new SortClause\Location\Path($sortOrder);
        }
    }
}