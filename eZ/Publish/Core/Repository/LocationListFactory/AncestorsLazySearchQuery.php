<?php

declare(strict_types=1);

namespace eZ\Publish\Core\Repository\LocationListFactory;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Ancestor;

/**
 * @internal
 */
final class AncestorsLazySearchQuery extends AbstractLazySearchQuery
{
    protected function createQuery(Location $location): LocationQuery
    {
        $query = new LocationQuery();
        $query->filter = new Ancestor($location->id);

        return $query;
    }
}
