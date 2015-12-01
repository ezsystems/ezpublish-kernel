<?php

/**
 * File containing the ScriptHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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
    public static function dumpAssets(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];
        $webDir = $options['symfony-web-dir'];
        $env = isset($options['ezpublish-asset-dump-env']) ? $options['ezpublish-asset-dump-env'] : '';

        if (!$env) {
            $env = $event->getIO()->ask(
                "<question>Which environment would you like to dump production assets for?</question> (Default: 'prod', type 'none' to skip) ",
                'prod'
            );
        }

        if ($env === 'none') {
            return;
        }

        if (!is_dir($appDir)) {
            echo 'The symfony-app-dir (' . $appDir . ') specified in composer.json was not found in ' . getcwd() . ', can not install assets.' . PHP_EOL;

            return;
        }

        if (!is_dir($webDir)) {
            echo 'The symfony-web-dir (' . $webDir . ') specified in composer.json was not found in ' . getcwd() . ', can not install assets.' . PHP_EOL;

            return;
        }

        static::executeCommand($event, $appDir, 'assetic:dump --env=' . escapeshellarg($env) . ' ' . escapeshellarg($webDir));
    }

    /**
     * Just dump help text on how to dump assets.
     *
     * Typically to use this instead on composer update as dump command uses prod environment where cache is not cleared,
     * causing it to sometimes crash when cache needs to be cleared.
     *
     * @param $event CommandEvent A instance
     */
    public static function dumpAssetsHelpText(CommandEvent $event)
    {
        $event->getIO()->write('<info>To dump eZ Publish production assets, execute the following:</info>');
        $event->getIO()->write('    php app/console assetic:dump --env=prod web');
        $event->getIO()->write('');
    }

    /**
     * Just dump welcome text on how to install eZ Platform.
     *
     * @param $event CommandEvent A instance
     */
    public static function installWelcomeText(CommandEvent $event)
    {
        $event->getIO()->write(<<<'EOT'

________________/\\\\\\\\\\\\\\\____________/\\\\\\\\\\\\\____/\\\\\\________________________________________/\\\\\_________________________________________________
 ________________\////////////\\\____________\/\\\/////////\\\_\////\\\______________________________________/\\\///__________________________________________________
  __________________________/\\\/_____________\/\\\_______\/\\\____\/\\\_______________________/\\\__________/\\\______________________________________________________
   _____/\\\\\\\\__________/\\\/_______________\/\\\\\\\\\\\\\/_____\/\\\_____/\\\\\\\\\_____/\\\\\\\\\\\__/\\\\\\\\\_______/\\\\\_____/\\/\\\\\\\_____/\\\\\__/\\\\\___
    ___/\\\/////\\\_______/\\\/_________________\/\\\/////////_______\/\\\____\////////\\\___\////\\\////__\////\\\//______/\\\///\\\__\/\\\/////\\\__/\\\///\\\\\///\\\_
     __/\\\\\\\\\\\______/\\\/___________________\/\\\________________\/\\\______/\\\\\\\\\\_____\/\\\_________\/\\\_______/\\\__\//\\\_\/\\\___\///__\/\\\_\//\\\__\/\\\_
      _\//\\///////_____/\\\/_____________________\/\\\________________\/\\\_____/\\\/////\\\_____\/\\\_/\\_____\/\\\______\//\\\__/\\\__\/\\\_________\/\\\__\/\\\__\/\\\_
       __\//\\\\\\\\\\__/\\\\\\\\\\\\\\\___________\/\\\______________/\\\\\\\\\_\//\\\\\\\\/\\____\//\\\\\______\/\\\_______\///\\\\\/___\/\\\_________\/\\\__\/\\\__\/\\\_
        ___\//////////__\///////////////____________\///______________\/////////___\////////\//______\/////_______\///__________\/////_____\///__________\///___\///___\///__


<fg=cyan>Welcome to eZ Platform!</fg=cyan>

<options=bold>Please read the INSTALL.md file to complete the installation.</options>

<options=bold>Assuming that your database information were correctly entered, you may install a clean database by running the install command:</options>
<comment>    $ php app/console --env=prod ezplatform:install clean</comment>

EOT
        );
    }
}
