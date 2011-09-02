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
    ezp\Persistence\User as UserValue,
    ezp\User\GroupAbleInterface;

/**
 * This class represents a User item
 *
 * @property-read mixed $id
 * @property string $login
 * @property string $email
 * @property string $password
 * @property int $hashAlgorithm
 * @property \ezp\User\Group[] $group
 * @property \ezp\User\Role[] $roles
 * @property \ezp\User\Policy[] $policies
 */
class User extends Model implements GroupAbleInterface
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'login' => true,
        'email' => true,
        'password' => true,
        'hashAlgorithm' => true,
        'isEnabled' => true,
        //'maxLogin' => true,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'groups' => false,
        'roles' => false,
        'policies' => false,
    );

    /**
     * @var \ezp\Content The User Group Content Object
     */
    protected $content;

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
     * @var \ezp\User\Group[] The Groups user is assigned to
     */
    protected $groups = array();

    /**
     * Creates and setups User object
     *
     * @param mixed $id Lets you specify id of User object on creation
     */
    public function __construct( $id = null )
    {
        $this->properties = new UserValue( array( 'id' => $id ) );
        $this->content = (object)array( 'locations' => array() );
    }

    /**
     * List of assigned groups
     *
     * @return \ezp\User\Group[]
     */
    public function getGroups()
    {
        return $this->groups;
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
