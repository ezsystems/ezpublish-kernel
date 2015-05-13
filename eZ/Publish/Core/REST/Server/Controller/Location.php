<?php
/**
 * File containing the Role controller class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\BadRequestException
     * @return \eZ\Publish\Core\REST\Server\Values\TemporaryRedirect
     */
    public function redirectLocation()
    {
        if ( !$this->request->query->has( 'id' ) && !$this->request->query->has( 'remoteId' ) )
        {
            throw new BadRequestException( "At least one of 'id' or 'remoteId' parameters is required." );
        }

        if ( $this->request->query->has( 'id' ) )
        {
            $location = $this->locationService->loadLocation( $this->request->query->get( 'id' ) );
        }
        else
        {
            $location = $this->locationService->loadLocationByRemoteId( $this->request->query->get( 'remoteId' ) );
        }

        return new Values\TemporaryRedirect(
            $this->router->generate(
                'ezpublish_rest_loadLocation',
                array(
                    'locationPath' => trim( $location->pathString, '/' )
                )
            )
        );
    }

    /**
     * Creates a new location for object with id $contentId
     *
     * @param mixed $contentId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedLocation
     */
    public function createLocation( $contentId )
    {
        $locationCreateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->headers->get( 'Content-Type' ) ),
                $this->request->getContent()
            )
        );

        $contentInfo = $this->contentService->loadContentInfo( $contentId );

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
     * @param string $locationPath
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestLocation
     */
    public function loadLocation( $locationPath )
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $locationPath )
        );

        if ( trim( $location->pathString, '/' ) != $locationPath )
        {
            throw new Exceptions\NotFoundException(
                "Could not find location with path string $locationPath"
            );
        }

        return new Values\CachedValue(
            new Values\RestLocation(
                $location,
                $this->locationService->getLocationChildCount( $location )
            ),
            array( 'locationId' => $location->id )
        );
    }

    /**
     * Deletes a location
     *
     * @param string $locationPath
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteSubtree( $locationPath)
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $locationPath )
        );
        $this->locationService->deleteLocation( $location );

        return new Values\NoContent();
    }

    /**
     * Copies a subtree to a new destination
     *
     * @param string $locationPath
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function copySubtree( $locationPath )
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $locationPath )
        );

        $destinationLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath(
                $this->requestParser->parseHref(
                    $this->request->headers->get( 'Destination' ),
                    'locationPath'
                )
            )
        );

        $newLocation = $this->locationService->copySubtree( $location, $destinationLocation );

        return new Values\ResourceCreated(
            $this->router->generate(
                'ezpublish_rest_loadLocation',
                array(
                    'locationPath' => trim( $newLocation->pathString, '/' ),
                )
            )
        );
    }

    /**
     * Moves a subtree to a new location
     *
     * @param string $locationPath
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\BadRequestException if the Destination header cannot be parsed as location or trash
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function moveSubtree( $locationPath )
    {
        $locationToMove = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $locationPath )
        );

        $destinationLocationId = null;
        $destinationHref = $this->request->headers->get( 'Destination' );
        try
        {
            // First check to see if the destination is for moving within another subtree
            $destinationLocationId = $this->extractLocationIdFromPath(
                $this->requestParser->parseHref( $destinationHref, 'locationPath' )
            );

            // We're moving the subtree
            $destinationLocation = $this->locationService->loadLocation( $destinationLocationId );
            $this->locationService->moveSubtree( $locationToMove, $destinationLocation );

            // Reload the location to get the new position is subtree
            $locationToMove = $this->locationService->loadLocation( $locationToMove->id );
            return new Values\ResourceCreated(
                $this->router->generate(
                    'ezpublish_rest_loadLocation',
                    array(
                        'locationPath' => rtrim( $locationToMove->pathString, '/' ),
                    )
                )
            );
        }
        // If parsing of destination fails, let's try to see if destination is trash
        catch ( Exceptions\InvalidArgumentException $e )
        {
            try
            {
                $route = $this->requestParser->parse( $destinationHref );
                if ( !isset( $route['_route'] ) || $route['_route'] !== 'ezpublish_rest_loadTrashItems' )
                {
                    throw new Exceptions\InvalidArgumentException( '' );
                }
                // Trash the subtree
                $trashItem = $this->trashService->trash( $locationToMove );
                return new Values\ResourceCreated(
                    $this->router->generate(
                        'ezpublish_rest_loadTrashItem',
                        array( 'trashItemId' => $trashItem->id )
                    )
                );
            }
            catch ( Exceptions\InvalidArgumentException $e )
            {
                // If that fails, the Destination header is not formatted right
                // so just throw the BadRequestException
                throw new BadRequestException( "{$destinationHref} is not an acceptable destination" );
            }
        }
    }

    /**
     * Swaps a location with another one
     *
     * @param string $locationPath
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function swapLocation( $locationPath )
    {
        $locationId = $this->extractLocationIdFromPath( $locationPath );
        $location = $this->locationService->loadLocation( $locationId );

        $destinationLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath(
                $this->requestParser->parseHref(
                    $this->request->headers->get( 'Destination' ),
                    'locationPath'
                )
            )
        );

        $this->locationService->swapLocation( $location, $destinationLocation );

        return new Values\NoContent();
    }

    /**
     * Loads a location by remote ID
     * @todo remove, or use in loadLocation with filter
     *
     * @return \eZ\Publish\Core\REST\Server\Values\LocationList
     */
    public function loadLocationByRemoteId()
    {
        return new Values\LocationList(
            array(
                new Values\RestLocation(
                    $location = $this->locationService->loadLocationByRemoteId(
                        $this->request->query->get( 'remoteId' )
                    ),
                    $this->locationService->getLocationChildCount( $location )
                )
            ),
            $this->request->getPathInfo()
        );
    }

    /**
     * Loads all locations for content object
     *
     * @param mixed $contentId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\LocationList
     */
    public function loadLocationsForContent( $contentId )
    {
        $restLocations = array();
        $contentInfo = $this->contentService->loadContentInfo( $contentId );
        foreach ( $this->locationService->loadLocations( $contentInfo ) as $location )
        {
            $restLocations[] = new Values\RestLocation(
                $location,
                // @todo Remove, and make optional in VO. Not needed for a location list.
                $this->locationService->getLocationChildCount( $location )
            );
        }

        return new Values\CachedValue(
            new Values\LocationList( $restLocations, $this->request->getPathInfo() ),
            array( 'locationId' => $contentInfo->mainLocationId )
        );
    }

    /**
     * Loads child locations of a location
     *
     * @param string $locationPath
     *
     * @return \eZ\Publish\Core\REST\Server\Values\LocationList
     */
    public function loadLocationChildren( $locationPath )
    {
        $offset = $this->request->query->has( 'offset' ) ? (int)$this->request->query->get( 'offset' ) : 0;
        $limit = $this->request->query->has( 'limit' ) ? (int)$this->request->query->get( 'limit' ) : 10;

        $restLocations = array();
        $locationId = $this->extractLocationIdFromPath( $locationPath );
        foreach (
            $this->locationService->loadLocationChildren(
                $this->locationService->loadLocation(
                    $locationId
                ),
                $offset >= 0 ? $offset : 0,
                $limit >= 0 ? $limit : 10
            )->locations as $location
        )
        {
            $restLocations[] = new Values\RestLocation(
                $location,
                $this->locationService->getLocationChildCount( $location )
            );
        }

        return new Values\CachedValue(
            new Values\LocationList( $restLocations, $this->request->getPathInfo() ),
            array( 'locationId' => $locationId )
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

    /**
     * Updates a location
     *
     * @param string $locationPath
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestLocation
     */
    public function updateLocation( $locationPath )
    {
        $locationUpdate = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->headers->get( 'Content-Type' ) ),
                $this->request->getContent()
            )
        );

        $location = $this->locationService->loadLocation( $this->extractLocationIdFromPath( $locationPath ) );

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
}
