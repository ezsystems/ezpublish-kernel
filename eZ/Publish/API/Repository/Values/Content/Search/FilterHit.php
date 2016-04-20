<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content\Search;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a SearchHit matching the query.
 */
class FilterHit extends ValueObject
{
    /**
     * The value found by the search.
     *
     * @var \eZ\Publish\API\Repository\Values\ValueObject
     */
    public $valueObject;

    /**
     * Language code of the Content translation that matched the query.
     *
     * @since 5.4.5
     *
     * @var string
     */
    public $matchedTranslation;
}
