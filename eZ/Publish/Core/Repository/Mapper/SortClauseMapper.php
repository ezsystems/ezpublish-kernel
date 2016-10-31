<?php

/**
 * File containing SortClauseMapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\Mapper;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

class SortClauseMapper
{
    /**
     * Get SortClause objects built from $location's sort options.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    public function getSortClauseFromLocation(Location $location)
    {
        $sortClause = $this->buildSortClauseFromSortField($location->sortField);
        $sortClause->direction = $this->mapLocationSortOrderToQuerySortOrder($location->sortOrder);

        return [$sortClause];
    }

    /**
     * Generates sorting field.
     *
     * @param mixed $sortField
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException if sort order is not implemented
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException if sort field is unknown
     */
    private function buildSortClauseFromSortField($sortField)
    {
        $map = [
            Location::SORT_FIELD_PATH => SortClause\Location\Path::class,
            Location::SORT_FIELD_PUBLISHED => SortClause\DatePublished::class,
            Location::SORT_FIELD_MODIFIED => SortClause\DateModified::class,
            Location::SORT_FIELD_SECTION => SortClause\SectionIdentifier::class,
            Location::SORT_FIELD_DEPTH => SortClause\Location\Depth::class,
            Location::SORT_FIELD_CLASS_IDENTIFIER => false,
            Location::SORT_FIELD_CLASS_NAME => false,
            Location::SORT_FIELD_PRIORITY => SortClause\Location\Priority::class,
            Location::SORT_FIELD_NAME => SortClause\ContentName::class,
            Location::SORT_FIELD_MODIFIED_SUBNODE => false,
            Location::SORT_FIELD_NODE_ID => SortClause\Location\Id::class,
            Location::SORT_FIELD_CONTENTOBJECT_ID => SortClause\ContentId::class,
        ];

        if ($map[$sortField] === false) {
            throw new NotImplementedException('Sort method not implemented.');
        }

        if (!isset($map[$sortField])) {
            throw new InvalidArgumentException('sortField', 'Unknown sort field');
        }

        return new $map[$sortField]();
    }

    /**
     * Generates sorting order.
     *
     * @param int $sortOrder
     *
     * @return mixed
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException if sort order is unknown
     */
    private function mapLocationSortOrderToQuerySortOrder($sortOrder)
    {
        $map = [
            Location::SORT_ORDER_DESC => Query::SORT_DESC,
            Location::SORT_ORDER_ASC => Query::SORT_ASC,
        ];

        if (!isset($map[$sortOrder])) {
            throw new InvalidArgumentException('sortOrder', 'Unknown sort order');
        }

        return $map[$sortOrder];
    }
}
