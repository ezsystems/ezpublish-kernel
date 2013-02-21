<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Search\Handler as BaseSearchHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
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
 * content objects based on criteria, which could not be convertedd in to
 * database statements.
 */
class Handler extends BaseSearchHandler
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
     * FieldHandler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\FieldHandler
     */
    protected $fieldHandler;

    /**
     * Creates a new content handler.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Mapper $contentMapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler $fieldHandler
     */
    public function __construct( Gateway $gateway, ContentMapper $contentMapper, FieldHandler $fieldHandler )
    {
        $this->gateway = $gateway;
        $this->contentMapper = $contentMapper;
        $this->fieldHandler = $fieldHandler;
    }

    /**
     * Finds content objects for the given query.
     *
     * @todo define structs for the field filters
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

        if ( count( $query->facetBuilders ) )
        {
            throw new NotImplementedException( "Facettes are not supported by the legacy search engine." );
        }

        $data  = $this->gateway->find( $query->criterion, $query->offset, $query->limit, $query->sortClauses, null );

        $result = new SearchResult();
        $result->time       = microtime( true ) - $start;
        $result->totalCount = $data['count'];

        foreach ( $this->contentMapper->extractContentFromRows( $data['rows'] ) as $content )
        {
            $this->fieldHandler->loadExternalFieldData( $content );
            $searchHit = new SearchHit();
            $searchHit->valueObject = $content;

            $result->searchHits[] = $searchHit;
        }

        return $result;
    }

    /**
     * Performs a query for a single content object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the object was not found by the query or due to permissions
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is more than than one result matching the criterions
     *
     * @todo define structs for the field filters
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function findSingle( Criterion $criterion, array $fieldFilters = array() )
    {
        $query = new Query();
        $query->criterion = $criterion;
        $query->offset    = 0;
        $query->limit     = 1;
        $result = $this->findContent( $query, $fieldFilters );

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
     * @param int $contentID
     * @param int|null $versionID
     *
     * @return void
     */
    public function deleteContent( $contentID, $versionID = null )
    {
        throw new \Exception( "Not implemented yet." );
    }
}
