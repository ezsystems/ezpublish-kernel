<?php

/**
 * File containing the SiteAccessAware class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;

/**
 * Interface for SiteAccess aware services.
 */
interface SiteAccessAware
{
    public function setSiteAccess(SiteAccess $siteAccess = null);
}
