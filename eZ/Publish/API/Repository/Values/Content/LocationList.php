<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content;

use ArrayIterator;
use eZ\Publish\API\Repository\Values\ValueObject;
use IteratorAggregate;
use Traversable;

/**
 * This class represents a queried location list holding a totalCount and a partial list of locations
 * (by offset/limit parameters and permission filters).
 *
 * @property-read int $totalCount - the total count of found locations (filtered by permissions)
 * @property-read \eZ\Publish\API\Repository\Values\Content\Location[] $locations - the partial list of locations controlled by offset/limit
 **/
class LocationList extends ValueObject implements IteratorAggregate
{
    /**
     * the total count of found locations (filtered by permissions).
     *
     * @var int
     */
    protected $totalCount = 0;

    /**
     * the partial list of locations controlled by offset/limit.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    protected $locations = [];

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]|\Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->locations);
    }
}
