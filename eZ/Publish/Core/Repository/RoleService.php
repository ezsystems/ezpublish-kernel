<?php
namespace eZ\Publish\Core\Repository;

use eZ\Publish\Core\Repository\Values\User\PolicyUpdateStruct,
    eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct as APIPolicyUpdateStruct,
    eZ\Publish\Core\Repository\Values\User\Policy,
    eZ\Publish\API\Repository\Values\User\Policy as APIPolicy,
    eZ\Publish\API\Repository\Values\User\RoleUpdateStruct,
    eZ\Publish\Core\Repository\Values\User\PolicyCreateStruct,
    eZ\Publish\API\Repository\Values\User\PolicyCreateStruct as APIPolicyCreateStruct,
    eZ\Publish\Core\Repository\Values\User\Role,
    eZ\Publish\API\Repository\Values\User\Role as APIRole,
    eZ\Publish\Core\Repository\Values\User\RoleCreateStruct,
    eZ\Publish\API\Repository\Values\User\RoleCreateStruct as APIRoleCreateStruct,
    eZ\Publish\Core\Repository\Values\User\UserRoleAssignment,
    eZ\Publish\Core\Repository\Values\User\UserGroupRoleAssignment,
    eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation,
    eZ\Publish\API\Repository\Values\User\User,
    eZ\Publish\API\Repository\Values\User\UserGroup,
    eZ\Publish\API\Repository\Values\User\Limitation,

    eZ\Publish\SPI\Persistence\User\Policy as SPIPolicy,
    eZ\Publish\SPI\Persistence\User\Role as SPIRole,
    eZ\Publish\SPI\Persistence\User\RoleUpdateStruct as SPIRoleUpdateStruct,

    eZ\Publish\API\Repository\RoleService as RoleServiceInterface,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\Handler,

    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * This service provides methods for managing Roles and Policies
 *
 * @todo add get roles for user including limitations
 *
 * @package eZ\Publish\Core\Repository
 */
class RoleService implements RoleServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository  $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $handler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
        $this->settings = $settings;
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
        if ( !is_string( $roleCreateStruct->identifier ) || empty( $roleCreateStruct->identifier ) )
            throw new InvalidArgumentValue( "identifier", $roleCreateStruct->identifier, "RoleCreateStruct" );

        if ( !is_string( $roleCreateStruct->mainLanguageCode ) || empty( $roleCreateStruct->mainLanguageCode ) )
            throw new InvalidArgumentValue( "mainLanguageCode", $roleCreateStruct->mainLanguageCode, "RoleCreateStruct" );

        if ( !is_array( $roleCreateStruct->names ) || empty( $roleCreateStruct->names ) )
            throw new InvalidArgumentValue( "names", $roleCreateStruct->names, "RoleCreateStruct" );

        if ( !is_array( $roleCreateStruct->descriptions ) || empty( $roleCreateStruct->descriptions ) )
            throw new InvalidArgumentValue( "descriptions", $roleCreateStruct->descriptions, "RoleCreateStruct" );

        try
        {
            $existingRole = $this->loadRoleByIdentifier( $roleCreateStruct->identifier );
            if ( $existingRole !== null )
                throw new InvalidArgumentException( "roleCreateStruct", "role with specified identifier already exists" );
        }
        catch ( NotFoundException $e ) {}

        $spiRole = $this->buildPersistenceRoleObject( $roleCreateStruct );
        $createdRole = $this->persistenceHandler->userHandler()->createRole( $spiRole );

        return $this->buildDomainRoleObject( $createdRole );
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
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( $roleUpdateStruct->identifier !== null && !is_string( $roleUpdateStruct->identifier ) )
            throw new InvalidArgumentValue( "identifier", $roleUpdateStruct->identifier, "RoleUpdateStruct" );

        if ( $roleUpdateStruct->mainLanguageCode !== null && !is_string( $roleUpdateStruct->mainLanguageCode ) )
            throw new InvalidArgumentValue( "mainLanguageCode", $roleUpdateStruct->mainLanguageCode, "RoleUpdateStruct" );

        if ( $roleUpdateStruct->names !== null && !is_array( $roleUpdateStruct->names ) )
            throw new InvalidArgumentValue( "names", $roleUpdateStruct->names, "RoleUpdateStruct" );

        if ( $roleUpdateStruct->descriptions !== null && !is_array( $roleUpdateStruct->descriptions ) )
            throw new InvalidArgumentValue( "descriptions", $roleUpdateStruct->descriptions, "RoleUpdateStruct" );

        if ( $roleUpdateStruct->identifier !== null )
        {
            try
            {
                $existingRole = $this->loadRoleByIdentifier( $roleUpdateStruct->identifier );
                if ( $existingRole !== null )
                    throw new InvalidArgumentException( "roleUpdateStruct", "role with specified identifier already exists" );
            }
            catch ( NotFoundException $e ) {}
        }

        $loadedRole = $this->loadRole( $role->id );

        $this->persistenceHandler->userHandler()->updateRole(
            new SPIRoleUpdateStruct(
                array(
                    'id'          => $loadedRole->id,
                    'identifier'  => $roleUpdateStruct->identifier ?: $role->identifier,
                    'name'        => $roleUpdateStruct->names ?: $role->getNames(),
                    'description' => $roleUpdateStruct->descriptions ?: $role->getDescriptions()
                )
            )
        );

        return $this->loadRole( $loadedRole->id );
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
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( !is_string( $policyCreateStruct->module ) || empty( $policyCreateStruct->module ) )
            throw new InvalidArgumentValue( "module", $policyCreateStruct->module, "PolicyCreateStruct" );

        if ( !is_string( $policyCreateStruct->function ) || empty( $policyCreateStruct->function ) )
            throw new InvalidArgumentValue( "function", $policyCreateStruct->function, "PolicyCreateStruct" );

        if ( $policyCreateStruct->module === '*' && $policyCreateStruct->function !== '*' )
            throw new InvalidArgumentValue( "module", $policyCreateStruct->module, "PolicyCreateStruct" );

        $loadedRole = $this->loadRole( $role->id );

        $spiPolicy = $this->buildPersistencePolicyObject(
            $policyCreateStruct->module,
            $policyCreateStruct->function,
            $policyCreateStruct->getLimitations()
        );

        $this->persistenceHandler->userHandler()->addPolicy( $loadedRole->id, $spiPolicy );

        return $this->loadRole( $loadedRole->id );
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
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( !is_numeric( $policy->id ) )
            throw new InvalidArgumentValue( "id", $policy->id, "Policy" );

        $loadedRole = $this->loadRole( $role->id );

        $this->persistenceHandler->userHandler()->removePolicy( $loadedRole->id, $policy->id );

        return $this->loadRole( $loadedRole->id );
    }

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitations are replaced by the ones in $roleUpdateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function updatePolicy( APIPolicy $policy, APIPolicyUpdateStruct $policyUpdateStruct )
    {
        if ( !is_numeric( $policy->id ) )
            throw new InvalidArgumentValue( "id", $policy->id, "Policy" );

        if ( !is_numeric( $policy->roleId ) )
            throw new InvalidArgumentValue( "roleId", $policy->roleId, "Policy" );

        if ( !is_string( $policy->module ) )
            throw new InvalidArgumentValue( "module", $policy->module, "Policy" );

        if ( !is_string( $policy->function ) )
            throw new InvalidArgumentValue( "function", $policy->function, "Policy" );

        $spiPolicy = $this->buildPersistencePolicyObject(
            $policy->module,
            $policy->function,
            $policyUpdateStruct->getLimitations()
        );

        $spiPolicy->id = $policy->id;
        $spiPolicy->roleId = $policy->roleId;

        $this->persistenceHandler->userHandler()->updatePolicy( $spiPolicy );

        return $this->buildDomainPolicyObject( $spiPolicy );
    }

    /**
     * loads a role for the given id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a role with the given id was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
    */
    public function loadRole( $id )
    {
        if ( !is_numeric( $id ) )
            throw new InvalidArgumentValue( "id", $id );

        $spiRole = $this->persistenceHandler->userHandler()->loadRole( $id );
        return $this->buildDomainRoleObject( $spiRole );
    }

    /**
     * loads a role for the given identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a role with the given name was not found
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRoleByIdentifier( $identifier )
    {
        if ( !is_string( $identifier ) )
            throw new InvalidArgumentValue( "identifier", $identifier );

        $spiRole = $this->persistenceHandler->userHandler()->loadRoleByIdentifier( $identifier );
        return $this->buildDomainRoleObject( $spiRole );
    }

    /**
     * loads all roles
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the roles
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role[]
     */
    public function loadRoles()
    {
        $spiRoles = $this->persistenceHandler->userHandler()->loadRoles();

        $roles = array();
        foreach ( $spiRoles as $spiRole )
        {
            $roles[] = $this->buildDomainRoleObject( $spiRole );
        }

        return $roles;
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
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        $loadedRole = $this->loadRole( $role->id );

        $this->persistenceHandler->userHandler()->deleteRole( $loadedRole->id );
    }

    /**
     * loads all policies from roles which are assigned to a user or to user groups to which the user belongs
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given id was not found
     *
     * @param $userId
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    public function loadPoliciesByUserId( $userId )
    {
        if ( !is_numeric( $userId ) )
            throw new InvalidArgumentValue( "userId", $userId );

        $loadedUser = $this->repository->getUserService()->loadUser( $userId );

        $spiPolicies = $this->persistenceHandler->userHandler()->loadPoliciesByUserId( $loadedUser->id );

        $policies = array();
        foreach ( $spiPolicies as $spiPolicy )
        {
            $policies[] = $this->buildDomainPolicyObject( $spiPolicy );
        }

        return $policies;
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
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( !is_numeric( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        $loadedRole = $this->loadRole( $role->id );
        $loadedUserGroup = $this->repository->getUserService()->loadUserGroup( $userGroup->id );

        $spiRoleLimitation = null;
        if ( $roleLimitation !== null )
        {
            $limitationIdentifier = $roleLimitation->getIdentifier();
            if ( $limitationIdentifier !== Limitation::SUBTREE && $limitationIdentifier !== Limitation::SECTION )
                throw new InvalidArgumentValue( "identifier", $limitationIdentifier, "RoleLimitation" );

            $spiRoleLimitation = array( $limitationIdentifier => $roleLimitation->limitationValues );
        }

        $this->persistenceHandler->userHandler()->assignRole(
            $loadedUserGroup->id,
            $loadedRole->id,
            $spiRoleLimitation
        );
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
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( !is_numeric( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        $spiRole = $this->persistenceHandler->userHandler()->loadRole( $role->id );

        if ( !in_array( $userGroup->id, $spiRole->groupIds ) )
            throw new InvalidArgumentException( "userGroup", "role is not assigned to the user group" );

        $this->persistenceHandler->userHandler()->unAssignRole( $userGroup->id, $role->id );
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
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( !is_numeric( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        $loadedRole = $this->loadRole( $role->id );
        $loadedUser = $this->repository->getUserService()->loadUser( $user->id );

        $spiRoleLimitation = null;
        if ( $roleLimitation !== null )
        {
            $limitationIdentifier = $roleLimitation->getIdentifier();
            if ( $limitationIdentifier !== Limitation::SUBTREE && $limitationIdentifier !== Limitation::SECTION )
                throw new InvalidArgumentValue( "identifier", $limitationIdentifier, "RoleLimitation" );

            $spiRoleLimitation = array( $limitationIdentifier => $roleLimitation->limitationValues );
        }

        $this->persistenceHandler->userHandler()->assignRole(
            $loadedUser->id,
            $loadedRole->id,
            $spiRoleLimitation
        );
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
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( !is_numeric( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        $spiRole = $this->persistenceHandler->userHandler()->loadRole( $role->id );

        if ( !in_array( $user->id, $spiRole->groupIds ) )
            throw new InvalidArgumentException( "user", "role is not assigned to the user" );

        $this->persistenceHandler->userHandler()->unAssignRole( $user->id, $role->id );
    }

    /**
     * returns the assigned user and user groups to this role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment[]
     */
    public function getRoleAssignments( APIRole $role )
    {
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        $spiRole = $this->persistenceHandler->userHandler()->loadRole( $role->id );

        $userService = $this->repository->getUserService();

        $roleAssignments = array();
        foreach ( $spiRole->groupIds as $groupId )
        {
            // $spiRole->groupIds can contain both group and user IDs, although assigning roles to
            // users is deprecated. Hence, we'll first check for groups. If that fails,
            // we'll check for users
            try
            {
                $userGroup = $userService->loadUserGroup( $groupId );
                $roleAssignments[] = new UserGroupRoleAssignment(
                    array(
                        // @todo: add limitation
                        'limitation' => null,
                        'role'       => $this->buildDomainRoleObject( $spiRole ),
                        'userGroup'  => $userGroup
                    )
                );
            }
            catch ( NotFoundException $e )
            {
                try
                {
                    $user = $userService->loadUser( $groupId );
                    $roleAssignments[] = new UserRoleAssignment(
                        array(
                            // @todo: add limitation
                            'limitation' => null,
                            'role'       => $this->buildDomainRoleObject( $spiRole ),
                            'user'       => $user
                        )
                    );
                }
                catch ( NotFoundException $e ) {}
            }
        }

        return $roleAssignments;
    }

    /**
     * returns the roles assigned to the given user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a user
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserRoleAssignment[]
     */
    public function getRoleAssignmentsForUser( User $user )
    {
        if ( !is_numeric( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        $roleAssignments = array();

        $spiRoles = $this->persistenceHandler->userHandler()->loadRolesByGroupId( $user->id );
        foreach ( $spiRoles as $spiRole )
        {
            $roleAssignments[] = new UserRoleAssignment(
                array(
                    // @todo: add limitation
                    'limitation' => null,
                    'role'       => $this->buildDomainRoleObject( $spiRole ),
                    'user'       => $user
                )
            );
        }

        return $roleAssignments;
    }

    /**
     * returns the roles assigned to the given user group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment[]
     */
    public function getRoleAssignmentsForUserGroup( UserGroup $userGroup )
    {
        if ( !is_numeric( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        $roleAssignments = array();

        $spiRoles = $this->persistenceHandler->userHandler()->loadRolesByGroupId( $userGroup->id );
        foreach ( $spiRoles as $spiRole )
        {
            $roleAssignments[] = new UserGroupRoleAssignment(
                array(
                    // @todo: add limitation
                    'limitation' => null,
                    'role'       => $this->buildDomainRoleObject( $spiRole ),
                    'userGroup'  => $userGroup
                )
            );
        }

        return $roleAssignments;
    }

    /**
     * instantiates a role create class
     *
     * @param string $name
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
     */
    public function newRoleCreateStruct( $name )
    {
        return new RoleCreateStruct(
            array(
                'identifier'   => $name,
                'policies'     => array()
            )
        );
    }

    /**
     * instantiates a policy create class
     *
     * @param string $module
     * @param string $function
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct
     */
    public function newPolicyCreateStruct( $module, $function )
    {
        return new PolicyCreateStruct(
            array(
                'module'      => $module,
                'function'    => $function,
                'limitations' => array()
            )
        );
    }

    /**
     * instantiates a policy update class
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
     */
    public function newPolicyUpdateStruct()
    {
        return new PolicyUpdateStruct(
            array(
                'limitations' => array()
            )
        );
    }

    /**
     * instantiates a policy update class
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct
     */
    public function newRoleUpdateStruct()
    {
        return new RoleUpdateStruct();
    }

    /**
     * Maps provided SPI Role value object to API Role value object
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    protected function buildDomainRoleObject( SPIRole $role )
    {
        $rolePolicies = array();
        foreach ( $role->policies as $spiPolicy )
        {
            $rolePolicies[] = $this->buildDomainPolicyObject( $spiPolicy, $role );
        }

        return new Role(
            array(
                'id'               => $role->id,
                'identifier'       => $role->identifier,
                //@todo: add main language code
                'mainLanguageCode' => null,
                'names'            => $role->name,
                'descriptions'     => $role->description,
                'policies'         => $rolePolicies
            )
        );
    }

    /**
     * Maps provided SPI Policy value object to API Policy value object
     *
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     * @param \eZ\Publish\SPI\Persistence\User\Role|null $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    protected function buildDomainPolicyObject( SPIPolicy $policy, SPIRole $role = null )
    {
        $policyLimitations = array();
        if ( $policy->module !== '*' && $policy->function !== '*' && is_array( $policy->limitations ) )
        {
            foreach ( $policy->limitations as $limitationIdentifier => $limitationValues )
            {
                $limitation = $this->getLimitationFromIdentifier( $limitationIdentifier );
                $limitation->limitationValues = $limitationValues;
                $policyLimitations[] = $limitation;
            }
        }

        return new Policy(
            array(
                'id'          => $policy->id,
                'roleId'      => $role ? $role->id : $policy->roleId,
                'module'      => $policy->module,
                'function'    => $policy->function,
                'limitations' => $policyLimitations
            )
        );
    }

    /**
     * Returns the correct implementation of API Limitation value object
     * based on provided identifier
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    protected function getLimitationFromIdentifier( $identifier )
    {
        switch ( $identifier )
        {
            case Limitation::CONTENTTYPE :
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();
                break;

            case Limitation::LANGUAGE :
                return new \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation();
                break;

            case Limitation::LOCATION :
                return new \eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation();
                break;

            case Limitation::OWNER :
                return new \eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation();
                break;

            case Limitation::PARENTOWNER :
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation();
                break;

            case Limitation::PARENTCONTENTTYPE :
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation();
                break;

            case Limitation::PARENTDEPTH :
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation();
                break;

            case Limitation::SECTION :
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation();
                break;

            case Limitation::SITEACCESS :
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SiteaccessLimitation();
                break;

            case Limitation::STATE :
                return new \eZ\Publish\API\Repository\Values\User\Limitation\StateLimitation();
                break;

            case Limitation::SUBTREE :
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation();
                break;

            case Limitation::USERGROUP :
                return new \eZ\Publish\API\Repository\Values\User\Limitation\UserGroupLimitation();
                break;

            case Limitation::PARENTUSERGROUP :
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation();
                break;

            default:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\CustomLimitation( $identifier );
        }
    }

    /**
     * Creates SPI Role value object from provided API role create struct
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    protected function buildPersistenceRoleObject( APIRoleCreateStruct $roleCreateStruct )
    {
        $policiesToCreate = array();
        foreach ( $roleCreateStruct->getPolicies() as $policyCreateStruct )
        {
            $policiesToCreate[] = $this->buildPersistencePolicyObject(
                $policyCreateStruct->module,
                $policyCreateStruct->function,
                $policyCreateStruct->getLimitations()
            );
        }

        return new SPIRole(
            array(
                'identifier'  => $roleCreateStruct->identifier,
                //@todo: main language code ?
                'name'        => $roleCreateStruct->names,
                'description' => $roleCreateStruct->descriptions,
                'policies'    => $policiesToCreate
            )
        );
    }

    /**
     * Creates SPI Policy value object from provided module, function and limitations
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     *
     * @return \eZ\Publish\SPI\Persistence\User\Policy
     */
    protected function buildPersistencePolicyObject( $module, $function, array $limitations )
    {
        $limitationsToCreate = '*';
        if ( $module !== '*' && $function !== '*' && !empty( $limitations ) )
        {
            $limitationsToCreate = array();
            foreach ( $limitations as $limitation )
            {
                $limitationsToCreate[$limitation->getIdentifier()] = $limitation->limitationValues;
            }
        }

        return new SPIPolicy(
            array(
                'module'      => $module,
                'function'    => $function,
                'limitations' => $limitationsToCreate
            )
        );
    }
}
