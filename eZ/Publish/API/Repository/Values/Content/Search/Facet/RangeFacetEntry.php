<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * This class holds statistical data for value ranges.
 *
 * @deprecated since eZ Platform 3.2.0, to be removed in eZ Platform 4.0.0.
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
