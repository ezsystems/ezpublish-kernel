<?php
/**
 * File containing the PostSiteAccessMatchEvent class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\Event;

use Symfony\Component\EventDispatcher\Event,
    Symfony\Component\HttpFoundation\Request,
    eZ\Publish\MVC\SiteAccess;

/**
 * This event is triggered after SiteAccess matching process and allows further control on it and the associated request.
 * Call setPathinfo() if you need to alter the request pathinfo after the siteaccess matching process (i.e. siteaccess in URI)
 */
class PostSiteAccessMatchEvent extends Event
{
    /**
     * @var \eZ\Publish\MVC\SiteAccess
     */
    private $siteAccess;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var string
     */
    private $fixedUpPathinfo;

    public function __construct( SiteAccess $siteAccess, Request $request )
    {
        $this->siteAccess = $siteAccess;
        $this->request = $request;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns matched SiteAccess instance
     *
     * @return \eZ\Publish\MVC\SiteAccess
     */
    public function getSiteAccess()
    {
        return $this->siteAccess;
    }

    /**
     * Sets the fixed up path info.
     * Use this method if you need to alter the request pathinfo after the siteaccess matching process (i.e. siteaccess in URI)
     *
     * @param string $fixedUpPathinfo
     */
    public function setPathinfo( $fixedUpPathinfo )
    {
        $this->fixedUpPathinfo = $fixedUpPathinfo;
    }

    /**
     * Returns the fixed up pathinfo
     *
     * @return string
     */
    public function getPathinfo()
    {
        return $this->fixedUpPathinfo;
    }

    /**
     * Returns whether a fixed up pathinfo has been set.
     *
     * @return bool
     */
    public function hasPathinfo()
    {
        return isset( $this->fixedUpPathinfo );
    }
}
