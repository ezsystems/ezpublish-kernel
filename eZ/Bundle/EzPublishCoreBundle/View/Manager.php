<?php
/**
 * File containing the Manager class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\View;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\View\Manager as BaseManager;

class Manager extends BaseManager implements SiteAccessAware
{
    /**
     * Changes SiteAccess.
     * Passed SiteAccess will be injected in all location/content/block view providers
     * to allow them to change their internal configuration based on this new SiteAccess.
     *
     * @param SiteAccess $siteAccess
     */
    public function setSiteAccess( SiteAccess $siteAccess = null )
    {
        if ( $this->logger )
        {
            $this->logger->debug( 'Changing SiteAccess in view providers' );
        }

        foreach (
            array_merge(
                $this->getAllLocationViewProviders(),
                $this->getAllContentViewProviders(),
                $this->getAllBlockViewProviders()
            )
            as $provider
        )
        {
            if ( $provider instanceof SiteAccessAware )
            {
                $provider->setSiteAccess( $siteAccess );
            }
        }
    }
}
