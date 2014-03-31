<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\HostElement class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;

use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher;

class HostElement implements VersatileMatcher
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest
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
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request
     *
     * @return void
     */
    public function setRequest( SimplifiedRequest $request )
    {
        $this->request = $request;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns matcher object corresponding to $siteAccessName or null if non applicable.
     *
     * @note Limitation: Will only work correctly if HostElement is used for all siteaccesses, as host cannot be guessed.
     *
     * @param string $siteAccessName
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\HostElement|null Typically a clone of current matcher, with appropriate config.
     */
    public function reverseMatch( $siteAccessName )
    {
        $request = clone $this->request;
        $hostElements = explode( '.', $request->host );
        $elementNumber = $this->elementNumber - 1;
        if ( !isset( $hostElements[$elementNumber] ) )
        {
            return null;
        }

        $hostElements[$elementNumber] = $siteAccessName;
        $matcher = clone $this;
        $matcher->getRequest()->setHost( implode( '.', $hostElements ) );

        return $matcher;
    }

    public function __clone()
    {
        $this->request = clone $this->request;
    }
}
