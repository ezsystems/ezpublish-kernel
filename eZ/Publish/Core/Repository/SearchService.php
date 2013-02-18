<?php
/**
 * File containing the eZ\Publish\Core\Repository\SearchService class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content\Search\Handler;

/**
 * Search service
 *
 * @package eZ\Publish\Core\Repository
 */
class SearchService implements SearchServiceInterface
{
    /**
     * 2^30, since PHP_INT_MAX can cause overflows in DB systems, if PHP is run
     * on 64 bit systems
     */
    const MAX_LIMIT = 1073741824;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    protected $searchHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $searchHandler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->searchHandler = $searchHandler;
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + array(
            //'defaultSetting' => array(),
        );
    }

    /**
     * Finds content objects for the given query.
     *
     * @todo define structs for the field filters
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     * @param boolean $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findContent( Query $query, array $fieldFilters = array(), $filterOnUserPermissions = true )
    {
        if ( $filterOnUserPermissions && !$this->addPermissionsCriterion( $query->criterion ) )
        {
            return new SearchResult( array( 'time' => 0, 'totalCount' => 0 ) );
        }

        if ( $query->limit === null )
        {
            $query->limit = self::MAX_LIMIT;
        }

        $result = $this->searchHandler->findContent( $query, $fieldFilters );
        foreach ( $result->searchHits as $hit )
        {
            $hit->valueObject = $this->repository->getContentService()->buildContentDomainObject(
                $hit->valueObject
            );
        }

        return $result;
    }

    /**
     * Performs a query for a single content object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the object was not found by the query or due to permissions
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is more than one result matching the criterions
     *
     * @todo define structs for the field filters
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     * @param boolean $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function findSingle( Criterion $criterion, array $fieldFilters = array(), $filterOnUserPermissions = true )
    {
        if ( $filterOnUserPermissions && !$this->addPermissionsCriterion( $criterion ) )
        {
            throw new NotFoundException( 'Content', '*' );
        }

        return $this->repository->getContentService()->buildContentDomainObject(
            $this->searchHandler->findSingle( $criterion, $fieldFilters )
        );
    }

    /**
     * Suggests a list of values for the given prefix
     *
     * @param string $prefix
     * @param string[] $fieldPaths
     * @param int $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     */
    public function suggest( $prefix, $fieldPaths = array(), $limit = 10, Criterion $filter = null )
    {

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
        if ( $criterion instanceof Criterion\LogicalAnd )
        {
            $criterion->criteria[] = $permissionCriterion;
        }
        else
        {
            $criterion = new Criterion\LogicalAnd(
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
     * @access private Temporarily made accessible until Location service stops using searchHandler()
     *
     * @uses \eZ\Publish\API\Repository::hasAccess()
     * @throws \RuntimeException If empty array of limitations are provided from hasAccess()
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
            throw new \RuntimeException( "Got an empty array of limitations from hasAccess( '{$module}', '{$function}' )" );

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
                    new Criterion\LogicalAnd( $limitationsAndCriteria ) :
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
                    $roleAssignmentOrCriteria[] = new Criterion\LogicalAnd(
                        array(
                            $type->getCriterion( $permissionSet['limitation'], $currentUser ),
                            isset( $policyOrCriteria[1] ) ? new Criterion\LogicalOr( $policyOrCriteria ) : $policyOrCriteria[0]
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
            new Criterion\LogicalOr( $roleAssignmentOrCriteria ) :
            $roleAssignmentOrCriteria[0];
    }
}
