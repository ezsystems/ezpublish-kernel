<?php
/**
 * File containing the Location Search Handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Location\Search;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content\Location\Search\Handler as BaseLocationSearchHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper;

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
    public function findLocations( Query $query )
    {
        return $this->locationMapper->createLocationsFromRows(
            $this->locationGateway->find( $query )
        );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Search\Handler::getLocationCount
     */
    public function getLocationCount( Criterion $criterion )
    {
        return $this->locationGateway->count( $criterion );
    }
}
