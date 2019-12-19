<?php

declare(strict_types=1);

namespace eZ\Publish\Core\Repository\LocationListFactory;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId;

/**
 * @internal
 */
final class ChildrenLazySearchQuery extends AbstractLazySearchQuery
{
    protected function createQuery(Location $location): LocationQuery
    {
        $query = new LocationQuery();
        $query->filter = new ParentLocationId($location->id);

        return $query;
    }
}
