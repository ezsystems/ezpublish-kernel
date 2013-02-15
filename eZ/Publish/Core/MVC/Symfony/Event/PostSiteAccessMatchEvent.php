<?php
/**
 * File containing the PostSiteAccessMatchEvent class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;

/**
 * This event is triggered after SiteAccess matching process and allows further control on it and the associated request.
 */
class PostSiteAccessMatchEvent extends Event
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    private $siteAccess;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

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
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    public function getSiteAccess()
    {
        return $this->siteAccess;
    }
}
