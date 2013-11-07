<?php
/**
 * File containing the UpdateStruct class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Type\Group;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 */
class UpdateStruct extends ValueObject
{
    /**
     * Primary key
     *
     * @var mixed
     */
    public $id;

    /**
     * Modified date (timestamp)
     *
     * @var int
     */
    public $modificationDate;

    /**
     * Modifier user id
     *
     * @var mixed
     *
     */
    public $modifierId;
}
