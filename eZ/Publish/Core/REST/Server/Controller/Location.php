<?php
/**
 * File containing the Role controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\TrashService;

use eZ\Publish\Core\REST\Server\Exceptions\BadRequestException;

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

        //@todo Error handling if a location under the given parent id already exists
        //Problem being that PAPI throws same exception for several conditions

        $contentInfo = $this->contentService->loadContentInfo( $values['object'] );
        return new Values\CreatedLocation(
            array(
                'location' => $this->locationService->createLocation( $contentInfo, $locationCreateStruct )
            )
        );
    }

    /**
     * Loads a location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function loadLocation()
    {
        $values = $this->urlHandler->parse( 'location', $this->request->path );
        return $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $values['location'] )
        );
    }

    /**
     * Deletes a location
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deleteSubtree()
    {
        $values = $this->urlHandler->parse( 'location', $this->request->path );
        $location = $this->locationService->loadLocation( $this->extractLocationIdFromPath( $values['location'] ) );
        $this->locationService->deleteLocation( $location );

        return new Values\ResourceDeleted();
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
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceSwapped
     */
    public function swapLocation()
    {
        $values = $this->urlHandler->parse( 'location', $this->request->path );

        $locationId = $this->extractLocationIdFromPath($values['location']);
        $location = $this->locationService->loadLocation( $locationId );

        $destinationValues = $this->urlHandler->parse( 'location', $this->request->destination );
        $destinationLocation = $this->locationService->loadLocation( $this->extractLocationIdFromPath( $destinationValues['location'] ) );

        $this->locationService->swapLocation( $location, $destinationLocation );

        return new Values\ResourceSwapped();
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
                $this->locationService->loadLocationByRemoteId(
                    $this->request->variables['remoteId']
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

        return new Values\LocationList(
            $this->locationService->loadLocations(
                $this->contentService->loadContentInfo( $values['object'] )
            ),
            $this->request->path
        );
    }

    /**
     * Loads child locations of a location
     *
     * @return \eZ\Publish\Core\REST\Server\Values\LocationList
     */
    public function loadLocationChildren()
    {
        $values = $this->urlHandler->parse( 'locationChildren', $this->request->path );

        return new Values\LocationList(
            $this->locationService->loadLocationChildren(
                $this->locationService->loadLocation(
                    $this->extractLocationIdFromPath( $values['location'] )
                )
            ),
            $this->request->path
        );
    }

    /**
     * Updates a location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
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

        return $this->locationService->updateLocation( $location, $locationUpdate->locationUpdateStruct );
    }

    /**
     * Extracts and returns an item id from a path, e.g. /1/2/58 => 58
     *
     * @param string $path
     * @return mixed
     */
    private function extractLocationIdFromPath( $path )
    {
        $pathParts = explode( '/', $path );
        return array_pop( $pathParts );
    }
}
