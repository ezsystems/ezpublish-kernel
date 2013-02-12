<?php
/**
 * File containing the RoleUpdateStruct class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\User;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 */
class RoleUpdateStruct extends ValueObject
{
    /**
     * ID of the user rule
     *
     * @var mixed
     */
    public $id;

    /**
     * Identifier of the role
     *
     * Legacy note: Maps to name in 4.x.
     *
     * @var string
     */
    public $identifier;

    /**
     * Name of the role
     *
     * @since 5.0
     * @var string[]
     */
    public $name;

    /**
     * Name of the role
     *
     * @since 5.0
     * @var string[]
     */
    public $description = array();
}
