<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\User\Role\CreateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence\User\Role;

use eZ\Publish\SPI\Persistence\MultiLanguageValueBase;

/**
 * this class is used to create a role
 *
 * @package eZ\Publish\SPI\Persistence\User\Role
 */
class CreateStruct extends MultiLanguageValueBase {

    /**
     * Policies associated with the role
     *
     * @var \eZ\Publish\SPI\Persistence\User\Role\Policy[]
     */
    public $policies = array();

    /**
     * Contains an array of group IDs that have this role assigned.
     *
     * @var mixed[] In LE implementation, id's are contentId's
     */
    public $groupIds = array();

    /**
     * Contains an array of user group IDs that have this role assigned.
     *
     * @var mixed[] In LE implementation, id's are contentId's
     */
    public $userIds = array();
}
