<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Search\SearchHit class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content\Search;

/**
 * This class represents a SearchHit matching the query.
 */
class SearchHit extends FilterHit
{
    /**
     * The score of this value;.
     *
     * @var float
     */
    public $score;

    /**
     * The index identifier where this value was found.
     *
     * @var string
     */
    public $index;

    /**
     * A representation of the search hit including highlighted terms.
     *
     * @var string
     */
    public $highlight;
}
