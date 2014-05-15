<?php
/**
 * File containing the (content) Search result class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Search;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 */
class Result extends ValueObject
{
    /**
     * Number of results found by the search
     *
     * @var int
     */
    public $count;

    /**
     * Content objects returned by the search
     *
     * @var \eZ\Publish\SPI\Persistence\Content[]
     */
    public $content = array();
}
