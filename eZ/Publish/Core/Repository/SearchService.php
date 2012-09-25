<?php
/**
 * File containing the eZ\Publish\Core\Repository\SearchService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\SearchService as SearchServiceInterface,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\Query,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\API\Repository\Values\Content\Search\SearchResult,

    eZ\Publish\Core\Base\Exceptions\NotFoundException,

    eZ\Publish\SPI\Persistence\Content\Search\Handler;

/**
 * Search service
 *
 * @package eZ\Publish\Core\Repository
 */
class SearchService implements SearchServiceInterface
{
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
     * @param \eZ\Publish\API\Repository\Repository  $repository
     * @param \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $searchHandler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->searchHandler = $searchHandler;
        $this->settings = $settings;
    }

     /**
     * finds content objects for the given query.
     *
     * @TODO define structs for the field filters
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array  $fieldFilters - a map of filters for the returned fields.
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
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is more than than one result matching the criterions
     *
     * @TODO define structs for the field filters
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array  $fieldFilters - a map of filters for the returned fields.
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
     * @param string[] $fieldpath
     * @param int $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     */
    public function suggest( $prefix, $fieldPaths = array(), $limit = 10, Criterion $filter = null )
    {

    }

    /**
     * Add Permission criteria if needed and return false if no access at all
     *
     * @access private Temporarly made accessible until Location service stops using searchHandler()
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     * @todo Known issue: Whole permissions system need to be change accommodate role limitations.
     */
    public function addPermissionsCriterion( Criterion &$criterion )
    {
        $limitations = $this->repository->hasAccess( 'content', 'read' );
        if ( $limitations === false || $limitations === true )
        {
            return $limitations;
        }

        if ( empty( $limitations ) )
            throw new \RuntimeException( "Got an empty array of limitations from hasAccess()" );

        // Create OR conditions for every "policy" that contains AND conditions for limitations
        $orCriteria = array();
        $roleService = $this->repository->getRoleService();
        foreach ( $limitations as $limitationSet )
        {
            $andCriteria = array();
            /**
             * @var \eZ\Publish\API\Repository\Values\User\Limitation $limitationValue
             */
            foreach ( $limitationSet as $limitationValue )
            {
                $type = $roleService->getLimitationType( $limitationValue->getIdentifier() );
                $andCriteria[] = $type->getCriterion( $limitationValue, $this->repository );
            }
            $orCriteria[] = isset( $andCriteria[1] ) ? new Criterion\LogicalAnd( $andCriteria ) : $andCriteria[0];
        }

        // Merge with $criterion
        if ( $criterion instanceof Criterion\LogicalAnd )
        {
            $criterion->criteria[] = isset( $orCriteria[1] ) ? new Criterion\LogicalOr( $orCriteria ) : $orCriteria[0];
        }
        else
        {
            $criterion = new Criterion\LogicalAnd(
                array(
                    $criterion,
                    (isset( $orCriteria[1] ) ? new Criterion\LogicalOr( $orCriteria ) : $orCriteria[0])
                )
            );
        }

        return true;
    }
}
