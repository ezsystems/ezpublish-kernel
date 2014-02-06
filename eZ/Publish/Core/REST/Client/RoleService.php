<?php
/**
 * File containing the RoleService class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\RoleService as APIRoleService;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\API\Repository\Values\User\Policy as APIPolicy;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct as APIPolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct as APIPolicyUpdateStruct;
use eZ\Publish\API\Repository\Values\User\Role as APIRole;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct as APIRoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;

use eZ\Publish\Core\Repository\Values\User\UserRoleAssignment;
use eZ\Publish\Core\Repository\Values\User\UserGroupRoleAssignment;

use eZ\Publish\Core\REST\Client\Values\User\PolicyCreateStruct;
use eZ\Publish\Core\REST\Client\Values\User\PolicyUpdateStruct;
use eZ\Publish\Core\REST\Client\Values\User\Role;
use eZ\Publish\Core\REST\Client\Values\User\Policy;
use eZ\Publish\Core\REST\Client\Values\User\RoleAssignment;

use eZ\Publish\Core\REST\Common\RequestParser;
use eZ\Publish\Core\REST\Common\Input\Dispatcher;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Common\Message;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\RoleService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\RoleService
 */
class RoleService implements APIRoleService, Sessionable
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var Dispatcher
     */
    private $inputDispatcher;

    /**
     * @var Visitor
     */
    private $outputVisitor;

    /**
     * @var RequestParser
     */
    private $requestParser;

    /**
     * @param UserService $userService
     * @param HttpClient $client
     * @param Dispatcher $inputDispatcher
     * @param Visitor $outputVisitor
     * @param RequestParser $requestParser
     */
    public function __construct( UserService $userService, HttpClient $client, Dispatcher $inputDispatcher, Visitor $outputVisitor, RequestParser $requestParser )
    {
        $this->userService     = $userService;
        $this->client          = $client;
        $this->inputDispatcher = $inputDispatcher;
        $this->outputVisitor   = $outputVisitor;
        $this->requestParser   = $requestParser;
    }

    public function setSession( $id )
    {
        if ( $this->outputVisitor instanceof Sessionable )
        {
            $this->outputVisitor->setSession( $id );
        }
    }

    public function createRole( APIRoleCreateStruct $roleCreateStruct )
    {
        $inputMessage = $this->outputVisitor->visit( $roleCreateStruct );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'Role' );

        $result = $this->client->request(
            'POST',
            $this->requestParser->generate( 'roles' ),
            $inputMessage
        );

        $createdRole = $this->inputDispatcher->parse( $result );
        $createdRoleValues = $this->requestParser->parse( 'role', $createdRole->id );

        $createdPolicies = array();
        foreach ( $roleCreateStruct->getPolicies() as $policyCreateStruct )
        {
            $inputMessage = $this->outputVisitor->visit( $policyCreateStruct );
            $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'Policy' );

            $result = $this->client->request(
                'POST',
                $this->requestParser->generate( 'policies', array( 'role' => $createdRoleValues['role'] ) ),
                $inputMessage
            );

            $createdPolicy = $this->inputDispatcher->parse( $result );

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
                'identifier' => $createdRole->identifier
            ),
            $createdPolicies
        );
    }

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

    public function addPolicy( APIRole $role, APIPolicyCreateStruct $policyCreateStruct )
    {
        $values = $this->requestParser->parse( 'role', $role->id );
        $inputMessage = $this->outputVisitor->visit( $policyCreateStruct );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'Policy' );

        $result = $this->client->request(
            'POST',
            $this->requestParser->generate( 'policies', array( 'role' => $values['role'] ) ),
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
                'identifier' => $role->identifier
            ),
            $existingPolicies
        );
    }

    public function removePolicy( APIRole $role, APIPolicy $policy )
    {
        $values = $this->requestParser->parse( 'role', $role->id );
        $response = $this->client->request(
            'DELETE',
            $this->requestParser->generate(
                'policy',
                array(
                    'role' => $values['role'],
                    'policy' => $policy->id
                )
            ),
            new Message(
                // @todo: What media-type should we set here? Actually, it should be
                // all expected exceptions + none? Or is "Section" correct,
                // since this is what is to be expected by the resource
                // identified by the URL?
                array( 'Accept' => $this->outputVisitor->getMediaType( 'Policy' ) )
            )
        );

        if ( !empty( $response->body ) )
            $this->inputDispatcher->parse( $response );

        return $this->loadRole( $role->id );
    }

    public function deletePolicy( APIPolicy $policy )
    {
        throw new \Exception( "@todo: Implement." );
    }

    public function updatePolicy( APIPolicy $policy, APIPolicyUpdateStruct $policyUpdateStruct )
    {
        $values = $this->requestParser->parse( 'role', $policy->roleId );
        $inputMessage = $this->outputVisitor->visit( $policyUpdateStruct );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'Policy' );
        $inputMessage->headers['X-HTTP-Method-Override'] = 'PATCH';

        $result = $this->client->request(
            'POST',
            $this->requestParser->generate(
                'policy',
                array(
                    'role' => $values['role'],
                    'policy' => $policy->id
                )
            ),
            $inputMessage
        );

        return $this->inputDispatcher->parse( $result );
    }

    public function loadRole( $id )
    {
        $response = $this->client->request(
            'GET',
            $id,
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'Role' ) )
            )
        );

        $loadedRole = $this->inputDispatcher->parse( $response );
        $loadedRoleValues = $this->requestParser->parse( 'role', $loadedRole->id );
        $response = $this->client->request(
            'GET',
            $this->requestParser->generate( 'policies', array( 'role' => $loadedRoleValues['role'] ) ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'PolicyList' ) )
            )
        );

        $policies = $this->inputDispatcher->parse( $response );
        return new Role(
            array(
                'id' => $loadedRole->id,
                'identifier' => $loadedRole->identifier
            ),
            $policies
        );
    }

    public function loadRoleByIdentifier( $name )
    {
        $response = $this->client->request(
            'GET',
            $this->requestParser->generate( 'roleByIdentifier', array( 'role' => $name ) ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'RoleList' ) )
            )
        );

        $result = $this->inputDispatcher->parse( $response );
        return reset( $result );
    }

    public function loadRoles()
    {
        $response = $this->client->request(
            'GET',
            $this->requestParser->generate( 'roles' ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'RoleList' ) )
            )
        );

        return $this->inputDispatcher->parse( $response );
    }

    public function deleteRole( APIRole $role )
    {
        $response = $this->client->request(
            'DELETE',
            $role->id,
            new Message(
                // @todo: What media-type should we set here? Actually, it should be
                // all expected exceptions + none? Or is "Section" correct,
                // since this is what is to be expected by the resource
                // identified by the URL?
                array( 'Accept' => $this->outputVisitor->getMediaType( 'Role' ) )
            )
        );

        if ( !empty( $response->body ) )
            $this->inputDispatcher->parse( $response );
    }

    public function loadPoliciesByUserId( $userId )
    {
        $values = $this->requestParser->parse( 'user', $userId );
        $response = $this->client->request(
            'GET',
            $this->requestParser->generate( 'userPolicies', array( 'user' => $values['user'] ) ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'PolicyList' ) )
            )
        );

        return $this->inputDispatcher->parse( $response );
    }

    public function assignRoleToUserGroup( APIRole $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null )
    {
        $roleAssignment = new RoleAssignment(
            array(
                'role' => $role,
                'limitation' => $roleLimitation
            )
        );

        $inputMessage = $this->outputVisitor->visit( $roleAssignment );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'RoleAssignmentList' );

        $result = $this->client->request(
            'POST',
            $this->requestParser->generate( 'groupRoleAssignments', array( 'group' => $userGroup->id ) ),
            $inputMessage
        );

        $this->inputDispatcher->parse( $result );
    }

    public function unassignRoleFromUserGroup( APIRole $role, UserGroup $userGroup )
    {
        $values = $this->requestParser->parse( 'group', $userGroup->id );
        $userGroupId = $values['group'];

        $values = $this->requestParser->parse( 'role', $role->id );
        $roleId = $values['role'];

        $response = $this->client->request(
            'DELETE',
            $this->requestParser->generate( 'groupRoleAssignment', array( 'group' => $userGroupId, 'role' => $roleId ) ),
            new Message(
                // @todo: What media-type should we set here? Actually, it should be
                // all expected exceptions + none? Or is "Section" correct,
                // since this is what is to be expected by the resource
                // identified by the URL?
                array( 'Accept' => $this->outputVisitor->getMediaType( 'RoleAssignmentList' ) )
            )
        );

        if ( !empty( $response->body ) )
            $this->inputDispatcher->parse( $response );
    }

    /**
     * {@inheritDoc}
     * @todo add limitations
     */
    public function assignRoleToUser( APIRole $role, User $user, RoleLimitation $roleLimitation = null )
    {
        $roleAssignment = new RoleAssignment(
            array(
                'role' => $role,
                'limitation' => $roleLimitation
            )
        );

        $inputMessage = $this->outputVisitor->visit( $roleAssignment );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'RoleAssignmentList' );

        $result = $this->client->request(
            'POST',
            $this->requestParser->generate( 'userRoleAssignments', array( 'user' => $user->id ) ),
            $inputMessage
        );

        $this->inputDispatcher->parse( $result );
    }

    public function unassignRoleFromUser( APIRole $role, User $user )
    {
        $values = $this->requestParser->parse( 'user', $user->id );
        $userId = $values['user'];

        $values = $this->requestParser->parse( 'role', $role->id );
        $roleId = $values['role'];

        $response = $this->client->request(
            'DELETE',
            $this->requestParser->generate( 'userRoleAssignment', array( 'user' => $userId, 'role' => $roleId ) ),
            new Message(
                // @todo: What media-type should we set here? Actually, it should be
                // all expected exceptions + none? Or is "Section" correct,
                // since this is what is to be expected by the resource
                // identified by the URL?
                array( 'Accept' => $this->outputVisitor->getMediaType( 'RoleAssignmentList' ) )
            )
        );

        if ( !empty( $response->body ) )
            $this->inputDispatcher->parse( $response );
    }

    public function getRoleAssignments( APIRole $role )
    {
        throw new \Exception( "@todo: Implement." );
    }

    public function getRoleAssignmentsForUser( User $user )
    {
        $response = $this->client->request(
            'GET',
            $this->requestParser->generate( 'userRoleAssignments' ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'RoleAssignmentList' ) )
            )
        );

        /** @var RoleAssignment[] $roleAssignments */
        $roleAssignments = $this->inputDispatcher->parse( $response );

        $userRoleAssignments = array();
        foreach ( $roleAssignments as $roleAssignment )
        {
            $userRoleAssignments[] = new UserRoleAssignment(
                array(
                    'limitation' => $roleAssignment->getRoleLimitation(),
                    'role' => $roleAssignment->getRole(),
                    'user' => $user
                )
            );
        }

        return $userRoleAssignments;
    }

    public function getRoleAssignmentsForUserGroup( UserGroup $userGroup )
    {
        $response = $this->client->request(
            'GET',
            $this->requestParser->generate( 'groupRoleAssignments' ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'RoleAssignmentList' ) )
            )
        );

        /** @var UserGroupRoleAssignment[] $roleAssignments */
        $roleAssignments = $this->inputDispatcher->parse( $response );

        $userGroupRoleAssignments = array();
        foreach ( $roleAssignments as $roleAssignment )
        {
            $userGroupRoleAssignments[] = new UserGroupRoleAssignment(
                array(
                    'limitation' => $roleAssignment->getRoleLimitation(),
                    'role' => $roleAssignment->getRole(),
                    'userGroup' => $userGroup
                )
            );
        }

        return $userGroupRoleAssignments;
    }

    public function newRoleCreateStruct( $name )
    {
        return new Values\User\RoleCreateStruct( $name );
    }

    public function newPolicyCreateStruct( $module, $function )
    {
        return new PolicyCreateStruct( $module, $function );
    }

    public function newPolicyUpdateStruct()
    {
        return new PolicyUpdateStruct();
    }

    public function newRoleUpdateStruct()
    {
        return new RoleUpdateStruct();
    }

    public function getLimitationType( $identifier )
    {
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException( __METHOD__ );
    }

    public function getLimitationTypesByModuleFunction( $module, $function )
    {
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException( __METHOD__ );
    }
}
