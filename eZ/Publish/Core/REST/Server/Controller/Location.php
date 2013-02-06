<?php
/**
 * File containing the Role controller class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\TrashService;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\REST\Server\Exceptions\BadRequestException;
use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;

/**
 * Location controller
 */
class Location extends RestController
{
    /**
     * Location service
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * Content service
     *
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * Trash service
     *
     * @var \eZ\Publish\API\Repository\TrashService
     */
    protected $trashService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\TrashService $trashService
     */
    public function __construct( LocationService $locationService, ContentService $contentService, TrashService $trashService )
    {
        $this->locationService = $locationService;
        $this->contentService  = $contentService;
        $this->trashService    = $trashService;
    }

    /**
     * Loads the location for a given ID (x)or remote ID
     *
     * @return \eZ\Publish\Core\REST\Server\Values\TemporaryRedirect
     */
    public function redirectLocation()
    {
        if ( !isset( $this->request->variables['id'] ) && !isset( $this->request->variables['remoteId'] ) )
        {
            throw new BadRequestException( "At least one of 'id' or 'remoteId' parameters is required." );
        }

        if ( isset( $this->request->variables['id'] ) )
        {
            $location = $this->locationService->loadLocation( $this->request->variables['id'] );
        }
        else
        {
            $location = $this->locationService->loadLocationByRemoteId( $this->request->variables['remoteId'] );
        }

        return new Values\TemporaryRedirect(
            $this->urlHandler->generate(
                'location',
                array(
                    'location' => rtrim( $location->pathString, '/' )
                )
            )
        );
    }

    /**
     * Creates a new location for the given content object
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedLocation
     */
    public function createLocation()
    {
        $values = $this->urlHandler->parse( 'objectLocations', $this->request->path );

        $locationCreateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        $contentInfo = $this->contentService->loadContentInfo( $values['object'] );

        try
        {
            $createdLocation = $this->locationService->createLocation( $contentInfo, $locationCreateStruct );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }

        return new Values\CreatedLocation( array( "restLocation" => new Values\RestLocation( $createdLocation, 0 ) ) );
    }

    /**
     * Loads a location
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestLocation
     */
    public function loadLocation()
    {
        $values = $this->urlHandler->parse( 'location', $this->request->path );
        return new Values\RestLocation(
            $location = $this->locationService->loadLocation(
                $this->extractLocationIdFromPath( $values['location'] )
            ),
            $this->locationService->getLocationChildCount( $location )
        );
    }

    /**
     * Deletes a location
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteSubtree()
    {
        $values = $this->urlHandler->parse( 'location', $this->request->path );
        $location = $this->locationService->loadLocation( $this->extractLocationIdFromPath( $values['location'] ) );
        $this->locationService->deleteLocation( $location );

        return new Values\NoContent();
    }

    /**
     * Copies a subtree to a new destination
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function copySubtree()
    {
        $values = $this->urlHandler->parse( 'location', $this->request->path );
        $location = $this->locationService->loadLocation( $this->extractLocationIdFromPath( $values['location'] ) );

        $destinationValues = $this->urlHandler->parse( 'location', $this->request->destination );
        $destinationLocation = $this->locationService->loadLocation( $this->extractLocationIdFromPath( $destinationValues['location'] ) );

        $newLocation = $this->locationService->copySubtree( $location, $destinationLocation );

        return new Values\ResourceCreated(
            $this->urlHandler->generate(
                'location',
                array(
                    'location' => rtrim( $newLocation->pathString, '/' ),
                )
            )
        );
    }

    /**
     * Moves a subtree to a new location
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\BadRequestException if the Destination header cannot be parsed as location or trash
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function moveSubtree()
    {
        $values = $this->urlHandler->parse( 'location', $this->request->path );

        $locationToMove = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $values['location'] )
        );

        $destinationLocationId = null;
        try
        {
            // First check to see if the destination is for moving within another subtree
            $destinationValues = $this->urlHandler->parse( 'location', $this->request->destination );
            $destinationLocationId = $this->extractLocationIdFromPath( $destinationValues['location'] );
        }
        catch ( Exceptions\InvalidArgumentException $e )
        {
            try
            {
                // If parsing of destination fails, let's try to see if destination is trash
                $this->urlHandler->parse( 'trashItems', $this->request->destination );
            }
            catch ( Exceptions\InvalidArgumentException $e )
            {
                // If that fails, the Destination header is not formatted right
                // so just throw the BadRequestException
                throw new BadRequestException( "{$this->request->destination} is not formatted correctly" );
            }
        }

        if ( $destinationLocationId !== null )
        {
            // We're moving the subtree
            $destinationLocation = $this->locationService->loadLocation( $destinationLocationId );
            $this->locationService->moveSubtree( $locationToMove, $destinationLocation );

            // Reload the location to get the new position is subtree
            $locationToMove = $this->locationService->loadLocation( $locationToMove->id );
            return new Values\ResourceCreated(
                $this->urlHandler->generate(
                    'location',
                    array(
                        'location' => rtrim( $locationToMove->pathString, '/' ),
                    )
                )
            );
        }

        // We're trashing the subtree
        $trashItem = $this->trashService->trash( $locationToMove );
        return new Values\ResourceCreated(
            $this->urlHandler->generate( 'trash', array( 'trash' => $trashItem->id ) )
        );
    }

    /**
     * Swaps a location with another one
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function swapLocation()
    {
        $values = $this->urlHandler->parse( 'location', $this->request->path );

        $locationId = $this->extractLocationIdFromPath( $values['location'] );
        $location = $this->locationService->loadLocation( $locationId );

        $destinationValues = $this->urlHandler->parse( 'location', $this->request->destination );
        $destinationLocation = $this->locationService->loadLocation( $this->extractLocationIdFromPath( $destinationValues['location'] ) );

        $this->locationService->swapLocation( $location, $destinationLocation );

        return new Values\NoContent();
    }

    /**
     * Loads a location by remote ID
     *
     * @return \eZ\Publish\Core\REST\Server\Values\LocationList
     */
    public function loadLocationByRemoteId()
    {
        return new Values\LocationList(
            array(
                new Values\RestLocation(
                    $location = $this->locationService->loadLocationByRemoteId(
                        $this->request->variables['remoteId']
                    ),
                    $this->locationService->getLocationChildCount( $location )
                )
            ),
            $this->request->path
        );
    }

    /**
     * Loads all locations for content object
     *
     * @return \eZ\Publish\Core\REST\Server\Values\LocationList
     */
    public function loadLocationsForContent()
    {
        $values = $this->urlHandler->parse( 'objectLocations', $this->request->path );
        $restLocations = array();
        foreach (
            $this->locationService->loadLocations(
                $this->contentService->loadContentInfo( $values['object'] )
            ) as $location
        )
        {
            $restLocations[] = new Values\RestLocation(
                $location,
                $this->locationService->getLocationChildCount( $location )
            );
        }

        return new Values\LocationList( $restLocations, $this->request->path );
    }

    /**
     * Loads child locations of a location
     *
     * @return \eZ\Publish\Core\REST\Server\Values\LocationList
     */
    public function loadLocationChildren()
    {
        $questionMark = strpos( $this->request->path, '?' );
        $requestPath = $questionMark !== false ? substr( $this->request->path, 0, $questionMark ) : $this->request->path;

        $values = $this->urlHandler->parse( 'locationChildren', $requestPath );

        $offset = isset( $this->request->variables['offset'] ) ? (int)$this->request->variables['offset'] : 0;
        $limit = isset( $this->request->variables['limit'] ) ? (int)$this->request->variables['limit'] : -1;

        $restLocations = array();
        foreach (
            $this->locationService->loadLocationChildren(
                $this->locationService->loadLocation(
                    $this->extractLocationIdFromPath( $values['location'] )
                ),
                $offset >= 0 ? $offset : 0,
                $limit >= 0 ? $limit : -1
            )->locations as $location
        )
        {
            $restLocations[] = new Values\RestLocation(
                $location,
                $this->locationService->getLocationChildCount( $location )
            );
        }

        return new Values\LocationList( $restLocations, $this->request->path );
    }

    /**
     * Updates a location
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestLocation
     */
    public function updateLocation()
    {
        $values = $this->urlHandler->parse( 'location', $this->request->path );

        $locationUpdate = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        $location = $this->locationService->loadLocation( $this->extractLocationIdFromPath( $values['location'] ) );

        // First handle hiding/unhiding so that updating location afterwards
        // will return updated location with hidden/visible status correctly updated
        // Exact check for true/false is needed as null signals that no hiding/unhiding
        // is to be performed
        if ( $locationUpdate->hidden === true )
        {
            $this->locationService->hideLocation( $location );
        }
        else if ( $locationUpdate->hidden === false )
        {
            $this->locationService->unhideLocation( $location );
        }

        return new Values\RestLocation(
            $location = $this->locationService->updateLocation( $location, $locationUpdate->locationUpdateStruct ),
            $this->locationService->getLocationChildCount( $location )
        );
    }

    /**
     * Extracts and returns an item id from a path, e.g. /1/2/58 => 58
     *
     * @param string $path
     *
     * @return mixed
     */
    private function extractLocationIdFromPath( $path )
    {
        $pathParts = explode( '/', $path );
        return array_pop( $pathParts );
    }
}
