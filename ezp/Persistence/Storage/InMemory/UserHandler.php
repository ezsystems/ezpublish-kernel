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
    ezp\Persistence\Content,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Exception\Logic,
    ezp\Base\Exception\NotFound,
    ezp\Base\Exception\NotFoundWithType,
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
     * @throws \ezp\Base\Exception\Logic If no id was provided or if it already exists
     */
    public function create( User $user )
    {
        $userArr = (array) $user;
        return $this->backend->create( 'User', $userArr, false );
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
                'match' => array( 'roleId' => 'id' ) )
            )
        );
        if ( !$list )
            throw new NotFound( 'User\\Role', $roleId );

        return $list[0];
    }

    /**
     * Load roles assigned to a user/group
     *
     * @param mixed $groupId
     * @return \ezp\Persistence\User\Role[]
     * @throws \ezp\Base\Exception\NotFound If user (it's content object atm) is not found
     * @throws \ezp\Base\Exception\NotFoundWithType If group is not of user_group Content Type
     */
    public function loadRolesByGroupId( $groupId )
    {
        $content = $this->backend->load( 'Content', $groupId );

        if ( !$content )
            throw new NotFound( 'Group', $groupId );
        if ( $content->typeId != 3 )
             throw new NotFoundWithType( "Content with TypeId:3", $groupId );

        return $this->backend->find(
            'User\\Role',
            array( 'groupIds' => $groupId ),
            array( 'policies' => array(
                'type' => 'User\\Policy',
                'match' => array( 'roleId' => 'id' ) )
            )
        );
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
        $this->backend->deleteByMatch( 'User\\Policy', array( 'roleId' => $roleId ) );
    }

    /**
     * Adds a policy to a role
     *
     * @param mixed $roleId
     * @param \ezp\Persistence\User\Policy $policy
     * @return \ezp\Persistence\User\Policy
     * @todo Throw on invalid Role Id?
     */
    public function addPolicy( $roleId, Policy $policy )
    {
        $policyArr = array( 'roleId' => $roleId ) + ( (array) $policy );
        return $this->backend->create( 'User\\Policy', $policyArr );
    }

    /**
     * Update a policy
     *
     * Replaces limitations values with new values.
     *
     * @param \ezp\Persistence\User\Policy $policy
     */
    public function updatePolicy( Policy $policy )
    {
        $policyArr = (array) $policy;
        $this->backend->update( 'User\\Policy', $policyArr['id'], $policyArr, false );
    }

    /**
     * Removes a policy from a role
     *
     * @param mixed $roleId
     * @param mixed $policyId
     * @return void
     * @todo Throw exception on missing role / policy?
     */
    public function removePolicy( $roleId, $policyId )
    {
        $this->backend->deleteByMatch( 'User\\Policy', array( 'id' => $policyId, 'roleId' => $roleId ) );
    }

    /**
     * Returns the user policies associated with the user
     *
     * @param mixed $userId
     * @return \ezp\Persistence\User\Policy[]
     * @throws \ezp\Base\Exception\NotFound If user (it's content object atm) is not found
     * @throws \ezp\Base\Exception\NotFoundWithType If user is not of user Content Type
     */
    public function loadPoliciesByUserId( $userId )
    {
        $list = $this->backend->find(
            'Content',
            array( 'id' => $userId ),
            array( 'locations' => array(
                'type' => 'Content\\Location',
                'match' => array( 'contentId' => 'id' ) )
            )
        );

        if ( !$list )
            throw new NotFound( 'User', $userId );

        $policies = array();
        $this->getPermissionsForObject( $list[0], 4, $policies );// @deprecated Roles assigned to user

        // crawl up path on all locations
        foreach ( $list[0]->locations as $location )
        {
            $parentIds = array_reverse( explode( '/', trim( $location->pathString, '/' ) ) );
            foreach ( $parentIds as $parentId )
            {
                if ( $parentId == $location->id )
                    continue;

                $list = $this->backend->find(
                    'Content',
                    array( 'locations' => array( 'id' => $parentId ) ),
                    array( 'locations' => array(
                        'type' => 'Content\\Location',
                        'match' => array( 'contentId' => 'id' ) )
                    )
                );

                if ( isset( $list[1] ) )
                    throw new Logic( 'content tree', 'there is more then one item with parentId:' . $parentId );
                else if ( $list )
                    $this->getPermissionsForObject( $list[0], 3, $policies );
            }
        }
        return array_values( $policies );
    }

    /**
     * @throws \ezp\Base\Exception\NotFoundWithType
     * @param \ezp\Persistence\Content $content
     * @param array $policies
     * @throws \ezp\Base\Exception\NotFoundWithType If $content is not of user_group Content Type
     */
    protected function getPermissionsForObject( Content $content, $typeId, array &$policies )
    {
        if ( $content->typeId != $typeId )
             throw new NotFoundWithType( "Content with TypeId:$typeId", $content->id );

        // fetch possible roles assigned to this object
        $list = $this->backend->find(
            'User\\Role',
            array( 'groupIds' => $content->id ),
            array( 'policies' => array(
                'type' => 'User\\Policy',
                'match' => array( 'roleId' => 'id' ) )
            )
        );

        // merge policies
        foreach ( $list as $role )
        {
            foreach ( $role->policies as $policy )
            {
                if ( !isset( $policies[$policy->id] ) )
                    $policies[$policy->id] = $policy;
            }
        }
    }

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
     * @throws \ezp\Base\Exception\NotFound If group or role is not found
     * @throws \ezp\Base\Exception\NotFoundWithType If group is not of user_group Content Type
     * @throws \ezp\Base\Exception\InvalidArgumentValue If group is already assigned role
     */
    public function assignRole( $groupId, $roleId, array $limitation = null )
    {
        $content = $this->backend->load( 'Content', $groupId );
        if ( !$content )
            throw new NotFound( 'User Group', $groupId );

        // @todo Use eZ Publish settings for this, and maybe a better exception
        if ( $content->typeId != 3 )
             throw new NotFoundWithType( 3, $groupId );

        $role = $this->loadRole( $roleId );
        if ( in_array( $groupId, $role->groupIds ) )
            throw new InvalidArgumentValue( '$roleId', $roleId );

        $role->groupIds[] = $groupId;
        $this->backend->update( 'User\\Role', $roleId, (array) $role );
    }

    /**
     * Un-assign a role
     *
     * @param mixed $groupId
     * @param mixed $roleId
     * @throws \ezp\Base\Exception\NotFound If group or role is not found
     * @throws \ezp\Base\Exception\NotFoundWithType If group is not of user[_group] Content Type
     * @throws \ezp\Base\Exception\InvalidArgumentValue If group does not contain role
     */
    public function unAssignRole( $groupId, $roleId )
    {
        $content = $this->backend->load( 'Content', $groupId );
        if ( !$content )
            throw new NotFound( 'User Group', $groupId );

        // @todo Use eZ Publish settings for this, and maybe a better exception
        if ( $content->typeId != 3 && $content->typeId != 4 )
             throw new NotFoundWithType( "3 or 4", $groupId );

        $role = $this->loadRole( $roleId );
        if ( !in_array( $groupId, $role->groupIds ) )
            throw new InvalidArgumentValue( '$roleId', $roleId );

        $role->groupIds = array_values( array_diff( $role->groupIds, array( $groupId ) ) );
        $this->backend->update( 'User\\Role', $roleId, (array) $role );
    }
}
?>
