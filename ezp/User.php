<?php
/**
 * File containing User interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp;
use ezp\Base\ModelDefinition;

/**
 * This interface represents a User item
 *
 * @property-read mixed $id
 * @property string $login
 * @property string $email
 * @property string $password
 * @property int $hashAlgorithm
 * @property \ezp\User\Group[] $groups
 * @property \ezp\User\Role[] $roles
 * @property \ezp\User\Policy[] $policies
 */
interface User extends ModelDefinition
{
    /**
     * @var int MD5 of password, not recommended
     */
    const PASSWORD_HASH_MD5_PASSWORD = 1;

    /**
     * @var int MD5 of user and password
     */
    const PASSWORD_HASH_MD5_USER = 2;

    /**
     * @var int MD5 of site, user and password
     */
    const PASSWORD_HASH_MD5_SITE = 3;

    /**
     * @var int Passwords in plaintext, should not be used for real sites
     */
    const PASSWORD_HASH_PLAIN_TEXT = 5;

    /**
     * List of assigned groups
     *
     * @return \ezp\User\Group[]
     */
    public function getGroups();

    /**
     * List of assigned Roles
     *
     * @return array|User\Role[]
     */
    public function getRoles();

    /**
     * List of assigned and inherited policies (via assigned and inherited roles)
     *
     * @return array|User\Policy[]
     */
    public function getPolicies();

    /**
     * Checks if user has access to a specific module/function
     *
     * Return array of limitations if user has access to a certain function
     * but limited by the returned limitations.
     * If you have the model instance you want to check permissions against, then
     * use {@link \ezp\Base\Repository::canUser()}.
     *
     * @param string $module
     * @param string $function
     * @return array|bool
     */
    public function hasAccessTo( $module, $function );
}
