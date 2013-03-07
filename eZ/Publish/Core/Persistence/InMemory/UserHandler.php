<?php
/**
 * File containing the User Handler inMemory impl
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;

use eZ\Publish\SPI\Persistence\User\Handler as UserHandlerInterface;
use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleAssignment;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use LogicException;

/**
 * Storage Engine handler for user module
 */
class UserHandler implements UserHandlerInterface
{
    /**
     * @var \eZ\Publish\Core\Persistence\InMemory\Handler
     */
    protected $handler;

    /**
     * @var \eZ\Publish\Core\Persistence\InMemory\Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to Handler object that created it.
     *
     * @param \eZ\Publish\Core\Persistence\InMemory\Handler $handler
     * @param \eZ\Publish\Core\Persistence\InMemory\Backend $backend The storage engine backend
     */
    public function __construct( Handler $handler, Backend $backend )
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
     * @param \eZ\Publish\SPI\Persistence\User $user
     *
     * @throws LogicException If no id was provided or if it already exists
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function create( User $user )
    {
        $userArr = (array)$user;
        return $this->backend->create( 'User', $userArr, false );
    }

    /**
     * Loads user with user ID.
     *
     * @param mixed $userId
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function load( $userId )
    {
        return $this->backend->load( 'User', $userId );
    }

    /**
     * Loads user with user login / email.
     *
     * @param string $login
     * @param boolean $alsoMatchEmail Also match user email, caller must verify that $login is a valid email address.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no users are found
     *
     * @return \eZ\Publish\SPI\Persistence\User[]
     */
    public function loadByLogin( $login, $alsoMatchEmail = false )
    {
        $users = $this->backend->find( 'User', array( 'login' => $login ) );
        if ( !$alsoMatchEmail )
        {
            if ( empty( $users ) )
                throw new NotFound( 'User', $login );

            return $users;
        }

        foreach ( $this->backend->find( 'User', array( 'email' => $login ) ) as $emailUser )
        {
            foreach ( $users as $loginUser )
            {
                if ( $emailUser->id === $loginUser->id )
                    continue 2;
            }
            $users[] = $emailUser;
        }

        if ( empty( $users ) )
            throw new NotFound( 'User', $login );

        return $users;
    }

    /**
     * Update the user information specified by the user struct
     *
     * @param \eZ\Publish\SPI\Persistence\User $user
     */
    public function update( User $user )
    {
        $userArr = (array)$user;
        $this->backend->update( 'User', $userArr['id'], $userArr );
    }

    /**
     * Delete user with the given ID.
     *
     * @param mixed $userId
     *
     * @todo Throw on missing user?
     */
    public function delete( $userId )
    {
        $this->backend->delete( 'User', $userId );
        $this->backend->deleteByMatch( 'User\\RoleAssignment', array( 'contentId' => $userId ) );
    }

    /**
     * Create new role
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role $role
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function createRole( Role $role )
    {
        if ( !$role->identifier )
            throw new InvalidArgumentValue( '$role->identifier', $role->identifier );

        $roleArr = (array)$role;
        $roleArr['policies'] = array();
        $newRole = $this->backend->create( 'User\\Role', $roleArr );
        foreach ( $role->policies as $policy )
        {
            $newRole->policies[] = $this->addPolicy( $newRole->id, $policy );
        }
        return $newRole;
    }

    /**
     * Loads a specified role by id
     *
     * @param mixed $roleId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If role is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function loadRole( $roleId )
    {
        $list = $this->backend->find(
            'User\\Role',
            array( 'id' => $roleId ),
            array(
                'policies' => array(
                    'type' => 'User\\Policy',
                    'match' => array( 'roleId' => 'id' )
                )
            )
        );
        if ( !$list )
            throw new NotFound( 'User\\Role', $roleId );

        return $list[0];
    }

    /**
     * Loads a specified role by $identifier
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If role is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function loadRoleByIdentifier( $identifier )
    {
        $list = $this->backend->find(
            'User\\Role',
            array( 'identifier' => $identifier ),
            array(
                'policies' => array(
                    'type' => 'User\\Policy',
                    'match' => array( 'roleId' => 'id' )
                )
            )
        );
        if ( !$list )
            throw new NotFound( 'User\\Role', $identifier );

        return $list[0];
    }

    /**
     * Loads all roles
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role[]
     */
    public function loadRoles()
    {
        return $this->backend->find(
            'User\\Role',
            array(),
            array(
                'policies' => array(
                    'type' => 'User\\Policy',
                    'match' => array( 'roleId' => 'id' )
                )
            )
        );
    }

    /**
     * Loads roles assignments Role
     *
     * Role Assignments with same roleId and limitationIdentifier will be merged together into one.
     *
     * @param mixed $roleId
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleAssignment[]
     */
    public function loadRoleAssignmentsByRoleId( $roleId )
    {
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException( __METHOD__ );
    }

    /**
     * Loads roles assignments to a user/group
     *
     * Role Assignments with same roleId and limitationIdentifier will be merged together into one.
     *
     * @param mixed $groupId In legacy storage engine this is the content object id roles are assigned to in ezuser_role.
     *                      By the nature of legacy this can currently also be used to get by $userId.
     * @param boolean $inherit If true also return inherited role assignments from user groups.
     *
     * @throws \LogicException Internal data corruption error
     * @uses getRoleAssignmentForContent()
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleAssignment[]
     */
    public function loadRoleAssignmentsByGroupId( $groupId, $inherit = false )
    {
        if ( $inherit === false )
            return $this->getRoleAssignmentForContent( array( $groupId ) );

        $contentIds = array( $groupId );
        $locations = $this->handler->locationHandler()->loadLocationsByContent( $groupId );
        if ( empty( $locations ) )
            return array();

        // crawl up path on all locations
        foreach ( $locations as $location )
        {
            $parentIds = array_reverse( explode( '/', trim( $location->pathString, '/' ) ) );
            foreach ( $parentIds as $parentId )
            {
                if ( $parentId == $location->id )
                    continue;

                $location = $this->backend->load( 'Content\\Location', $parentId );
                $list = $this->backend->find(
                    'Content',
                    array( 'id' => $location->contentId ),
                    array(
                        'versionInfo' => array(
                            'type' => 'Content\\VersionInfo',
                            'match' => array( '_contentId' => 'id', 'versionNo' => '_currentVersionNo' ),
                            'single' => true,
                            'sub' => array(
                                'contentInfo' => array(
                                    'type' => 'Content\\ContentInfo',
                                    'match' => array( 'id' => '_contentId' ),
                                    'single' => true
                                ),
                            )
                        ),
                    )
                );

                if ( isset( $list[1] ) )
                    throw new LogicException( "'content tree' logic error, there is more than one item with parentId: $parentId" );
                if ( $list )
                    $contentIds[] = $list[0]->versionInfo->contentInfo->id;
            }
        }

        return $this->getRoleAssignmentForContent( $contentIds );
    }

    /**
     * @param array $contentIds
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleAssignment[]
     */
    protected function getRoleAssignmentForContent( array $contentIds )
    {
        /** fetch possible roles assigned to this object
         * @var \eZ\Publish\SPI\Persistence\User\RoleAssignment[] $list
         */
        $list = $this->backend->find(
            'User\\RoleAssignment',
            array( 'contentId' => $contentIds )
        );

        // merge policies
        $data = array();
        foreach ( $list as $new )
        {
            // if user already have full access to a role, continue
            if ( isset( $data[$new->roleId][$new->contentId] )
              && $data[$new->roleId][$new->contentId] instanceof RoleAssignment )
                continue;

            if ( !empty( $new->limitationIdentifier ) )
            {
                if ( !isset( $data[$new->roleId][$new->contentId][$new->limitationIdentifier] ) )
                {
                    $new->values = array( $new->values );
                    $data[$new->roleId][$new->contentId][$new->limitationIdentifier] = $new;
                }
                // merge limitation values
                else
                {
                    $data[$new->roleId][$new->contentId][$new->limitationIdentifier]->values[] = $new->values;
                }
            }
            else
            {
                $data[$new->roleId][$new->contentId] = $new;
            }
        }

        // flatten structure
        $roleAssignments = array();
        array_walk_recursive(
            $data,
            function ( $roleAssignment ) use ( &$roleAssignments )
            {
                $roleAssignments[] = $roleAssignment;
            }
        );

        return $roleAssignments;
    }

    /**
     * Update role
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleUpdateStruct $role
     */
    public function updateRole( RoleUpdateStruct $role )
    {
        if ( !$role->id )
            throw new InvalidArgumentValue( '$role->id', $role->id );

        if ( !$role->identifier )
            throw new InvalidArgumentValue( '$role->identifier', $role->identifier );

        $roleArr = (array)$role;
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
        $this->backend->deleteByMatch( 'User\\RoleAssignment', array( 'roleId' => $roleId ) );
    }

    /**
     * Adds a policy to a role
     *
     * @param mixed $roleId
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     *
     * @return \eZ\Publish\SPI\Persistence\User\Policy
     * @todo Throw on invalid Role Id?
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue If $policy->limitation is empty (null, empty string/array..)
     */
    public function addPolicy( $roleId, Policy $policy )
    {
        if ( empty( $policy->limitations ) )
            throw new InvalidArgumentValue( '->limitations', $policy->limitations, get_class( $policy ) );

        $policyArr = array( 'roleId' => $roleId ) + ( (array)$policy );
        return $this->backend->create( 'User\\Policy', $policyArr );
    }

    /**
     * Update a policy
     *
     * Replaces limitations values with new values.
     *
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue If $policy->limitation is empty (null, empty string/array..)
     */
    public function updatePolicy( Policy $policy )
    {
        if ( empty( $policy->limitations ) )
            throw new InvalidArgumentValue( '->limitations', $policy->limitations, get_class( $policy ) );

        $policyArr = (array)$policy;
        $this->backend->update( 'User\\Policy', $policyArr['id'], $policyArr, false );
    }

    /**
     * Removes a policy from a role
     *
     * @param mixed $roleId
     * @param mixed $policyId
     *
     * @todo Throw exception on missing role / policy?
     *
     * @return void
     */
    public function removePolicy( $roleId, $policyId )
    {
        $this->backend->deleteByMatch( 'User\\Policy', array( 'id' => $policyId, 'roleId' => $roleId ) );
    }

    /**
     * Returns the user policies associated with the user (including inherited policies from user groups)
     *
     * @param mixed $userId
     *
     * @return \eZ\Publish\SPI\Persistence\User\Policy[]
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If user (it's content object atm) is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If user is not of user Content Type
     */
    public function loadPoliciesByUserId( $userId )
    {
        $list = $this->backend->find(
            'Content',
            array( 'id' => $userId ),
            array(
                'versionInfo' => array(
                    'type' => 'Content\\VersionInfo',
                    'match' => array( '_contentId' => 'id', 'versionNo' => '_currentVersionNo' ),
                    'single' => true,
                    'sub' => array(
                        'contentInfo' => array(
                            'type' => 'Content\\ContentInfo',
                            'match' => array( 'id' => '_contentId' ),
                            'single' => true
                        ),
                    )
                )
            )
        );

        if ( !$list )
            throw new NotFound( 'User', $userId );

        $policies = array();
        $this->getPermissionsForObject( $list[0], 4, $policies );

        // crawl up path on all locations
        $locations = $this->handler->locationHandler()->loadLocationsByContent(
            $list[0]->versionInfo->contentInfo->id
        );
        foreach ( $locations as $location )
        {
            $parentIds = array_reverse( explode( '/', trim( $location->pathString, '/' ) ) );
            foreach ( $parentIds as $parentId )
            {
                if ( $parentId == $location->id )
                    continue;

                $location = $this->backend->load( 'Content\\Location', $parentId );
                $list2 = $this->backend->find(
                    'Content',
                    array( 'id' => $location->contentId ),
                    array(
                        'versionInfo' => array(
                            'type' => 'Content\\VersionInfo',
                            'match' => array( '_contentId' => 'id', 'versionNo' => '_currentVersionNo' ),
                            'single' => true,
                            'sub' => array(
                                'contentInfo' => array(
                                    'type' => 'Content\\ContentInfo',
                                    'match' => array( 'id' => '_contentId' ),
                                    'single' => true
                                ),
                            )
                        )
                    )
                );

                if ( isset( $list2[1] ) )
                    throw new LogicException( "'content tree' logic error, there is more than one item with parentId: $parentId" );
                if ( $list2 )
                    $this->getPermissionsForObject( $list2[0], 3, $policies );
            }
        }
        return array_values( $policies );
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param mixed $typeId
     * @param array $policies
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $content is not type $typeId
     *
     * @return void
     */
    protected function getPermissionsForObject( Content $content, $typeId, array &$policies )
    {
        if ( $content->versionInfo->contentInfo->contentTypeId != $typeId )
            throw new NotFound( "Content with TypeId:$typeId", $content->versionInfo->contentInfo->id );

        // fetch possible roles assigned to this object
        $list = $this->backend->find(
            'User\\Role',
            array( 'groupIds' => $content->versionInfo->contentInfo->id ),
            array(
                'policies' => array(
                    'type' => 'User\\Policy',
                    'match' => array( 'roleId' => 'id' )
                )
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
     * Assigns role to a user or user group with given limitations
     *
     * The limitation array looks like:
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
     * @param mixed $contentId The groupId or userId to assign the role to.
     * @param mixed $roleId
     * @param array $limitation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group or role is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group is not of user_group Content Type
     */
    public function assignRole( $contentId, $roleId, array $limitation = null )
    {
        $content = $this->backend->load( 'Content\\ContentInfo', $contentId );
        if ( !$content )
            throw new NotFound( 'User Group', $contentId );

        $role = $this->backend->load( 'User\\Role', $roleId );
        if ( !$role )
            throw new NotFound( 'Role', $roleId );

        // @todo Use eZ Publish settings for this, and maybe a better exception
        if ( $content->contentTypeId != 3 && $content->contentTypeId != 4 )
            throw new NotFound( "Content", $contentId );

        if ( is_array( $limitation ) )
        {
            foreach ( $limitation as $limitIdentifier => $limitValues )
            {
                $this->backend->create(
                    'User\\RoleAssignment',
                    array(
                        'roleId' => $roleId,
                        'contentId' => $contentId,
                        'limitationIdentifier' => $limitIdentifier,
                        'values' => $limitValues
                    )
                );
            }
        }
        else
        {
            $this->backend->create(
                'User\\RoleAssignment',
                array(
                    'roleId' => $roleId,
                    'contentId' => $contentId,
                    'limitationIdentifier' => null,
                    'values' => null
                )
            );
        }

        $role->groupIds[] = $contentId;
        $this->backend->update( 'User\\Role', $roleId, (array)$role );
    }

    /**
     * Un-assign a role
     *
     * @param mixed $contentId The user or user group Id to un-assign the role from.
     * @param mixed $roleId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group or role is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group is not of user[_group] Content Type
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue If group does not contain role
     */
    public function unAssignRole( $contentId, $roleId )
    {
        $content = $this->backend->load( 'Content\\ContentInfo', $contentId );
        if ( !$content )
            throw new NotFound( 'User Group', $contentId );

        $role = $this->backend->load( 'User\\Role', $roleId );
        if ( !$role )
            throw new NotFound( 'Role', $roleId );

        // @todo Use eZ Publish settings for this, and maybe a better exception
        if ( $content->contentTypeId != 3 && $content->contentTypeId != 4 )
            throw new NotFound( "3 or 4", $contentId );

        $roleAssignments = $this->backend->find(
            'User\\RoleAssignment',
            array( 'roleId' => $roleId, 'contentId' => $contentId )
        );

        if ( empty( $roleAssignments ) )
            throw new InvalidArgumentValue( '$roleId', $roleId );

        $this->backend->deleteByMatch( 'User\\RoleAssignment', array( 'roleId' => $roleId, 'contentId' => $contentId ) );

        $role->groupIds = array_values( array_diff( $role->groupIds, array( $contentId ) ) );
        $this->backend->update( 'User\\Role', $roleId, (array)$role );
    }
}
