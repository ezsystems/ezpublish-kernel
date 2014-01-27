<?php
/**
 * File containing the Location Search Handler class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Location\Search;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content\Location\Search\Handler as BaseLocationSearchHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * The Location Handler interface defines operations on Location elements in the storage engine.
 */
class Handler implements BaseLocationSearchHandler
{
    /**
     * Gateway for handling location data
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Location locationMapper
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected $locationMapper;

    /**
     * Construct from userGateway
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway $locationGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper $locationMapper
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler
     */
    public function __construct( LocationGateway $locationGateway, LocationMapper $locationMapper )
    {
        $this->locationGateway = $locationGateway;
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

        $data = $this->locationGateway->find( $query );

        $result = new SearchResult();
        $result->time = microtime( true ) - $start;
        $result->totalCount = $data['count'];

        foreach ( $this->locationMapper->createLocationsFromRows( $data['rows'] ) as $location )
        {
            $searchHit = new SearchHit();
            $searchHit->valueObject = $location;

            $result->searchHits[] = $searchHit;
        }

        return $result;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Search\Handler::getLocationCount
     */
    public function getLocationCount( Criterion $criterion )
    {
        return $this->locationGateway->count( $criterion );
    }
}
