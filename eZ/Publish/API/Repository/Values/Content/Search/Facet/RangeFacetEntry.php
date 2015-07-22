<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Search\Facet\RangeFacetEntry class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * This class holds statistical data for value ranges.
 */
class RangeFacetEntry
{
    /**
     * The lower bound of the range.
     *
     * @var mixed
     */
    public $from;

    /**
     * The upper bound of the range.
     *
     * @var mixed
     */
    public $to;

    /**
     * The total count of objects in the range.
     *
     * @var int
     */
    public $totalCount;

    /**
     * The minimum count in the range.
     *
     * @var int
     */
    public $min;

    /**
     * The maximum count in the range.
     *
     * @var int
     */
    public $max;

    /**
     * The average count in the range.
     *
     * @var float
     */
    public $mean;
}
