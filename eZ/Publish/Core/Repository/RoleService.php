<?php

/**
 * File containing the eZ\Publish\Core\Repository\RoleService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\RoleService as RoleServiceInterface;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\API\Repository\Values\User\Policy as APIPolicy;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct as APIPolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyDraft;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct as APIPolicyUpdateStruct;
use eZ\Publish\API\Repository\Values\User\Role as APIRole;
use eZ\Publish\API\Repository\Values\User\RoleAssignment;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct as APIRoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleDraft as APIRoleDraft;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\LimitationValidationException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\NotFound\LimitationNotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Repository\Values\User\Policy;
use eZ\Publish\Core\Repository\Values\User\PolicyCreateStruct;
use eZ\Publish\Core\Repository\Values\User\PolicyUpdateStruct;
use eZ\Publish\Core\Repository\Values\User\Role;
use eZ\Publish\Core\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\Core\Repository\Values\User\RoleDraft;
use eZ\Publish\SPI\Persistence\User\Handler;
use eZ\Publish\SPI\Persistence\User\Role as SPIRole;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct as SPIRoleUpdateStruct;
use Exception;

/**
 * This service provides methods for managing Roles and Policies.
 */
class RoleService implements RoleServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\User\Handler
     */
    protected $userHandler;

    /**
     * @var \eZ\Publish\Core\Repository\Helper\LimitationService
     */
    protected $limitationService;

    /**
     * @var \eZ\Publish\Core\Repository\Helper\RoleDomainMapper
     */
    protected $roleDomainMapper;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\User\Handler $userHandler
     * @param \eZ\Publish\Core\Repository\Helper\LimitationService $limitationService
     * @param \eZ\Publish\Core\Repository\Helper\RoleDomainMapper $roleDomainMapper
     * @param array $settings
     */
    public function __construct(
        RepositoryInterface $repository,
        Handler $userHandler,
        Helper\LimitationService $limitationService,
        Helper\RoleDomainMapper $roleDomainMapper,
        array $settings = array()
    ) {
        $this->repository = $repository;
        $this->userHandler = $userHandler;
        $this->limitationService = $limitationService;
        $this->roleDomainMapper = $roleDomainMapper;
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + array(
            'policyMap' => array(
                'content' => array(
                    'read' => array('Class' => true, 'Section' => true, 'Owner' => true, 'Group' => true, 'Node' => true, 'Subtree' => true, 'State' => true),
                    'diff' => array('Class' => true, 'Section' => true, 'Owner' => true, 'Node' => true, 'Subtree' => true),
                    'view_embed' => array('Class' => true, 'Section' => true, 'Owner' => true, 'Node' => true, 'Subtree' => true),
                    'create' => array('Class' => true, 'Section' => true, 'ParentOwner' => true, 'ParentGroup' => true, 'ParentClass' => true, 'ParentDepth' => true, 'Node' => true, 'Subtree' => true, 'Language' => true),
                    'edit' => array('Class' => true, 'Section' => true, 'Owner' => true, 'Group' => true, 'Node' => true, 'Subtree' => true, 'Language' => true, 'State' => true),
                    'publish' => array('Class' => true, 'Section' => true, 'Owner' => true, 'Group' => true, 'Node' => true, 'Subtree' => true, 'Language' => true, 'State' => true),
                    'manage_locations' => array('Class' => true, 'Section' => true, 'Owner' => true, 'Subtree' => true),
                    'hide' => array('Class' => true, 'Section' => true, 'Owner' => true, 'Group' => true, 'Node' => true, 'Subtree' => true, 'Language' => true),
                    'translate' => array('Class' => true, 'Section' => true, 'Owner' => true, 'Node' => true, 'Subtree' => true, 'Language' => true),
                    'remove' => array('Class' => true, 'Section' => true, 'Owner' => true, 'Node' => true, 'Subtree' => true, 'State' => true),
                    'versionread' => array('Class' => true, 'Section' => true, 'Owner' => true, 'Status' => true, 'Node' => true, 'Subtree' => true),
                    'versionremove' => array('Class' => true, 'Section' => true, 'Owner' => true, 'Status' => true, 'Node' => true, 'Subtree' => true),
                    'pdf' => array('Class' => true, 'Section' => true, 'Owner' => true, 'Node' => true, 'Subtree' => true),
                ),
                'section' => array(
                    'assign' => array('Class' => true, 'Section' => true, 'Owner' => true, 'NewSection' => true),
                ),
                'state' => array(
                    'assign' => array('Class' => true, 'Section' => true, 'Owner' => true, 'Group' => true, 'Node' => true, 'Subtree' => true, 'State' => true, 'NewState' => true),
                ),
                'user' => array(
                    'login' => array('SiteAccess' => true),
                ),
            ),
        );
    }

    /**
     * Creates a new RoleDraft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a RoleDraft
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *         if the name of the role already exists or if limitation of the same type
     *         is repeated in the policy create struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a policy limitation in the $roleCreateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function createRole(APIRoleCreateStruct $roleCreateStruct)
    {
        if (!is_string($roleCreateStruct->identifier) || empty($roleCreateStruct->identifier)) {
            throw new InvalidArgumentValue('identifier', $roleCreateStruct->identifier, 'RoleCreateStruct');
        }

        if ($this->repository->hasAccess('role', 'create') !== true) {
            throw new UnauthorizedException('role', 'create');
        }

        try {
            $existingRole = $this->loadRoleByIdentifier($roleCreateStruct->identifier);

            throw new InvalidArgumentException(
                '$roleCreateStruct',
                "Role '{$existingRole->id}' with the specified identifier '{$roleCreateStruct->identifier}' " .
                'already exists'
            );
        } catch (APINotFoundException $e) {
            // Do nothing
        }

        $limitationValidationErrors = $this->validateRoleCreateStruct($roleCreateStruct);
        if (!empty($limitationValidationErrors)) {
            throw new LimitationValidationException($limitationValidationErrors);
        }

        $spiRoleCreateStruct = $this->roleDomainMapper->buildPersistenceRoleCreateStruct($roleCreateStruct);

        $this->repository->beginTransaction();
        try {
            $spiRole = $this->userHandler->createRole($spiRoleCreateStruct);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->roleDomainMapper->buildDomainRoleDraftObject($spiRole);
    }

    /**
     * Creates a new RoleDraft for an existing Role.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a RoleDraft
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the Role already has a RoleDraft that will need to be removed first
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function createRoleDraft(APIRole $role)
    {
        if ($this->repository->hasAccess('role', 'create') !== true) {
            throw new UnauthorizedException('role', 'create');
        }

        try {
            $this->userHandler->loadRole($role->id, Role::STATUS_DRAFT);

            // Throw exception, so platformui et al can do conflict management. Follow-up: EZP-24719
            throw new InvalidArgumentException(
                '$role',
                "Cannot create a draft for role '{$role->identifier}' because another draft exists"
            );
        } catch (APINotFoundException $e) {
            $this->repository->beginTransaction();
            try {
                $spiRole = $this->userHandler->createRoleDraft($role->id);
                $this->repository->commit();
            } catch (Exception $e) {
                $this->repository->rollback();
                throw $e;
            }
        }

        return $this->roleDomainMapper->buildDomainRoleDraftObject($spiRole);
    }

    /**
     * Loads a RoleDraft for the given id.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this RoleDraft
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a RoleDraft with the given id was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function loadRoleDraft($id)
    {
        if ($this->repository->hasAccess('role', 'read') !== true) {
            throw new UnauthorizedException('role', 'read');
        }

        $spiRole = $this->userHandler->loadRole($id, Role::STATUS_DRAFT);

        return $this->roleDomainMapper->buildDomainRoleDraftObject($spiRole);
    }

    /**
     * Loads a RoleDraft by the ID of the role it was created from.
     *
     * @param mixed $roleId ID of the role the draft was created from.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a RoleDraft with the given id was not found
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function loadRoleDraftByRoleId($roleId)
    {
        if ($this->repository->hasAccess('role', 'read') !== true) {
            throw new UnauthorizedException('role', 'read');
        }

        $spiRole = $this->userHandler->loadRoleDraftByRoleId($roleId);

        return $this->roleDomainMapper->buildDomainRoleDraftObject($spiRole);
    }

    /**
     * Updates the properties of a RoleDraft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a RoleDraft
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the identifier of the RoleDraft already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     * @param \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct $roleUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function updateRoleDraft(APIRoleDraft $roleDraft, RoleUpdateStruct $roleUpdateStruct)
    {
        if ($roleUpdateStruct->identifier !== null && !is_string($roleUpdateStruct->identifier)) {
            throw new InvalidArgumentValue('identifier', $roleUpdateStruct->identifier, 'RoleUpdateStruct');
        }

        $loadedRoleDraft = $this->loadRoleDraft($roleDraft->id);

        if ($this->repository->hasAccess('role', 'update') !== true) {
            throw new UnauthorizedException('role', 'update');
        }

        if ($roleUpdateStruct->identifier !== null) {
            try {
                /* Throw exception if:
                 * - A published role with the same identifier exists, AND
                 * - The ID of the published role does not match the original ID of the draft
                */
                $existingSPIRole = $this->userHandler->loadRoleByIdentifier($roleUpdateStruct->identifier);
                $SPIRoleDraft = $this->userHandler->loadRole($loadedRoleDraft->id, Role::STATUS_DRAFT);
                if ($existingSPIRole->id != $SPIRoleDraft->originalId) {
                    throw new InvalidArgumentException(
                        '$roleUpdateStruct',
                        "Role '{$existingSPIRole->id}' with the specified identifier '{$roleUpdateStruct->identifier}' " .
                        'already exists'
                    );
                }
            } catch (APINotFoundException $e) {
                // Do nothing
            }
        }

        $this->repository->beginTransaction();
        try {
            $this->userHandler->updateRole(
                new SPIRoleUpdateStruct(
                    array(
                        'id' => $loadedRoleDraft->id,
                        'identifier' => $roleUpdateStruct->identifier ?: $loadedRoleDraft->identifier,
                    )
                )
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadRoleDraft($loadedRoleDraft->id);
    }

    /**
     * Adds a new policy to the RoleDraft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to add  a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if limitation of the same type is repeated in policy create
     *                                                                        struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a limitation in the $policyCreateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function addPolicyByRoleDraft(APIRoleDraft $roleDraft, APIPolicyCreateStruct $policyCreateStruct)
    {
        if (!is_string($policyCreateStruct->module) || empty($policyCreateStruct->module)) {
            throw new InvalidArgumentValue('module', $policyCreateStruct->module, 'PolicyCreateStruct');
        }

        if (!is_string($policyCreateStruct->function) || empty($policyCreateStruct->function)) {
            throw new InvalidArgumentValue('function', $policyCreateStruct->function, 'PolicyCreateStruct');
        }

        if ($policyCreateStruct->module === '*' && $policyCreateStruct->function !== '*') {
            throw new InvalidArgumentValue('module', $policyCreateStruct->module, 'PolicyCreateStruct');
        }

        if ($this->repository->hasAccess('role', 'update') !== true) {
            throw new UnauthorizedException('role', 'update');
        }

        $loadedRoleDraft = $this->loadRoleDraft($roleDraft->id);

        $limitations = $policyCreateStruct->getLimitations();
        $limitationValidationErrors = $this->validatePolicy(
            $policyCreateStruct->module,
            $policyCreateStruct->function,
            $limitations
        );
        if (!empty($limitationValidationErrors)) {
            throw new LimitationValidationException($limitationValidationErrors);
        }

        $spiPolicy = $this->roleDomainMapper->buildPersistencePolicyObject(
            $policyCreateStruct->module,
            $policyCreateStruct->function,
            $limitations
        );

        $this->repository->beginTransaction();
        try {
            $this->userHandler->addPolicyByRoleDraft($loadedRoleDraft->id, $spiPolicy);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadRoleDraft($loadedRoleDraft->id);
    }

    /**
     * Removes a policy from a RoleDraft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if policy does not belong to the given RoleDraft
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     * @param PolicyDraft $policyDraft the policy to remove from the RoleDraft
     *
     * @return APIRoleDraft if the authenticated user is not allowed to remove a policy
     */
    public function removePolicyByRoleDraft(APIRoleDraft $roleDraft, PolicyDraft $policyDraft)
    {
        if ($this->repository->hasAccess('role', 'update') !== true) {
            throw new UnauthorizedException('role', 'update');
        }

        if ($policyDraft->roleId != $roleDraft->id) {
            throw new InvalidArgumentException('$policy', 'Policy does not belong to the given role');
        }

        $this->internalDeletePolicy($policyDraft);

        return $this->loadRoleDraft($roleDraft->id);
    }

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitations are replaced by the ones in $roleUpdateStruct.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if limitation of the same type is repeated in policy update
     *                                                                        struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a limitation in the $policyUpdateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     * @param \eZ\Publish\API\Repository\Values\User\PolicyDraft $policy
     * @param \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyDraft
     */
    public function updatePolicyByRoleDraft(APIRoleDraft $roleDraft, PolicyDraft $policy, APIPolicyUpdateStruct $policyUpdateStruct)
    {
        if (!is_string($policy->module)) {
            throw new InvalidArgumentValue('module', $policy->module, 'Policy');
        }

        if (!is_string($policy->function)) {
            throw new InvalidArgumentValue('function', $policy->function, 'Policy');
        }

        if ($this->repository->hasAccess('role', 'update') !== true) {
            throw new UnauthorizedException('role', 'update');
        }

        if ($policy->roleId !== $roleDraft->id) {
            throw new InvalidArgumentException('$policy', "doesn't belong to provided role draft");
        }

        $limitations = $policyUpdateStruct->getLimitations();
        $limitationValidationErrors = $this->validatePolicy(
            $policy->module,
            $policy->function,
            $limitations
        );
        if (!empty($limitationValidationErrors)) {
            throw new LimitationValidationException($limitationValidationErrors);
        }

        $spiPolicy = $this->roleDomainMapper->buildPersistencePolicyObject(
            $policy->module,
            $policy->function,
            $limitations
        );
        $spiPolicy->id = $policy->id;
        $spiPolicy->roleId = $policy->roleId;
        $spiPolicy->originalId = $policy->originalId;

        $this->repository->beginTransaction();
        try {
            $this->userHandler->updatePolicy($spiPolicy);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->roleDomainMapper->buildDomainPolicyObject($spiPolicy);
    }

    /**
     * Deletes the given RoleDraft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete this RoleDraft
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     */
    public function deleteRoleDraft(APIRoleDraft $roleDraft)
    {
        $loadedRoleDraft = $this->loadRoleDraft($roleDraft->id);

        $this->repository->beginTransaction();
        try {
            $this->userHandler->deleteRole($loadedRoleDraft->id, Role::STATUS_DRAFT);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Publishes a given RoleDraft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to publish this RoleDraft
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the role draft cannot be loaded
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the role draft has no policies
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     */
    public function publishRoleDraft(APIRoleDraft $roleDraft)
    {
        if ($this->repository->hasAccess('role', 'update') !== true) {
            throw new UnauthorizedException('role', 'update');
        }

        try {
            $loadedRoleDraft = $this->loadRoleDraft($roleDraft->id);
        } catch (APINotFoundException $e) {
            throw new BadStateException(
                '$roleDraft',
                'The role does not have a draft.',
                $e
            );
        }

        // TODO: Uncomment when role policy editing is done, see EZP-24711 & EZP-24713
        /*if (count($loadedRoleDraft->getPolicies()) === 0) {
            throw new InvalidArgumentException(
                "\$roleDraft",
                'The role draft should have at least one policy.'
            );
        }*/

        $this->repository->beginTransaction();
        try {
            $this->userHandler->publishRoleDraft($loadedRoleDraft->id);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Updates the name of the role.
     *
     * @deprecated since 6.0, use {@see updateRoleDraft}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the name of the role already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct $roleUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function updateRole(APIRole $role, RoleUpdateStruct $roleUpdateStruct)
    {
        if ($roleUpdateStruct->identifier !== null && !is_string($roleUpdateStruct->identifier)) {
            throw new InvalidArgumentValue('identifier', $roleUpdateStruct->identifier, 'RoleUpdateStruct');
        }

        $loadedRole = $this->loadRole($role->id);

        if ($this->repository->hasAccess('role', 'update') !== true) {
            throw new UnauthorizedException('role', 'update');
        }

        if ($roleUpdateStruct->identifier !== null) {
            try {
                $existingRole = $this->loadRoleByIdentifier($roleUpdateStruct->identifier);

                if ($existingRole->id != $loadedRole->id) {
                    throw new InvalidArgumentException(
                        '$roleUpdateStruct',
                        'Role with provided identifier already exists'
                    );
                }
            } catch (APINotFoundException $e) {
                // Do nothing
            }
        }

        $this->repository->beginTransaction();
        try {
            $this->userHandler->updateRole(
                new SPIRoleUpdateStruct(
                    array(
                        'id' => $loadedRole->id,
                        'identifier' => $roleUpdateStruct->identifier ?: $loadedRole->identifier,
                    )
                )
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadRole($loadedRole->id);
    }

    /**
     * Adds a new policy to the role.
     *
     * @deprecated since 6.0, use {@see addPolicyByRoleDraft}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to add  a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if limitation of the same type is repeated in policy create
     *                                                                        struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a limitation in the $policyCreateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function addPolicy(APIRole $role, APIPolicyCreateStruct $policyCreateStruct)
    {
        if (!is_string($policyCreateStruct->module) || empty($policyCreateStruct->module)) {
            throw new InvalidArgumentValue('module', $policyCreateStruct->module, 'PolicyCreateStruct');
        }

        if (!is_string($policyCreateStruct->function) || empty($policyCreateStruct->function)) {
            throw new InvalidArgumentValue('function', $policyCreateStruct->function, 'PolicyCreateStruct');
        }

        if ($policyCreateStruct->module === '*' && $policyCreateStruct->function !== '*') {
            throw new InvalidArgumentValue('module', $policyCreateStruct->module, 'PolicyCreateStruct');
        }

        if ($this->repository->hasAccess('role', 'update') !== true) {
            throw new UnauthorizedException('role', 'update');
        }

        $loadedRole = $this->loadRole($role->id);

        $limitations = $policyCreateStruct->getLimitations();
        $limitationValidationErrors = $this->validatePolicy(
            $policyCreateStruct->module,
            $policyCreateStruct->function,
            $limitations
        );
        if (!empty($limitationValidationErrors)) {
            throw new LimitationValidationException($limitationValidationErrors);
        }

        $spiPolicy = $this->roleDomainMapper->buildPersistencePolicyObject(
            $policyCreateStruct->module,
            $policyCreateStruct->function,
            $limitations
        );

        $this->repository->beginTransaction();
        try {
            $this->userHandler->addPolicy($loadedRole->id, $spiPolicy);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadRole($loadedRole->id);
    }

    /**
     * Deletes a policy.
     *
     * @deprecated since 6.0, use {@link removePolicyByRoleDraft()} instead.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy the policy to delete
     */
    public function deletePolicy(APIPolicy $policy)
    {
        if ($this->repository->hasAccess('role', 'update') !== true) {
            throw new UnauthorizedException('role', 'update');
        }

        $this->internalDeletePolicy($policy);
    }

    /**
     * Deletes a policy.
     *
     * Used by {@link removePolicy()} and {@link deletePolicy()}
     *
     * @param APIPolicy $policy
     *
     * @throws \Exception
     */
    protected function internalDeletePolicy(APIPolicy $policy)
    {
        $this->repository->beginTransaction();
        try {
            $this->userHandler->deletePolicy($policy->id, $policy->roleId);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitations are replaced by the ones in $roleUpdateStruct.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if limitation of the same type is repeated in policy update
     *                                                                        struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a limitation in the $policyUpdateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function updatePolicy(APIPolicy $policy, APIPolicyUpdateStruct $policyUpdateStruct)
    {
        if (!is_string($policy->module)) {
            throw new InvalidArgumentValue('module', $policy->module, 'Policy');
        }

        if (!is_string($policy->function)) {
            throw new InvalidArgumentValue('function', $policy->function, 'Policy');
        }

        if ($this->repository->hasAccess('role', 'update') !== true) {
            throw new UnauthorizedException('role', 'update');
        }

        $limitations = $policyUpdateStruct->getLimitations();
        $limitationValidationErrors = $this->validatePolicy(
            $policy->module,
            $policy->function,
            $limitations
        );
        if (!empty($limitationValidationErrors)) {
            throw new LimitationValidationException($limitationValidationErrors);
        }

        $spiPolicy = $this->roleDomainMapper->buildPersistencePolicyObject(
            $policy->module,
            $policy->function,
            $limitations
        );
        $spiPolicy->id = $policy->id;
        $spiPolicy->roleId = $policy->roleId;

        $this->repository->beginTransaction();
        try {
            $this->userHandler->updatePolicy($spiPolicy);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->roleDomainMapper->buildDomainPolicyObject($spiPolicy);
    }

    /**
     * Loads a role for the given id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a role with the given id was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRole($id)
    {
        if ($this->repository->hasAccess('role', 'read') !== true) {
            throw new UnauthorizedException('role', 'read');
        }

        $spiRole = $this->userHandler->loadRole($id);

        return $this->roleDomainMapper->buildDomainRoleObject($spiRole);
    }

    /**
     * Loads a role for the given identifier.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a role with the given name was not found
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRoleByIdentifier($identifier)
    {
        if (!is_string($identifier)) {
            throw new InvalidArgumentValue('identifier', $identifier);
        }

        if ($this->repository->hasAccess('role', 'read') !== true) {
            throw new UnauthorizedException('role', 'read');
        }

        $spiRole = $this->userHandler->loadRoleByIdentifier($identifier);

        return $this->roleDomainMapper->buildDomainRoleObject($spiRole);
    }

    /**
     * Loads all roles.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the roles
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role[]
     */
    public function loadRoles()
    {
        if ($this->repository->hasAccess('role', 'read') !== true) {
            throw new UnauthorizedException('role', 'read');
        }

        $spiRoles = $this->userHandler->loadRoles();

        $roles = array();
        foreach ($spiRoles as $spiRole) {
            $roles[] = $this->roleDomainMapper->buildDomainRoleObject($spiRole);
        }

        return $roles;
    }

    /**
     * Deletes the given role.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     */
    public function deleteRole(APIRole $role)
    {
        if ($this->repository->hasAccess('role', 'delete') !== true) {
            throw new UnauthorizedException('role', 'delete');
        }

        $loadedRole = $this->loadRole($role->id);

        $this->repository->beginTransaction();
        try {
            $this->userHandler->deleteRole($loadedRole->id);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Loads all policies from roles which are assigned to a user or to user groups to which the user belongs.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given id was not found
     *
     * @param mixed $userId
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    public function loadPoliciesByUserId($userId)
    {
        $spiPolicies = $this->userHandler->loadPoliciesByUserId($userId);

        $policies = array();
        foreach ($spiPolicies as $spiPolicy) {
            $policies[] = $this->roleDomainMapper->buildDomainPolicyObject($spiPolicy);
        }

        if (empty($policies)) {
            $this->userHandler->load($userId);
        }// For NotFoundException in case userId is invalid

        return $policies;
    }

    /**
     * Assigns a role to the given user group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if $roleLimitation is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If assignment already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUserGroup(APIRole $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null)
    {
        if ($this->repository->canUser('role', 'assign', $userGroup, $role) !== true) {
            throw new UnauthorizedException('role', 'assign');
        }

        if ($roleLimitation === null) {
            $limitation = null;
        } else {
            $limitationValidationErrors = $this->limitationService->validateLimitation($roleLimitation);
            if (!empty($limitationValidationErrors)) {
                throw new LimitationValidationException($limitationValidationErrors);
            }

            $limitation = array($roleLimitation->getIdentifier() => $roleLimitation->limitationValues);
        }

        // Check if objects exists
        $spiRole = $this->userHandler->loadRole($role->id);
        $loadedUserGroup = $this->repository->getUserService()->loadUserGroup($userGroup->id);

        $limitation = $this->checkAssignmentAndFilterLimitationValues($loadedUserGroup->id, $spiRole, $limitation);

        $this->repository->beginTransaction();
        try {
            $this->userHandler->assignRole(
                $loadedUserGroup->id,
                $spiRole->id,
                $limitation
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
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
    public function unassignRoleFromUserGroup(APIRole $role, UserGroup $userGroup)
    {
        if ($this->repository->canUser('role', 'assign', $userGroup, $role) !== true) {
            throw new UnauthorizedException('role', 'assign');
        }

        $spiRoleAssignments = $this->userHandler->loadRoleAssignmentsByGroupId($userGroup->id);
        $isAssigned = false;
        foreach ($spiRoleAssignments as $spiRoleAssignment) {
            if ($spiRoleAssignment->roleId === $role->id) {
                $isAssigned = true;
                break;
            }
        }

        if (!$isAssigned) {
            throw new InvalidArgumentException(
                '$userGroup',
                'Role is not assigned to the given UserGroup'
            );
        }

        $this->repository->beginTransaction();
        try {
            $this->userHandler->unassignRole($userGroup->id, $role->id);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Assigns a role to the given user.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if $roleLimitation is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If assignment already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUser(APIRole $role, User $user, RoleLimitation $roleLimitation = null)
    {
        if ($this->repository->canUser('role', 'assign', $user, $role) !== true) {
            throw new UnauthorizedException('role', 'assign');
        }

        if ($roleLimitation === null) {
            $limitation = null;
        } else {
            $limitationValidationErrors = $this->limitationService->validateLimitation($roleLimitation);
            if (!empty($limitationValidationErrors)) {
                throw new LimitationValidationException($limitationValidationErrors);
            }

            $limitation = array($roleLimitation->getIdentifier() => $roleLimitation->limitationValues);
        }

        // Check if objects exists
        $spiRole = $this->userHandler->loadRole($role->id);
        $spiUser = $this->userHandler->load($user->id);

        $limitation = $this->checkAssignmentAndFilterLimitationValues($spiUser->id, $spiRole, $limitation);

        $this->repository->beginTransaction();
        try {
            $this->userHandler->assignRole(
                $spiUser->id,
                $spiRole->id,
                $limitation
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
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
    public function unassignRoleFromUser(APIRole $role, User $user)
    {
        if ($this->repository->canUser('role', 'assign', $user, $role) !== true) {
            throw new UnauthorizedException('role', 'assign');
        }

        $spiRoleAssignments = $this->userHandler->loadRoleAssignmentsByGroupId($user->id);
        $isAssigned = false;
        foreach ($spiRoleAssignments as $spiRoleAssignment) {
            if ($spiRoleAssignment->roleId === $role->id) {
                $isAssigned = true;
                break;
            }
        }

        if (!$isAssigned) {
            throw new InvalidArgumentException(
                '$user',
                'Role is not assigned to the given User'
            );
        }

        $this->repository->beginTransaction();
        try {
            $this->userHandler->unassignRole($user->id, $role->id);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Removes the given role assignment.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role assignment
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleAssignment $roleAssignment
     */
    public function removeRoleAssignment(RoleAssignment $roleAssignment)
    {
        if ($this->repository->canUser('role', 'assign', $roleAssignment) !== true) {
            throw new UnauthorizedException('role', 'assign');
        }

        $spiRoleAssignment = $this->userHandler->loadRoleAssignment($roleAssignment->id);

        $this->repository->beginTransaction();
        try {
            $this->userHandler->removeRoleAssignment($spiRoleAssignment->id);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Loads a role assignment for the given id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the role assignment was not found
     *
     * @param mixed $roleAssignmentId
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment
     */
    public function loadRoleAssignment($roleAssignmentId)
    {
        if ($this->repository->hasAccess('role', 'read') !== true) {
            throw new UnauthorizedException('role', 'read');
        }

        $spiRoleAssignment = $this->userHandler->loadRoleAssignment($roleAssignmentId);
        $userService = $this->repository->getUserService();
        $role = $this->loadRole($spiRoleAssignment->roleId);
        $roleAssignment = null;

        // First check if the Role is assigned to a User
        // If no User is found, see if it belongs to a UserGroup
        try {
            $user = $userService->loadUser($spiRoleAssignment->contentId);
            $roleAssignment = $this->roleDomainMapper->buildDomainUserRoleAssignmentObject(
                $spiRoleAssignment,
                $user,
                $role
            );
        } catch (APINotFoundException $e) {
            try {
                $userGroup = $userService->loadUserGroup($spiRoleAssignment->contentId);
                $roleAssignment = $this->roleDomainMapper->buildDomainUserGroupRoleAssignmentObject(
                    $spiRoleAssignment,
                    $userGroup,
                    $role
                );
            } catch (APINotFoundException $e) {
                // Do nothing
            }
        }

        return $roleAssignment;
    }

    /**
     * Returns the assigned user and user groups to this role.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment[]
     */
    public function getRoleAssignments(APIRole $role)
    {
        if ($this->repository->hasAccess('role', 'read') !== true) {
            throw new UnauthorizedException('role', 'read');
        }

        $userService = $this->repository->getUserService();
        $spiRoleAssignments = $this->userHandler->loadRoleAssignmentsByRoleId($role->id);
        $roleAssignments = array();

        foreach ($spiRoleAssignments as $spiRoleAssignment) {
            // First check if the Role is assigned to a User
            // If no User is found, see if it belongs to a UserGroup
            try {
                $user = $userService->loadUser($spiRoleAssignment->contentId);
                $roleAssignments[] = $this->roleDomainMapper->buildDomainUserRoleAssignmentObject(
                    $spiRoleAssignment,
                    $user,
                    $role
                );
            } catch (APINotFoundException $e) {
                try {
                    $userGroup = $userService->loadUserGroup($spiRoleAssignment->contentId);
                    $roleAssignments[] = $this->roleDomainMapper->buildDomainUserGroupRoleAssignmentObject(
                        $spiRoleAssignment,
                        $userGroup,
                        $role
                    );
                } catch (APINotFoundException $e) {
                    // Do nothing
                }
            }
        }

        return $roleAssignments;
    }

    /**
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUser()
     */
    public function getRoleAssignmentsForUser(User $user, $inherited = false)
    {
        if ($this->repository->hasAccess('role', 'read') !== true) {
            throw new UnauthorizedException('role', 'read');
        }

        $roleAssignments = array();
        $spiRoleAssignments = $this->userHandler->loadRoleAssignmentsByGroupId($user->id, $inherited);
        foreach ($spiRoleAssignments as $spiRoleAssignment) {
            $role = $this->loadRole($spiRoleAssignment->roleId);
            if (!$inherited || $spiRoleAssignment->contentId == $user->id) {
                $roleAssignments[] = $this->roleDomainMapper->buildDomainUserRoleAssignmentObject(
                    $spiRoleAssignment,
                    $user,
                    $role
                );
            } else {
                $userGroup = $this->repository->getUserService()->loadUserGroup($spiRoleAssignment->contentId);
                $roleAssignments[] = $this->roleDomainMapper->buildDomainUserGroupRoleAssignmentObject(
                    $spiRoleAssignment,
                    $userGroup,
                    $role
                );
            }
        }

        return $roleAssignments;
    }

    /**
     * Returns the roles assigned to the given user group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment[]
     */
    public function getRoleAssignmentsForUserGroup(UserGroup $userGroup)
    {
        if ($this->repository->hasAccess('role', 'read') !== true) {
            throw new UnauthorizedException('role', 'read');
        }

        $roleAssignments = array();
        $spiRoleAssignments = $this->userHandler->loadRoleAssignmentsByGroupId($userGroup->id);
        foreach ($spiRoleAssignments as $spiRoleAssignment) {
            $role = $this->loadRole($spiRoleAssignment->roleId);
            $roleAssignments[] = $this->roleDomainMapper->buildDomainUserGroupRoleAssignmentObject(
                $spiRoleAssignment,
                $userGroup,
                $role
            );
        }

        return $roleAssignments;
    }

    /**
     * Instantiates a role create class.
     *
     * @param string $name
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
     */
    public function newRoleCreateStruct($name)
    {
        return new RoleCreateStruct(
            array(
                'identifier' => $name,
                'policies' => array(),
            )
        );
    }

    /**
     * Instantiates a policy create class.
     *
     * @param string $module
     * @param string $function
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct
     */
    public function newPolicyCreateStruct($module, $function)
    {
        return new PolicyCreateStruct(
            array(
                'module' => $module,
                'function' => $function,
                'limitations' => array(),
            )
        );
    }

    /**
     * Instantiates a policy update class.
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
     */
    public function newPolicyUpdateStruct()
    {
        return new PolicyUpdateStruct(
            array(
                'limitations' => array(),
            )
        );
    }

    /**
     * Instantiates a policy update class.
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct
     */
    public function newRoleUpdateStruct()
    {
        return new RoleUpdateStruct();
    }

    /**
     * Returns the LimitationType registered with the given identifier.
     *
     * Returns the correct implementation of API Limitation value object
     * based on provided identifier
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\Limitation\Type
     *
     * @throws \RuntimeException if there is no LimitationType with $identifier
     */
    public function getLimitationType($identifier)
    {
        return $this->limitationService->getLimitationType($identifier);
    }

    /**
     * Returns the LimitationType's assigned to a given module/function.
     *
     * Typically used for:
     *  - Internal validation limitation value use on Policies
     *  - Role admin gui for editing policy limitations incl list limitation options via valueSchema()
     *
     * @param string $module Legacy name of "controller", it's a unique identifier like "content"
     * @param string $function Legacy name of a controller "action", it's a unique within the controller like "read"
     *
     * @return \eZ\Publish\SPI\Limitation\Type[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If module/function to limitation type mapping
     *                                                                 refers to a non existing identifier.
     */
    public function getLimitationTypesByModuleFunction($module, $function)
    {
        if (empty($this->settings['policyMap'][$module][$function])) {
            return array();
        }

        $types = array();
        try {
            foreach (array_keys($this->settings['policyMap'][$module][$function]) as $identifier) {
                $types[$identifier] = $this->limitationService->getLimitationType($identifier);
            }
        } catch (LimitationNotFoundException $e) {
            throw new BadStateException(
                "{$module}/{$function}",
                "policyMap configuration is referring to non existing identifier: {$identifier}",
                $e
            );
        }

        return $types;
    }

    /**
     * Validates Policies and Limitations in Role create struct.
     *
     * @uses ::validatePolicy()
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     *
     * @return \eZ\Publish\Core\FieldType\ValidationError[][][]
     */
    protected function validateRoleCreateStruct(APIRoleCreateStruct $roleCreateStruct)
    {
        $allErrors = array();
        foreach ($roleCreateStruct->getPolicies() as $key => $policyCreateStruct) {
            $errors = $this->validatePolicy(
                $policyCreateStruct->module,
                $policyCreateStruct->function,
                $policyCreateStruct->getLimitations()
            );

            if (!empty($errors)) {
                $allErrors[$key] = $errors;
            }
        }

        return $allErrors;
    }

    /**
     * Validates Policy context: Limitations on a module and function.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If the same limitation is repeated or if
     *                                                                   limitation is not allowed on module/function
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     *
     * @return \eZ\Publish\Core\FieldType\ValidationError[][]
     */
    protected function validatePolicy($module, $function, array $limitations)
    {
        if ($module !== '*' && $function !== '*' && !empty($limitations)) {
            $limitationSet = array();
            foreach ($limitations as $limitation) {
                if (isset($limitationSet[$limitation->getIdentifier()])) {
                    throw new InvalidArgumentException(
                        'limitations',
                        "'{$limitation->getIdentifier()}' was found several times among the limitations"
                    );
                }

                if (!isset($this->settings['policyMap'][$module][$function][$limitation->getIdentifier()])) {
                    throw new InvalidArgumentException(
                        'policy',
                        "The limitation '{$limitation->getIdentifier()}' is not applicable on '{$module}/{$function}'"
                    );
                }

                $limitationSet[$limitation->getIdentifier()] = true;
            }
        }

        return $this->limitationService->validateLimitations($limitations);
    }

    /**
     * Validate that assignments not already exists and filter validations against existing.
     *
     * @param mixed $contentId
     * @param SPIRole $spiRole
     * @param array|null $limitation
     *
     * @return array[]|null Filtered version of $limitation
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If assignment already exists
     */
    protected function checkAssignmentAndFilterLimitationValues($contentId, SPIRole $spiRole, array $limitation = null)
    {
        $spiRoleAssignments = $this->userHandler->loadRoleAssignmentsByGroupId($contentId);
        foreach ($spiRoleAssignments as $spiAssignment) {
            // Ignore assignments to other roles
            if ($spiAssignment->roleId !== $spiRole->id) {
                continue;
            }

            // Throw if Role is already assigned without limitations
            if ($spiAssignment->limitationIdentifier === null) {
                throw new InvalidArgumentException(
                    '$role',
                    "Role '{$spiRole->id}' already assigned without limitations"
                );
            }

            // Ignore if we are going to assign without limitations
            if ($limitation === null) {
                continue;
            }

            // Ignore if not assigned with same limitation identifier
            if (!isset($limitation[$spiAssignment->limitationIdentifier])) {
                continue;
            }

            // Throw if Role is already assigned with all the same limitations
            $newValues = array_diff($limitation[$spiAssignment->limitationIdentifier], $spiAssignment->values);
            if (empty($newValues)) {
                throw new InvalidArgumentException(
                    '$role',
                    "Role '{$spiRole->id}' already assigned with same '{$spiAssignment->limitationIdentifier}' value"
                );
            }

            // Continue using the filtered list of limitations
            $limitation[$spiAssignment->limitationIdentifier] = $newValues;
        }

        return $limitation;
    }
}
