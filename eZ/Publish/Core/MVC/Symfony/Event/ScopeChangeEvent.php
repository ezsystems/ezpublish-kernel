<?php
/**
 * File containing the ScopeChangeEvent class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Event;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is sent when configuration scope is changed (e.g. for content preview in a given siteaccess).
 */
class ScopeChangeEvent extends Event
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    private $siteAccess;

    public function __construct( SiteAccess $siteAccess )
    {
        $this->siteAccess = $siteAccess;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    public function getSiteAccess()
    {
        return $this->siteAccess;
    }
}
