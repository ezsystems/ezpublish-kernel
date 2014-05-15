<?php
/**
 * File containing the ScriptHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Composer;

use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as DistributionBundleScriptHandler;
use Composer\Script\CommandEvent;

class ScriptHandler extends DistributionBundleScriptHandler
{
    /**
     * Dump minified assets for prod environment under the web root directory.
     *
     * @param $event CommandEvent A instance
     */
    public static function dumpAssets( CommandEvent $event )
    {
        $options = self::getOptions( $event );
        $appDir = $options['symfony-app-dir'];
        $webDir = $options['symfony-web-dir'];

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

        static::executeCommand( $event, $appDir, 'assetic:dump --env=prod ' . escapeshellarg( $webDir ) );
    }
}
