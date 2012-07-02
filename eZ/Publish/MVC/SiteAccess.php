<?php
/**
 * File containing the SiteAccess class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC;

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
     * Alias of the siteaccess.
     * Most likely the same than $name, but it's also possible to give an alias.
     *
     * @var string
     */
    public $alias;

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
     * @var \eZ\Publish\MVC\SiteAccess\Matcher
     */
    public $matcher;
}
