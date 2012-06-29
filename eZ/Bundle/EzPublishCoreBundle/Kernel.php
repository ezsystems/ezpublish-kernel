<?php
/**
 * File containing the eZ Publish Kernel class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle;

use Symfony\Component\HttpKernel\Kernel as BaseKernel,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class Kernel extends BaseKernel
{
    /**
     * Full URL (including scheme and host) used for siteaccess matching
     *
     * @var string
     */
    private $fullUrl;

    public function handle( Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true )
    {
        if ( $this->booted === false )
        {
            $this->boot();
        }

        $siteAccessRouter = $this->container->get( 'ezpublish.siteaccess_router' );
        $request->attributes->add(
            array(
                 'siteaccess' => $siteAccessRouter->match( $this->getFullUrl( $request ) )
            )
        );

        return parent::handle( $request, $type, $catch );
    }

    /**
     * Returns the full URL to be used for siteaccess matching
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    private function getFullUrl( Request $request )
    {
        if ( !isset( $this->fullUrl ) )
        {
            $this->fullUrl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getPathInfo();
        }

        return $this->fullUrl;
    }
}
