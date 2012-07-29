<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\InMemory\Utils class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\InMemory;

use eZ\Publish\Core\Base\ConfigurationManager,
    eZ\Publish\Core\Base\ServiceContainer;

/**
 * Utils class for InMemory tesst
 */
abstract class Utils
{
    /**
     * @static
     * @param string $persistenceHandler
     * @param string $ioHandler
     * @throws \RuntimeException
     * @return \eZ\Publish\Core\Base\ServiceContainer
     */
    protected static function getServiceContainer( $persistenceHandler = '@persistence_handler_inmemory', $ioHandler = '@io_handler_inmemory' )
    {
        // Get configuration config
        if ( !( $settings = include ( 'config.php' ) ) )
        {
            throw new \RuntimeException( 'Could not find config.php, please copy config.php-DEVELOPMENT to config.php customize to your needs!' );
        }

        // Load configuration uncached
        $configManager = new ConfigurationManager(
            array_merge_recursive( $settings, array(
                'base' => array(
                    'Configuration' => array(
                        'UseCache' => false
                    )
                )
            ) ),
            $settings['base']['Configuration']['Paths']
        );

        // Load service container & configuration, but force legacy handler
        $settings = $configManager->getConfiguration('service')->getAll();
        $settings['repository']['arguments']['persistence_handler'] = $persistenceHandler;
        $settings['repository']['arguments']['io_handler'] = $ioHandler;
        $settings['legacy_db_handler']['arguments']['dsn'] = ( isset( $_ENV['DATABASE'] ) && $_ENV['DATABASE'] ) ?
            $_ENV['DATABASE'] : 'sqlite://:memory:';

        // Inject legacy kernel, as it does not yet have a factory (see current mess in boostrap.php)
        $dependencies = array();
        if ( isset( $_ENV['legacyKernel'] ) )
        {
            $dependencies['@legacyKernel'] = $_ENV['legacyKernel'];
        }

        // Return Service Container
        return new ServiceContainer(
            $settings,
            $dependencies
        );

    }

    /**
     * @static
     * @return \eZ\Publish\API\Repository\Repository
     */
    public static function getRepository()
    {
        return self::getServiceContainer()->getRepository();
    }
}
