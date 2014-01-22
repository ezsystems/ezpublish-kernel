<?php
/**
 * File containing the eZ\Publish\Core\Repository\PermissionsCriterionHandler class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use RuntimeException;

/**
 * Handler for permissions Criterion
 *
 * @package eZ\Publish\Core\Repository
 */
class PermissionsCriterionHandler
{
    /**
     * Constructor
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct( RepositoryInterface $repository )
    {
        $this->repository = $repository;
    }

    /**
     * Adds content, read Permission criteria if needed and return false if no access at all
     *
     * @access private Temporarily made accessible until Location service stops using searchHandler()
     * @uses getPermissionsCriterion()
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean|\eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public function addPermissionsCriterion( Criterion &$criterion )
    {
        $permissionCriterion = $this->getPermissionsCriterion();
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
        return true;
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
        $permissionSets = $this->repository->hasAccess( $module, $function );
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
        $currentUser = $this->repository->getCurrentUser();
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
