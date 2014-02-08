<?php
/**
 * File containing the PermissionsService class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Permission;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use RuntimeException;

/**
 * Internal Service for permission
 *
 * @package eZ\Publish\Core\Repository\Permission
 */
class PermissionsService
{
    /**
     * Currently logged in user object for permission purposes
     *
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    protected $currentUser;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $roleService;

    /**
     * @var int
     */
    protected $anonymousUserID;

    /**
     * Constructor
     *
     * @param RepositoryInterface $repository A Repository which does not check permissions
     * @param int $anonymousUserID
     */
    public function __construct( RepositoryInterface $repository, $anonymousUserID = 10 )
    {
        $this->repository = $repository;
        $this->anonymousUserID = $anonymousUserID;
    }

    /**
     * Get current user
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getCurrentUser()
    {
        if ( !$this->currentUser instanceof User )
        {
            $this->currentUser = $this->repository->getUserService()->loadUser( $this->anonymousUserID );
        }

        return $this->currentUser;
    }

    /**
     * Sets the current user to the given $user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return void
     */
    public function setCurrentUser( User $user )
    {
        if ( !$user->id )
            throw new InvalidArgumentValue( '$user->id', $user->id );

        $this->currentUser = $user;
    }

    /**
     * Check if user has access to a given module / function
     *
     * Low level function, use canUser instead if you have objects to check against.
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return boolean|array Bool if user has full or no access, array if limitations if not
     */
    public function hasAccess( $module, $function, User $user = null )
    {
        if ( $user === null )
            $user = $this->getCurrentUser();

        // Uses SPI to avoid triggering permission checks in Role/User service
        $permissionSets = array();
        $roleService = $this->repository->getRoleService();
        $roleAssignments = $roleService->getRoleAssignmentsForUser( $user, true );
        foreach ( $roleAssignments as $roleAssignment )
        {
            $permissionSet = array( 'limitation' => null, 'policies' => array() );
            $roleAssignmentLimitation = $roleAssignment->getRoleLimitation();
            foreach ( $roleAssignment->getRole()->getPolicies() as $policy )
            {
                if ( $policy->module === '*' && $roleAssignmentLimitation === null )
                    return true;

                if ( $policy->module !== $module && $policy->module !== '*' )
                    continue;

                if ( $policy->function === '*' && $roleAssignmentLimitation === null )
                    return true;

                if ( $policy->function !== $function && $policy->function !== '*' )
                    continue;

                if ( empty( $policy->limitations ) && $roleAssignmentLimitation === null )
                    return true;

                $permissionSet['policies'][] = $policy;
            }

            if ( !empty( $permissionSet['policies'] ) )
            {
                if ( $roleAssignmentLimitation !== null )
                    $permissionSet['limitation'] = $roleService
                        ->getLimitationType( $roleAssignmentLimitation->getIdentifier() )
                        ->buildValue( $roleAssignmentLimitation->limitationValues );

                $permissionSets[] = $permissionSet;
            }
        }

        if ( !empty( $permissionSets ) )
            return $permissionSets;

        return false;// No policies matching $module and $function, or they contained limitations
    }

    /**
     * Check if user has access to a given action on a given value object
     *
     * Indicates if the current user is allowed to perform an action given by the function on the given
     * objects.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If any of the arguments are invalid
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If value of the LimitationValue is unsupported
     *
     * @param string $module The module, aka controller identifier to check permissions on
     * @param string $function The function, aka the controller action to check permissions on
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object The object to check if the user has access to
     * @param mixed $targets The location, parent or "assignment" value object, or an array of the same
     *
     * @return boolean
     */
    public function canUser( $module, $function, ValueObject $object, $targets = null )
    {
        $permissionSets = $this->hasAccess( $module, $function );
        if ( $permissionSets === false || $permissionSets === true )
        {
            return $permissionSets;
        }

        if ( $targets instanceof ValueObject )
        {
            $targets = array( $targets );
        }
        else if ( $targets !== null && !is_array( $targets ) )
        {
            throw new InvalidArgumentType(
                "\$targets",
                "null|\\eZ\\Publish\\API\\Repository\\Values\\ValueObject|\\eZ\\Publish\\API\\Repository\\Values\\ValueObject[]",
                $targets
            );
        }

        $roleService = $this->repository->getRoleService();
        $currentUser = $this->getCurrentUser();
        foreach ( $permissionSets as $permissionSet )
        {
            /**
             * @var \eZ\Publish\API\Repository\Values\User\Limitation[] $permissionSet
             */
            if ( $permissionSet['limitation'] instanceof Limitation )
            {
                $type = $roleService->getLimitationType( $permissionSet['limitation']->getIdentifier() );
                if ( !$type->evaluate( $permissionSet['limitation'], $currentUser, $object, $targets ) )
                    continue;// Continue to next policy set, all limitations must pass
            }

            /**
             * @var \eZ\Publish\API\Repository\Values\User\Policy $policy
             */
            foreach ( $permissionSet['policies'] as $policy )
            {
                $limitations = $policy->getLimitations();
                if ( $limitations === '*' )
                    return true;

                $limitationsPass = true;
                foreach ( $limitations as $limitation )
                {
                    $type = $roleService->getLimitationType( $limitation->getIdentifier() );
                    if ( !$type->evaluate( $limitation, $currentUser, $object, $targets ) )
                    {
                        $limitationsPass = false;
                        break;// Break to next policy, all limitations must pass
                    }
                }
                if ( $limitationsPass )
                    return true;
            }
        }
        return false;// None of the limitation sets wanted to let you in, sorry!
    }

    /**
     * Adds content, read Permission criteria if needed and return false if no access at all
     *
     * @access private Temporarily made accessible until Location service stops using searchHandler()
     * @uses getPermissionsCriterion()
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $module
     * @param string $function
     *
     * @return boolean|\eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public function addPermissionsCriterion( Criterion &$criterion, $module = 'content', $function = 'read' )
    {
        $permissionCriterion = $this->getPermissionsCriterion( $module, $function );

        // Signal full (true) or no access(false)
        if ( $permissionCriterion === true || $permissionCriterion === false )
        {
            return $permissionCriterion;
        }

        // Merge with original $criterion
        if ( $criterion instanceof LogicalAnd )
        {
            $criterion->criteria[] = $permissionCriterion;
        }
        else
        {
            $criterion = new LogicalAnd(
                array(
                    $criterion,
                    $permissionCriterion
                )
            );
        }
        return $criterion;
    }

    /**
     * Get content-read Permission criteria if needed and return false if no access at all
     *
     * @uses \eZ\Publish\API\Repository::hasAccess()
     * @throws \RuntimeException If empty array of limitations are provided from hasAccess()
     *
     * @param string $module
     * @param string $function
     *
     * @return boolean|\eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public function getPermissionsCriterion( $module = 'content', $function = 'read' )
    {
        $permissionSets = $this->hasAccess( $module, $function );

        // Signal full (true) or no access(false)
        if ( $permissionSets === false || $permissionSets === true )
        {
            return $permissionSets;
        }

        if ( empty( $permissionSets ) )
            throw new RuntimeException( "Got an empty array of limitations from hasAccess( '{$module}', '{$function}' )" );

        /**
         * RoleAssignment is a OR condition, so is policy, while limitations is a AND condition
         *
         * If RoleAssignment has limitation then policy OR conditions are wrapped in a AND condition with the
         * role limitation, otherwise it will be merged into RoleAssignment's OR condition.
         */
        $currentUser = $this->getCurrentUser();
        $roleAssignmentOrCriteria = array();
        $roleService = $this->repository->getRoleService();
        foreach ( $permissionSets as $permissionSet )
        {
            // $permissionSet is a RoleAssignment, but in the form of role limitation & role policies hash
            $policyOrCriteria = array();
            /**
             * @var \eZ\Publish\API\Repository\Values\User\Policy $policy
             */
            foreach ( $permissionSet['policies'] as $policy )
            {
                $limitations = $policy->getLimitations();
                if ( $limitations === '*' || empty( $limitations ) )
                    continue;

                $limitationsAndCriteria = array();
                foreach ( $limitations as $limitation )
                {
                    $type = $roleService->getLimitationType( $limitation->getIdentifier() );
                    $limitationsAndCriteria[] = $type->getCriterion( $limitation, $currentUser );
                }

                $policyOrCriteria[] = isset( $limitationsAndCriteria[1] ) ?
                    new LogicalAnd( $limitationsAndCriteria ) :
                    $limitationsAndCriteria[0];
            }

            /**
             * Apply role limitations if there is one
             * @var \eZ\Publish\API\Repository\Values\User\Limitation[] $permissionSet
             */
            if ( $permissionSet['limitation'] instanceof Limitation )
            {
                // We need to match both the limitation AND *one* of the policies, aka; roleLimit AND policies(OR)
                $type = $roleService->getLimitationType( $permissionSet['limitation']->getIdentifier() );
                if ( !empty( $policyOrCriteria ) )
                {
                    $roleAssignmentOrCriteria[] = new LogicalAnd(
                        array(
                            $type->getCriterion( $permissionSet['limitation'], $currentUser ),
                            isset( $policyOrCriteria[1] ) ? new LogicalOr( $policyOrCriteria ) : $policyOrCriteria[0]
                        )
                    );
                }
                else
                {
                    $roleAssignmentOrCriteria[] = $type->getCriterion( $permissionSet['limitation'], $currentUser );
                }
            }
            // Otherwise merge $policyOrCriteria into $roleAssignmentOrCriteria
            else if ( !empty( $policyOrCriteria ) )
            {
                // There is no role limitation, so any of the policies can globally match in the returned OR criteria
                $roleAssignmentOrCriteria = empty( $roleAssignmentOrCriteria ) ?
                    $policyOrCriteria :
                    array_merge( $roleAssignmentOrCriteria, $policyOrCriteria );
            }
        }

        if ( empty( $roleAssignmentOrCriteria ) )
            return false;

        return isset( $roleAssignmentOrCriteria[1] ) ?
            new LogicalOr( $roleAssignmentOrCriteria ) :
            $roleAssignmentOrCriteria[0];
    }
}
