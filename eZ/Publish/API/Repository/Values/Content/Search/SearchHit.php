<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Search\SearchHit class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Search;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a SearchHit matching the query
 *
 */
class SearchHit extends ValueObject
{
    /**
     * The value found by the search
     *
     * @var \eZ\Publish\API\Repository\Values\ValueObject
     */
    public $valueObject;

    /**
     * The score of this value;
     *
     * @var float
     */
    public $score;

    /**
     * The index identifier where this value was found
     *
     * @var string
     */
    public $index;

    /**
     * A representation of the search hit including highlighted terms
     *
     * @var string
     */
    public $highlight;
}
