<?php
/**
 * File containing the eZ\Publish\Core\Limitation\RoleLimitation class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Limitation;

use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;

/**
 * RoleLimitation is a helper class to get the actual RoleLimitations
 */
abstract class RoleLimitation implements SPILimitationTypeInterface
{
    /**
     * @static
     *
     * @param string $name Name of the role limitation, one of:
     *                     Section
     *                     Subtree
     *
     * @throws \LogicException
     * @return \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    final public static function createRoleLimitation( $name )
    {
        $className = __NAMESPACE__ . '\\' . $name;
        if ( class_exists( $className ) && $className instanceof SPILimitationTypeInterface )
            return new $className;

        throw new \LogicException( "Could not find Role limitation: {$name}" );

    }
}