<?php
/**
 * File containing the SiteAccessAware class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;

/**
 * Interface for SiteAccess aware services.
 */
interface SiteAccessAware
{
    public function setSiteAccess( SiteAccess $siteAccess = null );
}
