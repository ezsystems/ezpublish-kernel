<?php
/**
 * File containing the Trash controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\TrashService;
use eZ\Publish\API\Repository\LocationService;

use eZ\Publish\API\Repository\Values\Content\Query;

use Qafoo\RMF;

use InvalidArgumentException;

/**
 * Trash controller
 */
class Trash
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
     * Trash service
     *
     * @var \eZ\Publish\API\Repository\TrashService
     */
    protected $trashService;

    /**
     * Location service
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\TrashService $trashService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler, TrashService $trashService, LocationService $locationService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler = $urlHandler;
        $this->trashService = $trashService;
        $this->locationService = $locationService;
    }

    /**
     * Returns a list of all trash items
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\LocationList
     */
    public function loadTrashItems( RMF\Request $request )
    {
        return new Values\Trash(
            $this->trashService->findTrashItems(
                new Query()
            )->items,
            $request->path
        );
    }

    /**
     * Returns the trash item given by id
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function loadTrashItem( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'trash', $request->path );
        return $this->trashService->loadTrashItem( $values['trash'] );
    }

    /**
     * Empties the trash
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function emptyTrash( RMF\Request $request )
    {
        $this->trashService->emptyTrash();

        return new Values\ResourceDeleted();
    }

    /**
     * Deletes the given trash item
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deleteTrashItem( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'trash', $request->path );
        $this->trashService->deleteTrashItem(
            $this->trashService->loadTrashItem( $values['trash'] )
        );

        return new Values\ResourceDeleted();
    }

    /**
     * Restores a trashItem
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function restoreTrashItem( RMF\Request $request )
    {
        $requestDestination = null;
        try
        {
            $requestDestination = $request->destination;
        }
        catch ( InvalidArgumentException $e )
        {
            // No Destination header
        }

        $parentLocation = null;
        if ( $requestDestination !== null )
        {
            $destinationValues = $this->urlHandler->parse( 'location', $requestDestination );

            $locationPath = $destinationValues['location'];
            $locationPathParts = explode( '/', $locationPath );

            $parentLocation = $this->locationService->loadLocation( array_pop( $locationPathParts ) );
        }

        $values = $this->urlHandler->parse( 'trash', $request->path );

        $trashItem = $this->trashService->loadTrashItem( $values['trash'] );
        $location = $this->trashService->recover( $trashItem, $parentLocation );

        return new Values\ResourceCreated(
            $this->urlHandler->generate(
                'location',
                array(
                    'location' => rtrim( $location->pathString, '/' ),
                )
            )
        );
    }
}
