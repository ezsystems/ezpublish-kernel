<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;

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
    public function __construct( array $map )
    {
        $this->map = $map;
    }

    /**
     * Injects the key that will be used for matching against the map configuration
     *
     * @param string $key
     */
    public function setMapKey( $key )
    {
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
