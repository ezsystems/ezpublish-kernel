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
class Sibling extends CompositeCriterion
{
    public function __construct(int $locationId, int $parentLocationId)
    {
        $criteria = new LogicalAnd([
            new ParentLocationId($parentLocationId),
            new LogicalNot(
                new LocationId($locationId)
            ),
        ]);

        parent::__construct($criteria);
    }

    public static function fromLocation(Location $location): self
    {
        return new self($location->id, $location->parentLocationId);
    }
}
