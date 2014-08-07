<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Search\Handler as SearchHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Search\FieldType;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Section;

class Handler implements SearchHandlerInterface
{
    /**
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway
     */
    protected $gateway;

    /**
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Mapper
     */
    protected $mapper;

    /**
     * Search result extractor
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Extractor
     */
    protected $extractor;

    public function __construct(
        Gateway $gateway,
        Mapper $mapper,
        Extractor $extractor
    )
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
        $this->extractor = $extractor;
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
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        // TODO add field filters to the query

        $data = $this->gateway->find( $query, "content" );

        return $this->extractor->extract( $data );
    }

    /**
     * Performs a query for a single content object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the object was not found by the query or due to permissions
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Criterion is not applicable to its target
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is more than than one result matching the criterions
     *
     * @todo define structs for the field filters
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    public function findSingle( Criterion $filter, array $fieldFilters = array() )
    {
        // TODO: Implement findSingle() method.
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
        // TODO: Implement suggest() method.
    }

    /**
     * Indexes a content object
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    public function indexContent( Content $content )
    {
        $document = $this->mapper->mapContent( $content );

        $this->gateway->index( $document );
    }

    /**
     * Indexes several content objects
     *
     * @todo: This function and setCommit() is needed for Persistence\Solr for test speed but not part
     *       of interface for the reason described in Solr\Content\Search\Gateway\Native::bulkIndexContent
     *       Short: Bulk handling should be properly designed before added to the interface.
     *
     * @param \eZ\Publish\SPI\Persistence\Content[] $contentObjects
     *
     * @return void
     */
    public function bulkIndexContent( array $contentObjects )
    {
        $documents = array();
        foreach ( $contentObjects as $content )
        {
            $documents[] = $this->mapper->mapContent( $content );
        }

        $this->gateway->bulkIndex( $documents );
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
        if ( $versionId === null )
        {
            $query = array(
                "filter" => array(
                    "and" => array(
                        array(
                            "ids" => array(
                                "type" => "content",
                                "values" => $contentId,
                            )
                        ),
                        array(
                            "term" => array(
                                "version_id" => $versionId,
                            ),
                        ),
                    ),
                ),
            );

            $this->gateway->deleteByQuery( $query, "content" );
        }
        else
        {
            $this->gateway->delete( $contentId, "content" );
        }
    }

    /**
     *
     *
     * @todo When we support Location-less Content, we will have to reindex instead of removing
     * @todo Should we not already support the above?
     * @todo The subtree could potentially be huge, so this implementation should scroll reindex
     *
     * @param int|string $locationId
     */
    public function deleteLocation( $locationId )
    {
        // 1. Update all Content in the subtree with additional Location(s) outside of it
        $query = array(
            "filter" => array(
                "nested" => array(
                    "path" => "locations_doc",
                    "filter" => array(
                        "and" => array(
                            0 => array(
                                "regexp" => array(
                                    "locations_doc.path_string_id" => ".*/{$locationId}/.*",
                                ),
                            ),
                            1 => array(
                                "regexp" => array(
                                    "locations_doc.path_string_id" => array(
                                        // Matches anything (@) and (&) not (~) <expression>
                                        "value" => "@&~(.*/{$locationId}/.*)",
                                        "flags" => "INTERSECTION|COMPLEMENT|ANYSTRING",
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $response = $this->gateway->findRaw( $query, "content" );
        $result = json_decode( $response->body );

        $documents = array();
        foreach ( $result->hits->hits as $hit )
        {
            $documents[] = $this->mapper->mapContentById( $hit->_id );
        }

        $this->gateway->bulkIndex( $documents );

        // 2. Delete all Content in the subtree with no other Location(s) outside of it
        $query["filter"]["nested"]["filter"]["and"][1] = array(
            "not" => $query["filter"]["nested"]["filter"]["and"][1],
        );

        $this->gateway->deleteByQuery( $query, "content" );
    }

    /**
     * Purges all contents from the index
     *
     * @todo: Make this public API?
     *
     * @return void
     */
    public function purgeIndex()
    {
        $this->gateway->purgeIndex( "content" );
    }

    /**
     * Set if index/delete actions should commit or if several actions is to be expected
     *
     * This should be set to false before group of actions and true before the last one
     *
     * @param bool $commit
     */
    public function setCommit( $commit )
    {
        //$this->gateway->setCommit( $commit );
    }

    public function flush()
    {
        $this->gateway->flush();
    }
}
