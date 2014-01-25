<?php
/**
 * File contains: eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Integration\Legacy\Utils class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Integration\Legacy;

/**
 * Utils class for LegacySE test
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
