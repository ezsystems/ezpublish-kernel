<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\User\Role\UserAssignment class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\User\Role;

/**
 * the user role assignment class
 * @package eZ\Publish\SPI\Persistence\User
 */
class UserAssignment extends Assignment
{
    /**
     * The user id the role is assigned to
     *
     * @var mixed
     */
    public $userId;
}
