<?php
/**
 * File generates service container instance
 *
 * Expects global $settings to be set by caller
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

if ( empty( $_SERVER['SYMFONY__ezpublish_legacy__root_dir'] ) )
{
    // set the path to eZ Publish legacy (4.x)
    $dir = getcwd();
    if ( strpos( $dir, '/vendor/ezsystems/ezpublish' ) !== false )
    {
        // eZ Publish 5 context
        $_SERVER['SYMFONY__ezpublish_legacy__root_dir'] =
            str_replace( '/vendor/ezsystems/ezpublish', '', $dir ) . '/app/ezpublish_legacy';
    }
    else
    {
        // API context (unit testing)
        $_SERVER['SYMFONY__ezpublish_legacy__root_dir'] = $dir . '/vendor/ezsystems/ezpublish-legacy';
    }
}

$testKernel = new eZ\Publish\Core\Base\TestKernel();
$container = $testKernel->getContainer();
$siteAccessName = $container->hasParameter( 'ezpublish.siteaccess.default' ) ?
    $container->getParameter( 'ezpublish.siteaccess.default' ) :
    'default';
$siteAccess = new eZ\Bundle\EzPublishCoreBundle\SiteAccess( $siteAccessName, 'cli' );
$container->set( 'ezpublish.siteaccess', $siteAccess );

return $testKernel;