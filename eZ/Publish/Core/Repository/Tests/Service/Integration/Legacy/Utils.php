<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\Utils class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy;

/**
 * Utils class for LegacySE test.
 */
abstract class Utils
{
    /**
     * @var \eZ\Publish\API\Repository\Tests\SetupFactory
     */
    public static $setupFactory;

    /**
     * @return \eZ\Publish\API\Repository\Repository
     */
    final public static function getRepository()
    {
        if (static::$setupFactory === null) {
            static::$setupFactory = static::getSetupFactory();
        }

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
