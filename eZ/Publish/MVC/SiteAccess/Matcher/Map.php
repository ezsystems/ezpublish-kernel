<?php
/**
 * File containing the eZ\Publish\MVC\SiteAccess\Matcher\Map class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess\Matcher;

use eZ\Publish\MVC\SiteAccess\Matcher;

abstract class Map implements Matcher
{
    /**
     * String that will be looked up in the map.
     *
     * @var string
     */
    protected $key;

    /**
     * Map used for the matching.
     *
     * @var array
     */
    protected $map;

    /**
     * Constructor.
     *
     * @param array $map Map used for matching.
     */
    public function __construct( array $map, $key )
    {
        $this->map = $map;
        $this->key = $key;
    }

    /**
     * Returns matching Siteaccess.
     *
     * @return string|false Siteaccess matched or false.
     */
    public function match()
    {
        return isset( $this->map[$this->key] )
            ? $this->map[$this->key]
            : false;
    }
}
