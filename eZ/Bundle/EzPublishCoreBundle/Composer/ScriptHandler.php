<?php

/**
 * File containing the ScriptHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Composer;

use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as DistributionBundleScriptHandler;
use Composer\Script\Event;

class ScriptHandler extends DistributionBundleScriptHandler
{
    /**
     * Clears the Symfony cache.
     *
     * Overloaded to clear project containers first before booting up Symfony container as part of clearCache() =>
     * cache:clear call. Since this will crash with RuntimeException if bundles have been removed or added when for
     * instance moving between git branches and running `composer install/update` afterwards.
     *
     * @param Event $event
     */
    public static function clearCache(Event $event)
    {
        $options = static::getOptions($event);
        $cacheDir = $options['symfony-app-dir'] . '/cache';

        // Take Symfony 3.0 directory structure into account if configured.
        if (isset($options['symfony-var-dir']) && is_dir($options['symfony-var-dir'])) {
            $cacheDir = $options['symfony-var-dir'] . '/cache';
        }

        array_map('unlink', glob($cacheDir . '/*/*ProjectContainer.php'));
        parent::clearCache($event);
    }

    /**
     * Dump minified assets for prod environment under the web root directory.
     *
     * @param $event Event A instance
     */
    public static function dumpAssets(Event $event)
    {
        $options = self::getOptions($event);
        $consoleDir = static::getConsoleDir($event, 'install assets');

        if (null === $consoleDir) {
            return;
        }

        $webDir = $options['symfony-web-dir'];
        $command = 'assetic:dump';

        // if not set falls back to default behaviour of console commands (using SYMFONY_ENV or fallback to 'dev')
        if (!empty($options['ezpublish-asset-dump-env'])) {
            $event->getIO()->write('<error>Use of `ezpublish-asset-dump-env` is deprecated, use SYMFONY_ENV to set anything other then dev for all commands</error>');

            if ($options['ezpublish-asset-dump-env'] === 'none') {
                // If asset dumping is skipped, output help text on how to generate it if needed
                return self::dumpAssetsHelpText($event);
            }

            $command .= ' --env=' . escapeshellarg($options['ezpublish-asset-dump-env']);
        }

        if (!is_dir($consoleDir)) {
            echo 'The symfony console directory (' . $consoleDir . ') specified in composer.json was not found in ' . getcwd() . ', can not install assets.' . PHP_EOL;

            return;
        }

        if (!is_dir($webDir)) {
            echo 'The symfony-web-dir (' . $webDir . ') specified in composer.json was not found in ' . getcwd() . ', can not install assets.' . PHP_EOL;

            return;
        }

        static::executeCommand($event, $consoleDir, $command . ' ' . escapeshellarg($webDir));
    }

    /**
     * Just dump help text on how to dump assets.
     *
     * Typically to use this instead on composer update as dump command uses prod environment where cache is not cleared,
     * causing it to sometimes crash when cache needs to be cleared.
     *
     * @deprecated In 7.0 will either be made private for use by dumpAssets, or removed.
     * @param $event Event A instance
     */
    public static function dumpAssetsHelpText(Event $event)
    {
        $consoleDir = static::getConsoleDir($event, 'get console dir for asset dump text');
        $event->getIO()->write('<info>To dump eZ Publish production assets, which is needed for production environment, execute the following:</info>');
        $event->getIO()->write("    php ${consoleDir}/console assetic:dump --env=prod web");
        $event->getIO()->write('');
    }

    /**
     * Just dump welcome text on how to install eZ Platform.
     *
     * @param $event Event A instance
     */
    public static function installWelcomeText(Event $event)
    {
        $consoleDir = static::getConsoleDir($event, 'get console dir for welcome text');
        $event->getIO()->write(<<<EOT

      ________      ____    ___             __       ___         
     /\_____  \    /\  _`\ /\_ \           /\ \__  /'___\ 
   __\/____//'/'   \ \ \_\ \//\ \      __  \ \ ,_\/\ \__/  ___   _ __    ___ ___
 /'__`\   //'/'     \ \ ,__/ \ \ \   /'__`\ \ \ \/\ \ ,__\/ __`\/\`'__\/' __` __`\  
/\  __/  //'/'___    \ \ \/   \_\ \_/\ \L\.\_\ \ \_\ \ \_/\ \L\ \ \ \/ /\ \/\ \/\ \ 
\ \____\ /\_______\   \ \_\   /\____\ \__/.\_\\ \__\\ \_\\ \____/\ \_\ \ \_\ \_\ \_\
 \/____/ \/_______/    \/_/   \/____/\/__/\/_/ \/__/ \/_/ \/___/  \/_/  \/_/\/_/\/_/


<fg=cyan>Welcome to eZ Platform!</fg=cyan>

<options=bold>Quick test install:</>
<comment>    $  export SYMFONY_ENV="dev"</comment>
<comment>    $  composer ezplatform-install</comment>
<comment>    $  php ${consoleDir}/console server:run</comment>

Note:
- "composer ezplatform-install" is an alias for command <comment>php ${consoleDir}/console ezplatform:install <type></comment> 
- Instructions above assume the CLI user you execute these commands with is the same one that extracted/installed the software.
- The last command will give you the url to the frontend of the installation, add "/admin" to reach backend.


For full install instructions, including setting up full web server & directory permissions, see:
https://doc.ezplatform.com/en/latest/getting_started/install_using_composer/

EOT
        );
    }
}
