<?php
namespace  eZ\Publish\Core\Repository;

use eZ\Publish\Core\Repository\Values\User\PolicyUpdateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct as APIPolicyUpdateStruct;
use eZ\Publish\Core\Repository\Values\User\Policy;
use eZ\Publish\API\Repository\Values\User\Policy as APIPolicy;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use eZ\Publish\Core\Repository\Values\User\PolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct as APIPolicyCreateStruct;
use eZ\Publish\Core\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\Role as APIRole;
use eZ\Publish\Core\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct as APIRoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleAssignment;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\Limitation;

use eZ\Publish\SPI\Persistence\User\Policy as SPIPolicy;
use eZ\Publish\SPI\Persistence\User\Role as SPIRole;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct as SPIRoleUpdateStruct;

use eZ\Publish\API\Repository\RoleService as RoleServiceInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\SPI\Persistence\Handler;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\IllegalArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

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
    public $persistenceHandler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository  $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     */
    public function __construct( RepositoryInterface $repository, Handler $handler )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
    }

    /**
     * Creates a new Role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a role
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException if the name of the role already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function createRole( APIRoleCreateStruct $roleCreateStruct )
    {
        if ( empty( $roleCreateStruct->name ) )
            throw new InvalidArgumentValue( "name", $roleCreateStruct->name, "RoleCreateStruct" );

        try
        {
            $existingRole = $this->loadRole( $roleCreateStruct->name );
            if ( $existingRole !== null )
                throw new IllegalArgumentException( "name", $roleCreateStruct->name );
        }
        catch ( NotFoundException $e ) {}

        $spiRole = $this->buildPersistanceRoleObject( $roleCreateStruct );
        $createdRole = $this->persistenceHandler->userHandler()->createRole( $spiRole );

        return $this->buildDomainRoleObject( $createdRole );
    }

    /**
     * Updates the name and (5.x) description of the role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a role
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException if the name of the role already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct $roleUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function updateRole( APIRole $role, RoleUpdateStruct $roleUpdateStruct )
    {
        if ( empty( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( !empty( $roleUpdateStruct->name ) )
        {
            try
            {
                $existingRole = $this->loadRole( $roleUpdateStruct->name );
                if ( $existingRole !== null )
                    throw new IllegalArgumentException( "name", $roleUpdateStruct->name );
            }
            catch ( NotFoundException $e ) {}
        }

        $spiRole = $this->persistenceHandler->userHandler()->loadRole( $role->id );

        $this->persistenceHandler->userHandler()->updateRole( new SPIRoleUpdateStruct( array(
            'id'          => $role->id,
            'name'        => !empty( $roleUpdateStruct->name ) ? $roleUpdateStruct->name : $spiRole->name,
            //'description' => !empty( $roleUpdateStruct->description ) ? $roleUpdateStruct->description : $spiRole->description,
        ) ) );

        $spiRole = $this->persistenceHandler->userHandler()->loadRole( $role->id );
        return $this->buildDomainRoleObject( $spiRole );
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
        if ( empty( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( empty( $policyCreateStruct->module ) )
            throw new InvalidArgumentValue( "module", $policyCreateStruct->module, "PolicyCreateStruct" );

        if ( empty( $policyCreateStruct->function ) )
            throw new InvalidArgumentValue( "function", $policyCreateStruct->function, "PolicyCreateStruct" );

        if ( $policyCreateStruct->module === '*' && $policyCreateStruct->function !== '*' )
            throw new InvalidArgumentValue( "module", $policyCreateStruct->module, "PolicyCreateStruct" );

        // load role to check existence
        $this->persistenceHandler->userHandler()->loadRole( $role->id );

        $spiPolicy = $this->buildPersistancePolicyObject( $policyCreateStruct->module,
                                                          $policyCreateStruct->function,
                                                          $policyCreateStruct->limitations );

        $this->persistenceHandler->userHandler()->addPolicy( $role->id, $spiPolicy );

        $spiRole = $this->persistenceHandler->userHandler()->loadRole( $role->id );
        return $this->buildDomainRoleObject( $spiRole );
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
        if ( empty( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( empty( $policy->id ) )
            throw new InvalidArgumentValue( "id", $policy->id, "Policy" );

        // load role to check existence
        $this->persistenceHandler->userHandler()->loadRole( $role->id );

        $this->persistenceHandler->userHandler()->removePolicy( $role->id, $policy->id );

        $spiRole = $this->persistenceHandler->userHandler()->loadRole( $role->id );
        return $this->buildDomainRoleObject( $spiRole );
    }

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitaions are replaced by the ones in $roleUpdateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function updatePolicy( APIPolicy $policy, APIPolicyUpdateStruct $policyUpdateStruct ){}

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
    public function loadRole( $name ){}

    /**
     * loads all roles
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the roles
     *
     * @return array an array of {@link \eZ\Publish\API\Repository\Values\User\Role}
     */
    public function loadRoles(){}

    /**
     * deletes the given role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     */
    public function deleteRole( APIRole $role )
    {
        if ( empty( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        // load role to check existence
        $this->persistenceHandler->userHandler()->loadRole( $role->id );

        $this->persistenceHandler->userHandler()->deleteRole( $role->id );
    }

    /**
     * loads all policies from roles which are assigned to a user or to user groups to which the user belongs
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given id was not found
     *
     * @param $userId
     *
     * @return array an array of {@link Policy}
     */
    public function loadPoliciesByUserId( $userId )
    {
        if ( empty( $userId ) )
            throw new InvalidArgumentValue( "userId", $userId );

        $spiPolicies = $this->persistenceHandler->userHandler()->loadPoliciesByUserId( $userId );

        $policies = array();
        if ( is_array( $spiPolicies ) && !empty( $spiPolicies ) )
        {
            foreach ( $spiPolicies as $spiPolicy )
            {
                $policies[] = $this->buildDomainPolicyObject( $spiPolicy );
            }
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
    public function assignRoleToUserGroup( APIRole $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null ){}

    /**
     * removes a role from the given user group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  If the role is not assigned to the given user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     */
    public function unassignRoleFromUserGroup( APIRole $role, UserGroup $userGroup ){}

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
    public function assignRoleToUser( APIRole $role, User $user, RoleLimitation $roleLimitation = null ){}

    /**
     * removes a role from the given user.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the role is not assigned to the user
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     */
    public function unassignRoleFromUser( APIRole $role, User $user ){}

    /**
     * returns the assigned user and user groups to this role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return array an array of {@link RoleAssignment}
     */
    public function getRoleAssignments( APIRole $role ){}

    /**
     * returns the roles assigned to the given user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a user
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return array an array of {@link UserRoleAssignment}
     */
    public function getRoleAssignmentsForUser( User $user ){}

    /**
     * returns the roles assigned to the given user group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @return array an array of {@link UserGroupRoleAssignment}
     */
    public function getRoleAssignmentsForUserGroup( UserGroup $userGroup ){}

    /**
     * instantiates a role create class
     *
     * @param string $name
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
     */
    public function newRoleCreateStruct( $name )
    {
        return new RoleCreateStruct( array(
            'name'        => $name,
            'description' => '',
            'policies'    => array()
        ) );
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
        return new PolicyCreateStruct( array(
            'module'      => $module,
            'function'    => $function,
            'limitations' => array()
        ) );
    }

    /**
     * instantiates a policy update class
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
     */
    public function newPolicyUpdateStruct()
    {
        return new PolicyUpdateStruct( array(
            'limitations' => array()
        ) );
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
            $rolePolicies[] = $this->buildDomainPolicyObject( $spiPolicy );
        }

        return new Role( array(
            'id'          => $role->id,
            'name'        => $role->name,
            // 'description' => $role->description,
            'policies'    => $rolePolicies
        ) );
    }

    /**
     * Maps provided SPI Policy value object to API Policy value object
     *
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    protected function buildDomainPolicyObject( SPIPolicy $policy )
    {
        $policyLimitations = '*';
        if ( $policy->module !== '*' && $policy->function !== '*'
             && is_array( $policy->limitations ) && !empty( $policy->limitations ) )
        {
            $policyLimitations = array();
            foreach ( $policy->limitations as $limitationIdentifier => $limitationValues )
            {
                $limitation = $this->getLimitationFromIdentifier( $limitationIdentifier );
                $limitation->limitationValues = $limitationValues;
                $policyLimitations[] = $limitation;
            }
        }

        return new Policy( array(
            'id'          => $policy->id,
            'roleId'      => $policy->roleId,
            'module'      => $policy->module,
            'function'    => $policy->function,
            'limitations' => $policyLimitations
        ) );
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
        switch( $identifier )
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
    protected function buildPersistanceRoleObject( APIRoleCreateStruct $roleCreateStruct )
    {
        $policiesToCreate = array();
        if ( !empty( $roleCreateStruct->policies ) )
        {
            foreach ( $roleCreateStruct->policies as $policy )
            {
                /* @var $policy APIPolicy */
                $policiesToCreate[] = $this->buildPersistancePolicyObject( $policy->module,
                                                                           $policy->function,
                                                                           $policy->getLimitations() );
            }
        }

        return new SPIRole( array(
            'name'     => $roleCreateStruct->name,
            'policies' => $policiesToCreate
        ) );
    }

    /**
     * Creates SPI Policy value object from provided module, function and limitations
     *
     * @param string $module
     * @param string $function
     * @param array $limitations
     *
     * @return \eZ\Publish\SPI\Persistence\User\Policy
     */
    protected function buildPersistancePolicyObject( $module, $function, array $limitations )
    {
        $limitationsToCreate = '*';
        if ( $module !== '*' && $function !== '*' && is_array( $limitations ) && !empty( $limitations ) )
        {
            $limitationsToCreate = array();
            foreach ( $limitations as $limitation )
            {
                /* @var $limitation Limitation */
                $limitationsToCreate[$limitation->getIdentifier()] = $limitation->limitationValues;
            }
        }

        return new SPIPolicy( array(
            'module'      => $module,
            'function'    => $function,
            'limitations' => $limitationsToCreate
        ) );
    }
}
