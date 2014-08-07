<?php
/**
 * File containing the Location Search Handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Location;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\Search\Handler as LocationSearchHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * The Location Search handler retrieves sets of of Location objects, based on a
 * set of criteria.
 */
class Handler implements LocationSearchHandler
{
    /**
     * Gateway for handling location data
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Location\Gateway
     */
    protected $gateway;

    /**
     * Location locationMapper
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected $locationMapper;

    /**
     * Construct from search gateway and mapper
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Location\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper $locationMapper
     */
    public function __construct( Gateway $gateway, LocationMapper $locationMapper )
    {
        $this->gateway = $gateway;
        $this->locationMapper = $locationMapper;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Search\Handler::findLocations
     */
    public function findLocations( LocationQuery $query )
    {
        $start = microtime( true );
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        if ( count( $query->facetBuilders ) )
        {
            throw new NotImplementedException( "Facets are not supported by the legacy search engine." );
        }

        // The legacy search does not know about scores, so we just
        // combine the query with the filter
        $data = $this->gateway->find(
            new Criterion\LogicalAnd( array( $query->query, $query->filter ) ),
            $query->offset,
            $query->limit,
            $query->sortClauses
        );

        $result = new SearchResult();
        $result->time = microtime( true ) - $start;
        $result->totalCount = $data['count'];

        foreach ( $this->locationMapper->createLocationsFromRows( $data['rows'] ) as $location )
        {
            $result->searchHits[] = new SearchHit( array( "valueObject" => $location ) );
        }

        return $result;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     */
    public function indexLocation( Location $location )
    {
        // TODO: Implement indexLocation() method.
    }

    /**
     * @param int|string $locationId
     */
    public function deleteLocation( $locationId )
    {
        // This method does nothing in Legacy Storage Engine
    }

    /**
     * @param int|string $contentId
     */
    public function deleteContent( $contentId )
    {
        // This method does nothing in Legacy Storage Engine
    }
}
