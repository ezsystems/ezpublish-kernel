<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Search\SearchHit class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Search;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a SearchHit matching the query.
 */
class SearchHit extends ValueObject
{
    /**
     * The value found by the search.
     *
     * @var \eZ\Publish\API\Repository\Values\ValueObject
     */
    public $valueObject;

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
     * Language code of the Content translation that matched the query.
     *
     * @since 5.4.5
     *
     * @var string
     */
    public $matchedTranslation;

    /**
     * A representation of the search hit including highlighted terms.
     *
     * @var string
     */
    public $highlight;
}
