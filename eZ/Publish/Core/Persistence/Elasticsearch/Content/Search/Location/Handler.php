<?php
/**
 * File containing the Location Search handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Location;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Search\Location\Handler as SearchHandlerInterface;
use eZ\Publish\SPI\Search\FieldType;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Mapper;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Extractor;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway;

/**
 * The Location Search Handler interface defines search operations on Location elements in the storage engine.
 */
class Handler implements SearchHandlerInterface
{
    /**
     * Content locator gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway
     */
    protected $gateway;

    /**
     * Document mapper
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Mapper
     */
    protected $mapper;

    /**
     * Search result extractor
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Extractor
     */
    protected $extractor;

    /**
     * Creates a new content handler.
     *
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Mapper $mapper
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Extractor $extractor
     */
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
     * Finds Locations for the given $query
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findLocations( LocationQuery $query )
    {
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        $data = $this->gateway->find( $query, "location" );

        return $this->extractor->extract( $data );
    }

    /**
     * Indexes a Location in the index storage
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     */
    public function indexLocation( Location $location )
    {
        $document = $this->mapper->mapContentLocation( $location );

        $this->gateway->index( $document );
    }

    /**
     * Indexes several Locations
     *
     * @todo: This function and setCommit() is needed for Persistence\Solr for test speed but not part
     *       of interface for the reason described in Solr\Content\Search\Gateway\Native::bulkIndexContent
     *       Short: Bulk handling should be properly designed before added to the interface.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location[] $locations
     */
    public function bulkIndexLocations( array $locations )
    {
        $documents = array();
        foreach ( $locations as $location )
        {
            $documents[] = $this->mapper->mapContentLocation( $location );
        }

        $this->gateway->bulkIndex( $documents );
    }

    /**
     * Deletes a Location from the index storage
     *
     * @param int|string $locationId
     */
    public function deleteLocation( $locationId )
    {
        $this->gateway->delete( $locationId, "location" );
    }

    /**
     * Deletes a Content from the index storage
     *
     * @param $contentId
     */
    public function deleteContent( $contentId )
    {
        $ast = array(
            "query" => array(
                "filtered" => array(
                    "filter" => array(
                        "term" => array(
                            "content_id" => $contentId,
                        ),
                    ),
                ),
            ),
        );

        $this->gateway->deleteByQuery( json_encode( $ast ), "location" );
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
        $this->gateway->purgeIndex( "location" );
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
