<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Routing;

use eZ\Bundle\EzPublishCoreBundle\Routing\DefaultRouter as BaseRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class DefaultRouter extends BaseRouter
{
    protected $legacyAwareRoutes = [];

    /**
     * Injects route names that are allowed to run with legacy_mode: true.
     *
     * @param array $routes
     */
    public function setLegacyAwareRoutes( array $routes )
    {
        $this->legacyAwareRoutes = $routes;
    }

    public function matchRequest( Request $request )
    {
        $attributes = parent::matchRequest( $request );

        if (
            isset( $attributes['_route'] )
            && !$this->isLegacyAwareRoute( $attributes['_route'] )
            && $this->configResolver->getParameter( 'legacy_mode' ) === true
        )
        {
            throw new ResourceNotFoundException( "Legacy mode activated, default router is bypassed" );
        }

        return $attributes;
    }

    /**
     * Checks if $routeName can be used in legacy mode.
     *
     * @param string $routeName
     *
     * @return bool
     */
    protected function isLegacyAwareRoute( $routeName )
    {
        foreach ( $this->legacyAwareRoutes as $legacyAwareRoute )
        {
            if ( strpos( $routeName, $legacyAwareRoute ) === 0 )
            {
                return true;
            }
        }

        return false;
    }
}
