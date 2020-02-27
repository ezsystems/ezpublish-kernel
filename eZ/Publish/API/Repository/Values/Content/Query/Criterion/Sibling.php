<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * A criterion that matches content that is sibling to the given Location.
 */
class Sibling extends AggregateCriterion
{
    public function __construct(Location $location)
    {
        $criteria = new LogicalAnd([
            new ParentLocationId($location->parentLocationId),
            new LogicalNot(
                new LocationId($location->id)
            ),
        ]);

        parent::__construct($criteria);
    }
}
