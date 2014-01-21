<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\InMemory\Utils class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration\InMemory;

/**
 * Utils class for InMemory test
 */
abstract class Utils
{
    /**
     * @var \eZ\Publish\API\Repository\Tests\SetupFactory
     */
    static $setupFactory;

    /**
     * @return \eZ\Publish\API\Repository\Repository
     */
    final public static function getRepository()
    {
        if ( static::$setupFactory === null )
            static::$setupFactory = static::getSetupFactory();

        // Return repository
        return static::$setupFactory->getRepository();
    }

    /**
     * @return \eZ\Publish\API\Repository\Tests\SetupFactory
     */
    protected static function getSetupFactory()
    {
        return new SetupFactory();
    }
}
