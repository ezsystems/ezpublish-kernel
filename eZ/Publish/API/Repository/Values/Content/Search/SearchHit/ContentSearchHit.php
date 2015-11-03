<?php

/**
 * File containing a SearchHit class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Search\SearchHit;

use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;

/**
 * This class represents a SearchHit matching the query.
 */
class ContentSearchHit extends SearchHit
{
    /**
     * The value found by the search.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    public $valueObject;
}
