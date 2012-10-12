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
     * @param string $defaultSE Default storage engine, one of: [ inMemory, legacy ]
     * @param string $dsn
     *
     * @throws \RuntimeException
     *
     * @return \eZ\Publish\Core\Base\TestKernel
     */
    protected static function getTestKernel( $defaultSE = 'inMemory', $dsn = 'sqlite://:memory:' )
    {
        // ezpublish.api.storage_engine.legacy.dsn
        // ezsettings.default.database.dsn
        $_SERVER['SYMFONY__ezpublish__api__storage_engine__default'] = $defaultSE;
        $_SERVER['SYMFONY__ezpublish__system__ezdemo_group__database__dsn'] = $dsn;
        /**
         * @var \eZ\Publish\Core\Base\TestKernel $testKernel
         */
        $testKernel = require 'test_container.php';

        unset( $_SERVER['SYMFONY__ezpublish__api__storage_engine__default'] );
        unset( $_SERVER['SYMFONY__ezpublish__system__ezdemo_group__database__dsn'] );
        return $testKernel;
    }

    /**
     * @static
     * @return \eZ\Publish\API\Repository\Repository
     */
    public static function getRepository()
    {
        return self::getTestKernel()->getRepository();
    }
}
