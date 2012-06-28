<?php
/**
 * File containing the eZ\Publish\MVC\SiteAccess\Matcher\HostElement class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess\Matcher;

use eZ\Publish\MVC\SiteAccess\Matcher;

class HostElement implements Matcher
{
    /**
     * Host of the URI as returned by parse_url().
     *
     * @var string
     */
    private $host;

    /**
     * Number of elements to take into account.
     *
     * @var int
     */
    private $elementNumber;

    /**
     * Constructor.
     *
     * @param array $URIElements Elements of the URI as parsed by parse_url().
     * @param int $elementNumber Number of elements to take into account.
     */
    public function __construct( array $URIElements, $elementNumber )
    {
        $this->host = $URIElements["host"];
        $this->elementNumber = (int)$elementNumber;
    }

    /**
     * Returns matching Siteaccess.
     *
     * @return string|false Siteaccess matched or false.
     */
    public function match()
    {
        $elements = explode( ".", $this->host );

        return isset( $elements[$this->elementNumber - 1] ) ? $elements[$this->elementNumber - 1] : false;
    }
}
