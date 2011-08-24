<?php
/**
 * File containing the User Handler inMemory impl
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\InMemory;
use ezp\Persistence\User\Handler as UserHandlerInterface,
    ezp\Persistence\User,
    ezp\Persistence\User\Role,
    ezp\Persistence\User\RoleUpdateStruct,
    ezp\Persistence\User\Policy,
    ezp\Base\Exception\NotFound,
    ezp\Base\Exception\BadContentType,
    ezp\Persistence\Storage\InMemory\RepositoryHandler,
    ezp\Persistence\Storage\InMemory\Backend;

/**
 * Storage Engine handler for user module
 *
 */
class UserHandler implements UserHandlerInterface
{
    /**
     * @var RepositoryHandler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to RepositoryHandler object that created it.
     *
     * @param RepositoryHandler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( RepositoryHandler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

    /**
     * Create a user
     *
     * The User struct used to create the user will contain an ID which is used
     * to reference the user.
     *
     * @param \ezp\Persistence\User $user
     * @return \ezp\Persistence\User
     */
    public function create( User $user )
    {
        $userArr = (array) $user;
        return $this->backend->create( 'User', $userArr );
    }

    /**
     * Load user with user ID.
     *
     * @param mixed $userId
     * @return \ezp\Persistence\User
     */
    public function load( $userId )
    {
        return $this->backend->load( 'User', $userId );
    }

    /**
     * Update the user information specified by the user struct
     *
     * @param \ezp\Persistence\User $user
     */
    public function update( User $user )
    {
        $userArr = (array) $user;
        $this->backend->update( 'User', $userArr['id'], $userArr );
    }

    /**
     * Delete user with the given ID.
     *
     * @param mixed $userId
     * @todo Throw on missing user?
     */
    public function delete( $userId )
    {
        $this->backend->delete( 'User', $userId );
    }

    /**
     * Create new role
     *
     * @param \ezp\Persistence\User\Role $role
     * @return \ezp\Persistence\User\Role
     */
    public function createRole( Role $role )
    {
        $roleArr = (array) $role;
        $roleArr['policies'] = array();
        $newRole = $this->backend->create( 'User\\Role', $roleArr );
        foreach ( $role->policies as $policy )
        {
            $newRole->policies[] = $this->addPolicy( $newRole->id, $policy );
        }
        return $newRole;
    }

    /**
     * Load a specified role by id
     *
     * @param mixed $roleId
     * @return \ezp\Persistence\User\Role
     * @throws \ezp\Base\Exception\NotFound If role is not found
     */
    public function loadRole( $roleId )
    {
        $list = $this->backend->find(
            'User\\Role',
            array( 'id' => $roleId ),
            array( 'policies' => array(
                'type' => 'User\\Policy',
                'match' => array( '_roleId' => 'id' ) )
            )
        );
        if ( !$list )
            throw new NotFound( 'User\\Role', $roleId );

        return $list[0];
    }

    /**
     * Update role
     *
     * @param \ezp\Persistence\User\RoleUpdateStruct $role
     */
    public function updateRole( RoleUpdateStruct $role )
    {
        $roleArr = (array) $role;
        $this->backend->update( 'User\\Role', $roleArr['id'], $roleArr );
    }

    /**
     * Delete the specified role
     *
     * @param mixed $roleId
     */
    public function deleteRole( $roleId )
    {
        $this->backend->delete( 'User\\Role', $roleId );
        $this->backend->deleteByMatch( 'User\\Policy', array( '_roleId' => $roleId ) );
        // @todo Deal with role assignments
    }

    /**
     * Adds a policy to a role
     *
     * @param mixed $roleId
     * @param \ezp\Persistence\User\Policy $policy
     * @return \ezp\Persistence\User\Policy
     * @todo Validate Role Id?
     */
    public function addPolicy( $roleId, Policy $policy )
    {
        $policyArr = array( '_roleId' => $roleId ) + ( (array) $policy );
        return $this->backend->create( 'User\\Policy', $policyArr );
    }

    /**
     * Removes a policy from a role
     *
     * @param mixed $roleId
     * @param mixed $policyId
     * @return void
     * @todo Throw exception on missing policy?
     */
    public function removePolicy( $roleId, $policyId )
    {
        $this->backend->deleteByMatch( 'User\\Policy', array( 'id' => $policyId, '_roleId' => $roleId ) );
    }

    /**
     * Returns the user policies associated with the user
     *
     * @param mixed $userId
     * @return \ezp\Persistence\User\Policy[]
     */
    public function getPermissions( $userId ){}

    /**
     * Assign role to user with given limitation
     *
     * The limitation array may look like:
     * <code>
     *  array(
     *      'Subtree' => array(
     *          '/1/2/',
     *          '/1/4/',
     *      ),
     *      'Foo' => array( 'Bar' ),
     *      …
     *  )
     * </code>
     *
     * Where the keys are the limitation identifiers, and the respective values
     * are an array of limitation values. The limitation parameter is optional.
     *
     * @param mixed $groupId
     * @param mixed $roleId
     * @param array $limitation @todo Remove or implement
     * @throws \ezp\Base\Exception\NotFound If role or group is not found
     */
    public function assignRole( $groupId, $roleId, array $limitation = null )
    {
        $content = (array) $this->backend->load( 'Content', $groupId );
        if ( !$content )
            throw new NotFound( 'User Group', $groupId );

        // @todo Use eZ Publish settings for this, and maybe a better exception
        if ( $content['typeId'] != 3 )
             throw new BadContentType( 3, $content['typeId'] );

        $role = $this->loadRole( $roleId );
        $content['_roleIds'][] = $role->id;
        $this->backend->update( 'Content', $groupId, $content );
    }

    /**
     * Un-assign a role
     *
     * @param mixed $groupId
     * @param mixed $roleId
     */
    public function unAssignRole( $groupId, $roleId ){}
}
?>
