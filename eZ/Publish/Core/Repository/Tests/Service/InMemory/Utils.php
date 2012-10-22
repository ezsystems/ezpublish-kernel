<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\InMemory\Utils class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\InMemory;

use RuntimeException;

/**
 * Utils class for InMemory tesst
 */
abstract class Utils
{
    /**
     * @static
     *
     * @param string $persistenceHandler
     * @param string $ioHandler
     * @param string $dsn
     *
     * @throws \RuntimeException
     *
     * @return \eZ\Publish\Core\Base\ServiceContainer
     */
    protected static function getServiceContainer(
        $persistenceHandler = '@persistence_handler_inmemory',
        $ioHandler = '@io_handler_inmemory',
        $dsn = 'sqlite://:memory:'
    )
    {
        // Get configuration config
        if ( !( $settings = include ( 'config.php' ) ) )
        {
            throw new RuntimeException( 'Could not find config.php, please copy config.php-DEVELOPMENT to config.php customize to your needs!' );
        }

        $settings['base']['Configuration']['UseCache'] = false;
        $settings['service']['inner_repository']['arguments']['persistence_handler'] = $persistenceHandler;
        $settings['service']['inner_repository']['arguments']['io_handler'] = $ioHandler;
        $settings['service']['parameters']['legacy_dsn'] = $dsn;

        // Return Service Container
        return require 'container.php';

    }

    /**
     * @static
     * @return \eZ\Publish\API\Repository\Repository
     */
    public static function getRepository()
    {
        return self::getServiceContainer()->get( 'inner_repository' );
    }
}
