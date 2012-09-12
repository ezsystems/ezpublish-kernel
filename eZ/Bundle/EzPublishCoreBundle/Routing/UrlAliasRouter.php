<?php
/**
 * File containing the UrlAliasRouter class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Routing;

use eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter as BaseUrlAliasRouter,
    eZ\Bundle\EzPublishCoreBundle\SiteAccess,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Routing\Exception\ResourceNotFoundException;

class UrlAliasRouter extends BaseUrlAliasRouter
{
    public function matchRequest( Request $request )
    {
        $siteAccess = $request->attributes->get( 'siteaccess' );
        if ( $siteAccess instanceof SiteAccess )
        {
            // UrlAliasRouter might be disabled from configuration.
            // An example is for running the admin interface: it needs to be entirely run through the legacy kernel.
            if ( $siteAccess->attributes->get( 'useUrlAliasRouter' ) === false )
                throw new ResourceNotFoundException( "Config says to bypass UrlAliasRouter" );
        }

        return parent::matchRequest( $request );
    }
}
