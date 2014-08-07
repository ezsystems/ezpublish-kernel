<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Search\Handler as SearchHandlerInterface;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * The Content Search handler retrieves sets of of Content objects, based on a
 * set of criteria.
 *
 * The basic idea of this class is to do the following:
 *
 * 1) The find methods retrieve a recursive set of filters, which define which
 * content objects to retrieve from the database. Those may be combined using
 * boolean operators.
 *
 * 2) This recursive criterion definition is visited into a query, which limits
 * the content retrieved from the database. We might not be able to create
 * sensible queries from all criterion definitions.
 *
 * 3) The query might be possible to optimize (remove empty statements),
 * reduce singular and and or constructs…
 *
 * 4) Additionally we might need a post-query filtering step, which filters
 * content objects based on criteria, which could not be converted in to
 * database statements.
 */
class Handler implements SearchHandlerInterface
{
    /**
     * Content locator gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway
     */
    protected $gateway;

    /**
     * Content mapper
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $contentMapper;

    /**
     * Creates a new content handler.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Mapper $contentMapper
     */
    public function __construct( Gateway $gateway, ContentMapper $contentMapper )
    {
        $this->gateway = $gateway;
        $this->contentMapper = $contentMapper;
    }

    /**
     * Finds content objects for the given query.
     *
     * @todo define structs for the field filters
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Query criterion is not applicable to its target
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findContent( Query $query, array $fieldFilters = array() )
    {
        $start = microtime( true );
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        if ( count( $query->facetBuilders ) )
        {
            throw new NotImplementedException( "Facets are not supported by the legacy search engine." );
        }

        // The legacy search does not know about scores, so that we just
        // combine the query with the filter
        $filter = new Criterion\LogicalAnd( array( $query->query, $query->filter ) );

        $data = $this->gateway->find( $filter, $query->offset, $query->limit, $query->sortClauses, null );

        $result = new SearchResult();
        $result->time = microtime( true ) - $start;
        $result->totalCount = $data['count'];

        foreach ( $this->contentMapper->extractContentInfoFromRows( $data['rows'], '', 'main_tree_' ) as $contentInfo )
        {
            $searchHit = new SearchHit();
            $searchHit->valueObject = $contentInfo;

            $result->searchHits[] = $searchHit;
        }

        return $result;
    }

    /**
     * Performs a query for a single content object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the object was not found by the query or due to permissions
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Criterion is not applicable to its target
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is more than than one result matching the criterions
     *
     * @todo define structs for the field filters
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    public function findSingle( Criterion $filter, array $fieldFilters = array() )
    {
        $searchQuery = new Query();
        $searchQuery->filter = $filter;
        $searchQuery->query  = new Criterion\MatchAll();
        $searchQuery->offset = 0;
        $searchQuery->limit  = 1;
        $result = $this->findContent( $searchQuery, $fieldFilters );

        if ( !$result->totalCount )
            throw new NotFoundException( 'Content', "findSingle() found no content for given \$criterion" );
        else if ( $result->totalCount > 1 )
            throw new InvalidArgumentException( "totalCount", "findSingle() found more then one item for given \$criterion" );

        $first = reset( $result->searchHits );
        return $first->valueObject;
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
        throw new NotImplementedException( "Suggestions are not supported by legacy search engine." );
    }

    /**
     * Indexes a content object
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return void
     */
    public function indexContent( Content $content )
    {
        throw new \Exception( "Not implemented yet." );
    }

    /**
     * Deletes a content object from the index
     *
     * @param int $contentId
     * @param int|null $versionId
     *
     * @return void
     */
    public function deleteContent( $contentId, $versionId = null )
    {
        throw new \Exception( "Not implemented yet." );
    }

    /**
     * Deletes a location from the index
     *
     * @param int|string $locationId
     */
    public function deleteLocation( $locationId )
    {
        // Not implemented in Legacy Storage Engine
    }
}
