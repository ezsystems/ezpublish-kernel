<?php
/**
 * File containing the RoleUpdateStruct class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
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
     * Name of the role
     *
     * @var string
     */
    public $name;
}
?>
