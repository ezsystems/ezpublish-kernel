<?php
/**
 * File containing the SiteAccess class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony;

/**
 * Base struct for a siteaccess representation
 */
class SiteAccess
{
    /**
     * Name of the siteaccess
     *
     * @var string
     */
    public $name;

    /**
     * The matching type that has been used to discover the siteaccess.
     * Contains the matcher class FQN, or 'default' if fell back to the default siteaccess.
     *
     * @var string
     */
    public $matchingType;

    /**
     * The matcher instance that has been used to discover the siteaccess.
     *
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher
     */
    public $matcher;

    public function __construct( $name = null, $matchingType = null, $matcher = null )
    {
        $this->name = $name;
        $this->matchingType = $matchingType;
        $this->matcher = $matcher;
    }

    public function __toString()
    {
        return "$this->name (matched by '$this->matchingType')";
    }
}
