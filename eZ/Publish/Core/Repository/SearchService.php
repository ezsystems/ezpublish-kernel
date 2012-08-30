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

    eZ\Publish\SPI\Persistence\Handler;

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
        $limitations = $this->repository->hasAccess( 'content', 'read' );
        if ( $limitations === false )
        {
            return new SearchResult( array( 'time' => 0, 'totalCount' => 0 ) );
        }
        else if ( $filterOnUserPermissions && $limitations !== true )
        {
            $query->criterion = $this->addPermissionsCriterion( $query->criterion, $limitations );
        }

        $result = $this->persistenceHandler->searchHandler()->findContent( $query, $fieldFilters );
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
        $limitations = $this->repository->hasAccess( 'content', 'read' );
        if ( $limitations === false )
        {
            throw new NotFoundException( 'Content', '*' );
        }
        else if ( $filterOnUserPermissions && $limitations !== true )
        {
            $criterion = $this->addPermissionsCriterion( $criterion, $limitations );
        }

        return $this->repository->getContentService()->buildContentDomainObject(
            $this->persistenceHandler->searchHandler()->findSingle( $criterion, $fieldFilters )
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
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    private function addPermissionsCriterion( Criterion $criterion, array $limitations )
    {
        if ( empty( $limitations ) )
            return $criterion;

        $roleService = $this->repository->getRoleService();
        foreach ( $limitations as $limitationSet )
        {
            /**
             * @var \eZ\Publish\API\Repository\Values\User\Limitation $limitationValue
             */
            foreach ( $limitationSet as $limitationValue )
            {
                if ( !$criterion instanceof Criterion\LogicalAnd )
                    $criterion = new Criterion\LogicalAnd( array( $criterion ) );

                $type = $roleService->getLimitationType( $limitationValue->getIdentifier() );
                $criterion->criteria[] = $type->getCriterion( $limitationValue, $this->repository );
            }
        }

        return $criterion;
    }
}
