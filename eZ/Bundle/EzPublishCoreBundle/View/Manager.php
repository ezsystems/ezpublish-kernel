<?php

/**
 * File containing the Manager class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
        if ($this->logger) {
            $this->logger->debug('Changing SiteAccess in view providers');
        }

        $providers = array_merge(
            $this->getAllLocationViewProviders(),
            $this->getAllContentViewProviders(),
            $this->getAllBlockViewProviders()
        );
        foreach ($providers as $provider) {
            if ($provider instanceof SiteAccessAware) {
                $provider->setSiteAccess($siteAccess);
            }
        }
    }
}
