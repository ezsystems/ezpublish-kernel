<?php
/**
 * File containing the UpdateStruct class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
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
     * Name
     *
     * @since 5.0
     * @var string[]
     */
    public $name = array();

    /**
     * Description
     *
     * @since 5.0
     * @var string[]
     */
    public $description = array();

    /**
     * Readable string identifier of a group
     *
     * @var string
     */
    public $identifier;

    /**
     * Modified date (timestamp)
     *
     * @var int
     */
    public $modified;

    /**
     * Modifier user id
     *
     * @var mixed
     *
     */
    public $modifierId;
}
