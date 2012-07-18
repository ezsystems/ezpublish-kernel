<?php
/**
 * File containing the RoleService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client;

use \eZ\Publish\API\Repository\Values\Content\Content;
use \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use \eZ\Publish\API\Repository\Values\User\Policy as APIPolicy;
use \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct as APIPolicyCreateStruct;
use \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct as APIPolicyUpdateStruct;
use \eZ\Publish\API\Repository\Values\User\Role as APIRole;
use \eZ\Publish\API\Repository\Values\User\RoleCreateStruct as APIRoleCreateStruct;
use \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use \eZ\Publish\API\Repository\Values\User\User;
use \eZ\Publish\API\Repository\Values\User\UserGroup;

use \eZ\Publish\Core\REST\Client\Values\User\PolicyCreateStruct;
use \eZ\Publish\Core\REST\Client\Values\User\PolicyUpdateStruct;
use \eZ\Publish\Core\REST\Client\Values\User\Role;
use \eZ\Publish\Core\REST\Client\Values\User\Policy;

use \eZ\Publish\Core\REST\Common\UrlHandler;
use \eZ\Publish\Core\REST\Common\Input;
use \eZ\Publish\Core\REST\Common\Output;
use \eZ\Publish\Core\REST\Common\Message;
use \eZ\Publish\Core\REST\Client\Sessionable;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\RoleService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\RoleService
 */
class RoleService implements \eZ\Publish\API\Repository\RoleService, Sessionable
{
    /**
     * @var \eZ\Publish\Core\REST\Client\UserService
     */
    private $userService;

    /**
     * @var \eZ\Publish\Core\REST\Client\HttpClient
     */
    private $client;

    /**
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    private $inputDispatcher;

    /**
     * @var \eZ\Publish\Core\REST\Common\Output\Visitor
     */
    private $outputVisitor;

    /**
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    private $urlHandler;

    /**
     * @param \eZ\Publish\Core\REST\Client\UserService $userService
     * @param \eZ\Publish\Core\REST\Client\HttpClient $client
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $outputVisitor
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     */
    public function __construct( UserService $userService, HttpClient $client, Input\Dispatcher $inputDispatcher, Output\Visitor $outputVisitor, UrlHandler $urlHandler )
    {
        $this->userService     = $userService;
        $this->client          = $client;
        $this->inputDispatcher = $inputDispatcher;
        $this->outputVisitor   = $outputVisitor;
        $this->urlHandler      = $urlHandler;
    }

    /**
     * Set session ID
     *
     * Only for testing
     *
     * @param mixed $id
     * @return void
     * @private
     */
    public function setSession( $id )
    {
        if ( $this->outputVisitor instanceof Sessionable )
        {
            $this->outputVisitor->setSession( $id );
        }
    }

    /**
     * Creates a new Role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the name of the role already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function createRole( APIRoleCreateStruct $roleCreateStruct )
    {
        $inputMessage = $this->outputVisitor->visit( $roleCreateStruct );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'Role' );

        $result = $this->client->request(
            'POST',
            $this->urlHandler->generate( 'roles' ),
            $inputMessage
        );

        // If error occurred (in which case the return value is not role value object)
        // do not add policies if any, only return received response
        $createdRole = $this->inputDispatcher->parse( $result );
        if ( !$createdRole instanceof APIRole )
            return $createdRole;

        $createdPolicies = array();
        $policyCreateStructs = $roleCreateStruct->getPolicies();
        foreach ( $policyCreateStructs as $policyCreateStruct )
        {
            $inputMessage = $this->outputVisitor->visit( $policyCreateStruct );
            $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'Policy' );

            $result = $this->client->request(
                'POST',
                $createdRole->id . '/policies',
                $inputMessage
            );

            $createdPolicy = $this->inputDispatcher->parse( $result );

            // Same case with creating role, if error occurred
            // return the response
            if ( !$createdPolicy instanceof Policy )
                return $createdPolicy;

            // @todo Workaround for missing roleId in Policy XSD definition
            $createdPolicyArray = array(
                'id' => $createdPolicy->id,
                'roleId' => $createdRole->id,
                'module' => $createdPolicy->module,
                'function' => $createdPolicy->function
            );

            $createdPolicy = new Policy( $createdPolicyArray );

            $createdPolicies[] = $createdPolicy;
        }

        return new Role(
            array(
                'id' => $createdRole->id,
                'identifier' => $createdRole->identifier,
                'mainLanguageCode' => $createdRole->mainLanguageCode,
                'names' => $createdRole->getNames(),
                'descriptions' => $createdRole->getDescriptions()
            ),
            $createdPolicies
        );
    }

    /**
     * Updates the name and (5.x) description of the role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the name of the role already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct $roleUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function updateRole( APIRole $role, RoleUpdateStruct $roleUpdateStruct )
    {
        $inputMessage = $this->outputVisitor->visit( $roleUpdateStruct );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'Role' );
        $inputMessage->headers['X-HTTP-Method-Override'] = 'PATCH';

        $result = $this->client->request(
            'POST',
            $role->id,
            $inputMessage
        );

        return $this->inputDispatcher->parse( $result );
    }

    /**
     * adds a new policy to the role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to add  a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function addPolicy( APIRole $role, APIPolicyCreateStruct $policyCreateStruct )
    {
        $inputMessage = $this->outputVisitor->visit( $policyCreateStruct );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'Policy' );

        $result = $this->client->request(
            'POST',
            $role->id . '/policies',
            $inputMessage
        );

        $createdPolicy = $this->inputDispatcher->parse( $result );

        // @todo Workaround for missing roleId in Policy XSD definition
        $createdPolicyArray = array(
            'id' => $createdPolicy->id,
            'roleId' => $role->id,
            'module' => $createdPolicy->module,
            'function' => $createdPolicy->function
        );

        $createdPolicy = new Policy( $createdPolicyArray );

        $existingPolicies = $role->getPolicies();
        $existingPolicies[] = $createdPolicy;

        return new Role(
            array(
                'id' => $role->id,
                'identifier' => $role->identifier,
                'mainLanguageCode' => $role->mainLanguageCode,
                'names' => $role->getNames(),
                'descriptions' => $role->getDescriptions(),
            ),
            $existingPolicies
        );
    }

    /**
     * removes a policy from the role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy the policy to remove from the role
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role the updated role
     */
    public function removePolicy( APIRole $role, APIPolicy $policy )
    {
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitations are replaced by the ones in $roleUpdateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to u�date a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function updatePolicy( APIPolicy $policy, APIPolicyUpdateStruct $policyUpdateStruct )
    {
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * loads a role for the given id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a role with the given name was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRole( $id )
    {
        $response = $this->client->request(
            'GET',
            $id,
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'Role' ) )
            )
        );
        return $this->inputDispatcher->parse( $response );
    }

    /**
     * loads a role for the given name
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a role with the given name was not found
     *
     * @param string $name
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRoleByIdentifier( $name )
    {
        $response = $this->client->request(
            'GET',
            $this->urlHandler->generate( 'roleByIdentifier', array( 'role' => $name ) ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'RoleList' ) )
            )
        );

        $result = $this->inputDispatcher->parse( $response );
        return reset( $result );
    }

    /**
     * loads all roles
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the roles
     *
     * @return array an array of {@link \eZ\Publish\API\Repository\Values\User\Role}
     */
    public function loadRoles()
    {
        $response = $this->client->request(
            'GET',
            $this->urlHandler->generate( 'roles' ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'RoleList' ) )
            )
        );

        return $this->inputDispatcher->parse( $response );
    }

    /**
     * deletes the given role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     */
    public function deleteRole( APIRole $role )
    {
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * loads all policies from roles which are assigned to a user or to user groups to which the user belongs
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given id was not found
     *
     * @param mixed $userId
     *
     * @return array an array of {@link Policy}
     */
    public function loadPoliciesByUserId( $userId )
    {
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * assigns a role to the given user group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUserGroup( APIRole $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null )
    {
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * removes a role from the given user group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  If the role is not assigned to the given user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     */
    public function unassignRoleFromUserGroup( APIRole $role, UserGroup $userGroup )
    {
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * assigns a role to the given user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     *
     * @todo add limitations
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUser( APIRole $role, User $user, RoleLimitation $roleLimitation = null )
    {
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * removes a role from the given user.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the role is not assigned to the user
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     */
    public function unassignRoleFromUser( APIRole $role, User $user )
    {
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * returns the assigned user and user groups to this role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment[] an array of {@link RoleAssignment}
     */
    public function getRoleAssignments( APIRole $role )
    {
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * returns the roles assigned to the given user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a user
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserRoleAssignment[] an array of {@link UserRoleAssignment}
     */
    public function getRoleAssignmentsForUser( User $user )
    {
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * returns the roles assigned to the given user group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment[] an array of {@link UserGroupRoleAssignment}
     */
    public function getRoleAssignmentsForUserGroup( UserGroup $userGroup )
    {
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * Instantiates a role create class
     *
     * @param string $name
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
     */
    public function newRoleCreateStruct( $name )
    {
        return new Values\User\RoleCreateStruct( $name );
    }

    /**
     * Instantiates a policy create class
     *
     * @param string $module
     * @param string $function
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct
     */
    public function newPolicyCreateStruct( $module, $function )
    {
        return new PolicyCreateStruct( $module, $function );
    }

    /**
     * Instantiates a policy update class
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
     */
    public function newPolicyUpdateStruct()
    {
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * Instantiates a policy update class
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct
     */
    public function newRoleUpdateStruct()
    {
        return new RoleUpdateStruct();
    }
}
