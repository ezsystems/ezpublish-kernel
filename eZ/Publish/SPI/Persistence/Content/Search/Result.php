<?php
/**
 * File containing the (content) Search result class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
