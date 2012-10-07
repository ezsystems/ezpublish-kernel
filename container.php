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

if ( !isset( $_ENV['SYMFONY__ez_publish_legacy__root_dir'] ) )
{
    // set the path to eZ Publish legacy (4.x)
    $dir = getcwd();
    if ( strpos( $dir, '/vendor/ezsystems/ezpublish' ) !== false )
    {
        // eZ Publish 5 context
        $_ENV['SYMFONY__ez_publish_legacy__root_dir'] =
            str_replace( '/vendor/ezsystems/ezpublish', '', $dir ) .
            '/app/ezpublish_legacy';
    }
    else
    {
        // API context (unit testing)
        $_ENV['SYMFONY__ez_publish_legacy__root_dir'] = $dir . '/vendor/ezsystems/ezpublish-legacy';
    }
}

return new eZ\Publish\Core\Base\TestKernel();
