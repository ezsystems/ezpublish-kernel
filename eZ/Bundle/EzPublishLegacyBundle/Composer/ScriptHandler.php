<?php
/**
 * File containing the ScriptHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Composer;

use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as DistributionBundleScriptHandler;
use Composer\Script\CommandEvent;

class ScriptHandler extends DistributionBundleScriptHandler
{
    /**
     * Installs the legacy assets under the web root directory.
     *
     * For better interoperability, assets are copied instead of symlinked by default.
     *
     * Even if symlinks work on Windows, this is only true on Windows Vista and later,
     * but then, only when running the console with admin rights or when disabling the
     * strict user permission checks (which can be done on Windows 7 but not on Windows
     * Vista).
     *
     * @param $event CommandEvent A instance
     */
    public static function installAssets( CommandEvent $event )
    {
        $options = self::getOptions( $event );
        $appDir = $options['symfony-app-dir'];
        $webDir = $options['symfony-web-dir'];

        $symlink = '';
        if ( $options['symfony-assets-install'] === 'symlink' )
        {
            $symlink = '--symlink ';
        }
        else if ( $options['symfony-assets-install'] === 'relative' )
        {
            $symlink = '--symlink --relative ';
        }

        if ( !is_dir( $appDir ) )
        {
            echo 'The symfony-app-dir (' . $appDir . ') specified in composer.json was not found in ' . getcwd() . ', can not install assets.' . PHP_EOL;
            return;
        }

        if ( !is_dir( $webDir ) )
        {
            echo 'The symfony-web-dir (' . $webDir . ') specified in composer.json was not found in ' . getcwd() . ', can not install assets.' . PHP_EOL;
            return;
        }

        static::executeCommand( $event, $appDir, 'ezpublish:legacy:assets_install ' . $symlink . escapeshellarg( $webDir ) );
    }

    public static function installLegacyBundlesExtensions( CommandEvent $event )
    {
        $options = self::getOptions( $event );
        $appDir = $options['symfony-app-dir'];

        $symlink = '';
        if ( $options['symfony-assets-install'] === 'relative' )
        {
            $symlink = '--relative ';
        }

        if ( !is_dir( $appDir ) )
        {
            echo 'The symfony-app-dir (' . $appDir . ') specified in composer.json was not found in ' . getcwd() . ', can not install assets.' . PHP_EOL;
            return;
        }

        static::executeCommand( $event, $appDir, 'ezpublish:legacybundles:install_extensions ' . $symlink );
    }
}
