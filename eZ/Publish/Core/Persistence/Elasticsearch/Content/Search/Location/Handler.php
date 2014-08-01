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
use eZ\Publish\SPI\Persistence\Content\Location\Search\Handler as SearchHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Search\FieldType;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Mapper;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Extractor;

/**
 *
 */
class Handler implements SearchHandlerInterface
{
    /**
     * Content locator gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Location\Gateway
     */
    protected $gateway;

    /**
     * Field name generator
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
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Location\Gateway $gateway
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

        $data = $this->gateway->findLocations( $query );

        return $this->extractor->extract( $data );
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     */
    public function indexLocation( Location $location )
    {
        $document = $this->mapper->mapContentLocation( $location );

        $this->gateway->indexDocument( $document );
    }

    public function bulkIndexLocations( array $locations )
    {
        foreach ( $locations as $location )
        {
            $this->indexLocation( $location );
        }
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
        $this->gateway->purgeIndex();
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
