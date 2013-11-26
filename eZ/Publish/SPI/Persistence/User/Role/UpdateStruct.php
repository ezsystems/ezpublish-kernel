<?php
/**
 * File containing the \eZ\Publish\SPI\Persistence\User\Role\UpdateStruct class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\User\Role;

use eZ\Publish\SPI\Persistence\MultiLanguageValueBase;

/**
 * this class is used to update a role
 */
class UpdateStruct extends MultiLanguageValueBase
{
    /**
     * ID of the user rule
     *
     * @var mixed
     */
    public $id;
}
