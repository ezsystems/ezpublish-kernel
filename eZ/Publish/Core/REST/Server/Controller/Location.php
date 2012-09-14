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
use eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\ContentService;

use Qafoo\RMF;

/**
 * Location controller
 */
class Location
{
    /**
     * Input dispatcher
     *
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    protected $inputDispatcher;

    /**
     * URL handler
     *
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    protected $urlHandler;

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
     * Construct controller
     *
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler, LocationService $locationService, ContentService $contentService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler      = $urlHandler;
        $this->locationService = $locationService;
        $this->contentService  = $contentService;
    }

    /**
     * Creates a new location for the given content object
     *
     * @param \Qafoo\RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function createLocation( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'objectLocations', $request->path );

        $locationCreateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
            )
        );

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
     * @param \Qafoo\RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function loadLocation( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'location', $request->path );
        return $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $values['location'] )
        );
    }

    /**
     * Deletes a location
     *
     * @param \Qafoo\RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deleteSubtree( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'location', $request->path );
        $location = $this->locationService->loadLocation( $this->extractLocationIdFromPath( $values['location'] ) );
        $this->locationService->deleteLocation( $location );

        return new Values\ResourceDeleted();
    }

    /**
     * Copies a subtree to a new destination
     *
     * @param \Qafoo\RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function copySubtree( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'location', $request->path );
        $location = $this->locationService->loadLocation( $this->extractLocationIdFromPath( $values['location'] ) );

        $destinationValues = $this->urlHandler->parse( 'location', $request->destination );
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
     * @param \QaFoo\RMF\Request $request
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function moveSubtree( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'location', $request->path );

        $locationId = $this->extractLocationIdFromPath($values['location']);
        $location = $this->locationService->loadLocation( $locationId );

        $destinationValues = $this->urlHandler->parse( 'location', $request->destination );
        $destinationLocation = $this->locationService->loadLocation( $this->extractLocationIdFromPath( $destinationValues['location'] ) );

        $this->locationService->moveSubtree( $location, $destinationLocation );

        $location = $this->locationService->loadLocation( $locationId );

        return new Values\ResourceCreated(
            $this->urlHandler->generate(
                'location',
                array(
                    'location' => rtrim( $location->pathString, '/' ),
                )
            )
        );
    }

    /**
     * Swaps a location with another one
     *
     * @param \QaFoo\RMF\Request $request
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceSwapped
     */
    public function swapLocation( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'location', $request->path );

        $locationId = $this->extractLocationIdFromPath($values['location']);
        $location = $this->locationService->loadLocation( $locationId );

        $destinationValues = $this->urlHandler->parse( 'location', $request->destination );
        $destinationLocation = $this->locationService->loadLocation( $this->extractLocationIdFromPath( $destinationValues['location'] ) );

        $this->locationService->swapLocation( $location, $destinationLocation );

        return new Values\ResourceSwapped();
    }

    /**
     * Loads a location by remote ID
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\LocationList
     */
    public function loadLocationByRemoteId( RMF\Request $request )
    {
        return new Values\LocationList(
            array(
                $this->locationService->loadLocationByRemoteId(
                    $request->variables['remoteId']
                )
            ),
            $request->path
        );
    }

    /**
     * Loads all locations for content object
     *
     * @param \Qafoo\RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\LocationList
     */
    public function loadLocationsForContent( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'objectLocations', $request->path );

        return new Values\LocationList(
            $this->locationService->loadLocations(
                $this->contentService->loadContentInfo( $values['object'] )
            ),
            $request->path
        );
    }

    /**
     * Loads child locations of a location
     *
     * @param \Qafoo\RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\LocationList
     */
    public function loadLocationChildren( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'locationChildren', $request->path );

        return new Values\LocationList(
            $this->locationService->loadLocationChildren(
                $this->locationService->loadLocation(
                    $this->extractLocationIdFromPath( $values['location'] )
                )
            ),
            $request->path
        );
    }

    /**
     * Updates a location
     *
     * @param \Qafoo\RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function updateLocation( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'location', $request->path );

        return $this->locationService->updateLocation(
            $this->locationService->loadLocation(
                $this->extractLocationIdFromPath( $values['location'] )
            ),
            $this->inputDispatcher->parse(
                new Message(
                    array( 'Content-Type' => $request->contentType ),
                    $request->body
                )
            )
        );
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
