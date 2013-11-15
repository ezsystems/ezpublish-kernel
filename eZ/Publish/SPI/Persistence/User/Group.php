<?php
/**
 * File containing the Group class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\User;

use eZ\Publish\SPI\Persistence\ValueObject;

class Group extends ValueObject
{
    /**
     * Group ID
     *
     * @var mixed
     */
    public $id;

    /**
     * ID of the parent group
     *
     * @var mixed
     */
    public $parentId;
}
