<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Search\Facet\RangeFacetEntry class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * This class holds statistical data for value ranges
 */
class RangeFacetEntry
{
    /**
     * The lower bound of the range
     *
     * @var mixed
     */
    public $from;

    /**
     * The upper bound of the range
     *
     * @var mixed
     */
    public $to;

    /**
     * The total count of objects in the range
     *
     * @var int
     */
    public $totalCount;

    /**
     * The minimum count in the range
     *
     * @var int
     */
    public $min;

    /**
     * The maximum count in the range
     *
     * @var int
     */
    public $max;

    /**
     * The average count in the range
     *
     * @var float
     */
    public $mean;
}
