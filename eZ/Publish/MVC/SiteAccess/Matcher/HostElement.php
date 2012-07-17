<?php
/**
 * File containing the eZ\Publish\MVC\SiteAccess\Matcher\HostElement class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess\Matcher;

use eZ\Publish\MVC\SiteAccess\Matcher,
    eZ\Publish\MVC\Routing\SimplifiedRequest;

class HostElement implements Matcher
{
    /**
     * @var \eZ\Publish\MVC\Routing\SimplifiedRequest
     */
    private $request;

    /**
     * Number of elements to take into account.
     *
     * @var int
     */
    private $elementNumber;

    /**
     * Constructor.
     *
     * @param int $elementNumber Number of elements to take into account.
     */
    public function __construct( $elementNumber )
    {
        $this->elementNumber = (int)$elementNumber;
    }

    /**
     * Returns matching Siteaccess.
     *
     * @return string|false Siteaccess matched or false.
     */
    public function match()
    {
        $elements = explode( ".", $this->request->host );

        return isset( $elements[$this->elementNumber - 1] ) ? $elements[$this->elementNumber - 1] : false;
    }

    public function getName()
    {
        return 'host:element';
    }

    /**
     * Injects the request object to match against.
     *
     * @param \eZ\Publish\MVC\Routing\SimplifiedRequest $request
     * @return void
     */
    public function setRequest( SimplifiedRequest $request )
    {
        $this->request = $request;
    }
}
