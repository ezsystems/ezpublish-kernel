<?php
/**
 * File containing User object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp;
use ezp\Base\Model,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Persistence\User as UserValue;

/**
 * This class represents a User item
 *
 * @property-read mixed $id
 * @property string $login
 * @property string $email
 * @property string $password
 * @property int $hashAlgorithm
 * @property \ezp\User\Role[] $roles
 * @property \ezp\User\Policy[] $policies
 */
class User extends Model
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'login' => true,
        'email' => true,
        'password' => true,
        'hashAlgorithm' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        //'groups' => false,
        'roles' => false,
        'policies' => false,
    );

    /**
     * Assigned Roles
     *
     * @var \ezp\User\Role[]
     */
    protected $roles = array();

    /**
     * Assigned and inherited policies (via assigned and inherited roles)
     *
     * @var \ezp\User\Policy[]
     */
    protected $policies = array();

    /**
     * Creates and setups User object
     */
    public function __construct()
    {
        $this->properties = new UserValue();
    }

    /**
     * List of assigned Roles
     *
     * @return array|User\Role[]
     */
    protected function getRoles()
    {
        return $this->roles;
    }

    /**
     * List of assigned and inherited policies (via assigned and inherited roles)
     *
     * @return array|User\Policy[]
     */
    protected function getPolicies()
    {
        return $this->policies;
    }
}
