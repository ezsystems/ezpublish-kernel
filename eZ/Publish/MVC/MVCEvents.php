<?php
/**
 * File containing the Events class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC;

final class MVCEvents
{
    /**
     * The SITEACCESS event occurs after the SiteAccess matching has occurred.
     * This event gives further control on the matched SiteAccess.
     *
     * The event listener method receives a \eZ\Publish\MVC\Event\PostSiteAccessMatchEvent
     */
    const SITEACCESS = 'ezpublish.siteaccess';
}
