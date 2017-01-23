<?php

/**
 * File containing the UserHandler interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\User;

use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Handler as BaseUserHandler;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleCreateStruct;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;
use eZ\Publish\Core\Persistence\Legacy\Exception\RoleNotFound;
use eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway as RoleGateway;
use eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationConverter;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use LogicException;

/**
 * Storage Engine handler for user module.
 */
class Handler implements BaseUserHandler
{
    /**
     * Gateway for storing user data.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\User\Gateway
     */
    protected $userGateway;

    /**
     * Gateway for storing role data.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway
     */
    protected $roleGateway;

    /**
     * Mapper for user related objects.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\User\Mapper
     */
    protected $mapper;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationConverter
     */
    protected $limitationConverter;

    /**
     * Construct from userGateway.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\User\Gateway $userGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway $roleGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\User\Mapper $mapper
     * @param \eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationConverter $limitationConverter
     */
    public function __construct(Gateway $userGateway, RoleGateway $roleGateway, Mapper $mapper, LimitationConverter $limitationConverter)
    {
        $this->userGateway = $userGateway;
        $this->roleGateway = $roleGateway;
        $this->mapper = $mapper;
        $this->limitationConverter = $limitationConverter;
    }

    /**
     * Create a user.
     *
     * The User struct used to create the user will contain an ID which is used
     * to reference the user.
     *
     * @param \eZ\Publish\SPI\Persistence\User $user
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function create(User $user)
    {
        $this->userGateway->createUser($user);

        return $user;
    }

    /**
     * Loads user with user ID.
     *
     * @param mixed $userId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If user is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function load($userId)
    {
        $data = $this->userGateway->load($userId);

        if (empty($data)) {
            throw new NotFound('user', $userId);
        }

        return $this->mapper->mapUser(reset($data));
    }

    /**
     * Loads user with user login.
     *
     * @param string $login
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If user is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function loadByLogin($login)
    {
        $data = $this->userGateway->loadByLogin($login);

        if (empty($data)) {
            throw new NotFound('user', $login);
        } elseif (isset($data[1])) {
            throw new LogicException("Found more then one user with login '{$login}'");
        }

        return $this->mapper->mapUser($data[0]);
    }

    /**
     * Loads user(s) with user email.
     *
     * As earlier eZ Publish versions supported several users having same email (ini config),
     * this function may return several users.
     *
     * @param string $email
     *
     * @return \eZ\Publish\SPI\Persistence\User[]
     */
    public function loadByEmail($email)
    {
        $data = $this->userGateway->loadByEmail($email);

        if (empty($data)) {
            return array();
        }

        return $this->mapper->mapUsers($data);
    }

    /**
     * Update the user information specified by the user struct.
     *
     * @param \eZ\Publish\SPI\Persistence\User $user
     */
    public function update(User $user)
    {
        $this->userGateway->updateUser($user);
    }

    /**
     * Delete user with the given ID.
     *
     * @param mixed $userId
     */
    public function delete($userId)
    {
        $this->userGateway->deleteUser($userId);
    }

    /**
     * Create new role draft.
     *
     * Sets status to Role::STATUS_DRAFT on the new returned draft.
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleCreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function createRole(RoleCreateStruct $createStruct)
    {
        return $this->internalCreateRole($createStruct);
    }

    /**
     * Creates a draft of existing defined role.
     *
     * Sets status to Role::STATUS_DRAFT on the new returned draft.
     *
     * @param mixed $roleId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If role with defined status is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function createRoleDraft($roleId)
    {
        $createStruct = $this->mapper->createCreateStructFromRole(
            $this->loadRole($roleId)
        );

        return $this->internalCreateRole($createStruct, $roleId);
    }

    /**
     * Internal method for creating Role.
     *
     * Used by self::createRole() and self::createRoleDraft()
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleCreateStruct $createStruct
     * @param mixed|null $roleId Used by self::createRoleDraft() to retain Role id in the draft
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    protected function internalCreateRole(RoleCreateStruct $createStruct, $roleId = null)
    {
        $createStruct = clone $createStruct;
        $role = $this->mapper->createRoleFromCreateStruct(
            $createStruct
        );
        $role->id = $roleId;
        $role->status = Role::STATUS_DRAFT;

        $this->roleGateway->createRole($role);

        foreach ($role->policies as $policy) {
            $this->addPolicyByRoleDraft($role->id, $policy);
        }

        return $role;
    }

    /**
     * Loads a specified role (draft) by $roleId and $status.
     *
     * @param mixed $roleId
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If role with given status does not exist
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function loadRole($roleId, $status = Role::STATUS_DEFINED)
    {
        $data = $this->roleGateway->loadRole($roleId, $status);

        if (empty($data)) {
            throw new RoleNotFound($roleId, $status);
        }

        $role = $this->mapper->mapRole($data);
        foreach ($role->policies as $policy) {
            $this->limitationConverter->toSPI($policy);
        }

        return $role;
    }

    /**
     * Loads a specified role (draft) by $identifier and $status.
     *
     * @param string $identifier
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If role is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function loadRoleByIdentifier($identifier, $status = Role::STATUS_DEFINED)
    {
        $data = $this->roleGateway->loadRoleByIdentifier($identifier, $status);

        if (empty($data)) {
            throw new RoleNotFound($identifier, $status);
        }

        $role = $this->mapper->mapRole($data);
        foreach ($role->policies as $policy) {
            $this->limitationConverter->toSPI($policy);
        }

        return $role;
    }

    /**
     * Loads a role draft by the original role ID.
     *
     * @param mixed $roleId ID of the role the draft was created from.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If role is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function loadRoleDraftByRoleId($roleId)
    {
        $data = $this->roleGateway->loadRoleDraftByRoleId($roleId);

        if (empty($data)) {
            throw new RoleNotFound($roleId, Role::STATUS_DRAFT);
        }

        $role = $this->mapper->mapRole($data);
        foreach ($role->policies as $policy) {
            $this->limitationConverter->toSPI($policy);
        }

        return $role;
    }

    /**
     * Loads all roles.
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role[]
     */
    public function loadRoles()
    {
        $data = $this->roleGateway->loadRoles();

        $roles = $this->mapper->mapRoles($data);
        foreach ($roles as $role) {
            foreach ($role->policies as $policy) {
                $this->limitationConverter->toSPI($policy);
            }
        }

        return $roles;
    }

    /**
     * Update role (draft).
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleUpdateStruct $role
     */
    public function updateRole(RoleUpdateStruct $role)
    {
        $this->roleGateway->updateRole($role);
    }

    /**
     * Delete the specified role (draft).
     *
     * @param mixed $roleId
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     */
    public function deleteRole($roleId, $status = Role::STATUS_DEFINED)
    {
        $role = $this->loadRole($roleId, $status);

        foreach ($role->policies as $policy) {
            $this->roleGateway->removePolicy($policy->id);
        }

        $this->roleGateway->deleteRole($role->id, $status);
    }

    /**
     * Publish the specified role draft.
     *
     * @param mixed $roleDraftId
     */
    public function publishRoleDraft($roleDraftId)
    {
        $roleDraft = $this->loadRole($roleDraftId, Role::STATUS_DRAFT);

        try {
            $originalRoleId = $roleDraft->originalId;
            $role = $this->loadRole($originalRoleId);
            $roleAssignments = $this->loadRoleAssignmentsByRoleId($role->id);
            $this->deleteRole($role->id);

            foreach ($roleAssignments as $roleAssignment) {
                if (empty($roleAssignment->limitationIdentifier)) {
                    $this->assignRole($roleAssignment->contentId, $originalRoleId);
                } else {
                    $this->assignRole(
                        $roleAssignment->contentId,
                        $originalRoleId,
                        [$roleAssignment->limitationIdentifier => $roleAssignment->values]
                    );
                }
            }
            $this->roleGateway->publishRoleDraft($roleDraft->id, $role->id);
        } catch (NotFound $e) {
            // If no published role is found, only publishing is needed, without specifying original role ID as there is none.
            $this->roleGateway->publishRoleDraft($roleDraft->id);
        }
    }

    /**
     * Adds a policy to a role draft.
     *
     * @param mixed $roleId
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     *
     * @return \eZ\Publish\SPI\Persistence\User\Policy
     */
    public function addPolicyByRoleDraft($roleId, Policy $policy)
    {
        $legacyPolicy = clone $policy;
        $legacyPolicy->originalId = $policy->id;
        $this->limitationConverter->toLegacy($legacyPolicy);

        $this->roleGateway->addPolicy($roleId, $legacyPolicy);
        $policy->id = $legacyPolicy->id;
        $policy->originalId = $legacyPolicy->originalId;
        $policy->roleId = $legacyPolicy->roleId;

        return $policy;
    }

    /**
     * Adds a policy to a role.
     *
     * @param mixed $roleId
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     *
     * @return \eZ\Publish\SPI\Persistence\User\Policy
     */
    public function addPolicy($roleId, Policy $policy)
    {
        $legacyPolicy = clone $policy;
        $this->limitationConverter->toLegacy($legacyPolicy);

        $this->roleGateway->addPolicy($roleId, $legacyPolicy);
        $policy->id = $legacyPolicy->id;
        $policy->roleId = $legacyPolicy->roleId;

        return $policy;
    }

    /**
     * Update a policy.
     *
     * Replaces limitations values with new values.
     *
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     */
    public function updatePolicy(Policy $policy)
    {
        $policy = clone $policy;
        $this->limitationConverter->toLegacy($policy);

        $this->roleGateway->removePolicyLimitations($policy->id);
        $this->roleGateway->addPolicyLimitations($policy->id, $policy->limitations === '*' ? array() : $policy->limitations);
    }

    /**
     * Removes a policy from a role.
     *
     * @param mixed $policyId
     * @param mixed $roleId
     */
    public function deletePolicy($policyId, $roleId)
    {
        // Each policy can only be associated to exactly one role. Thus it is
        // sufficient to use the policyId for identification and just remove
        // the policy completely.
        $this->roleGateway->removePolicy($policyId);
    }

    /**
     * Returns the user policies associated with the user (including inherited policies from user groups).
     *
     * @param mixed $userId
     *
     * @return \eZ\Publish\SPI\Persistence\User\Policy[]
     */
    public function loadPoliciesByUserId($userId)
    {
        $data = $this->roleGateway->loadPoliciesByUserId($userId);

        $policies = $this->mapper->mapPolicies($data);

        foreach ($policies as $policy) {
            $this->limitationConverter->toSPI($policy);
        }

        return $policies;
    }

    /**
     * Assigns role to a user or user group with given limitations.
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
     */
    public function assignRole($contentId, $roleId, array $limitation = null)
    {
        $limitation = $limitation ?: array('' => array(''));
        $this->userGateway->assignRole($contentId, $roleId, $limitation);
    }

    /**
     * Un-assign a role.
     *
     * @param mixed $contentId The user or user group Id to un-assign the role from.
     * @param mixed $roleId
     */
    public function unassignRole($contentId, $roleId)
    {
        $this->userGateway->removeRole($contentId, $roleId);
    }

    /**
     * Un-assign a role by assignment ID.
     *
     * @param mixed $roleAssignmentId The assignment ID.
     */
    public function removeRoleAssignment($roleAssignmentId)
    {
        $this->userGateway->removeRoleAssignmentById($roleAssignmentId);
    }

    /**
     * Loads role assignment for specified assignment ID.
     *
     * @param mixed $roleAssignmentId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If role assignment is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleAssignment
     */
    public function loadRoleAssignment($roleAssignmentId)
    {
        $data = $this->roleGateway->loadRoleAssignment($roleAssignmentId);

        if (empty($data)) {
            throw new NotFound('roleAssignment', $roleAssignmentId);
        }

        return $this->mapper->mapRoleAssignments($data)[0];
    }

    /**
     * Loads roles assignments Role.
     *
     * Role Assignments with same roleId and limitationIdentifier will be merged together into one.
     *
     * @param mixed $roleId
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleAssignment[]
     */
    public function loadRoleAssignmentsByRoleId($roleId)
    {
        $data = $this->roleGateway->loadRoleAssignmentsByRoleId($roleId);

        if (empty($data)) {
            return array();
        }

        return $this->mapper->mapRoleAssignments($data);
    }

    /**
     * Loads roles assignments to a user/group.
     *
     * Role Assignments with same roleId and limitationIdentifier will be merged together into one.
     *
     * @param mixed $groupId In legacy storage engine this is the content object id roles are assigned to in ezuser_role.
     *                      By the nature of legacy this can currently also be used to get by $userId.
     * @param bool $inherit If true also return inherited role assignments from user groups.
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleAssignment[]
     */
    public function loadRoleAssignmentsByGroupId($groupId, $inherit = false)
    {
        $data = $this->roleGateway->loadRoleAssignmentsByGroupId($groupId, $inherit);

        if (empty($data)) {
            return array();
        }

        return $this->mapper->mapRoleAssignments($data);
    }
}
