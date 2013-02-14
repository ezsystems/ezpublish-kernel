<?php
/**
 * File containing the Trash controller class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use eZ\Publish\API\Repository\TrashService;
use eZ\Publish\API\Repository\LocationService;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;

use InvalidArgumentException;

/**
 * Trash controller
 */
class Trash extends RestController
{
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
     * @param \eZ\Publish\API\Repository\TrashService $trashService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     */
    public function __construct( TrashService $trashService, LocationService $locationService )
    {
        $this->trashService = $trashService;
        $this->locationService = $locationService;
    }

    /**
     * Returns a list of all trash items
     *
     * @return \eZ\Publish\Core\REST\Server\Values\Trash
     */
    public function loadTrashItems()
    {
        $offset = isset( $this->request->variables['offset'] ) ? (int)$this->request->variables['offset'] : 0;
        $limit = isset( $this->request->variables['limit'] ) ? (int)$this->request->variables['limit'] : -1;

        $query = new Query();
        $query->offset = $offset >= 0 ? $offset : null;
        $query->limit = $limit >= 0 ? $limit : null;

        $trashItems = array();

        foreach (
            $this->trashService->findTrashItems(
                $query
            )->items as $trashItem
        )
        {
            $trashItems[] = new Values\RestTrashItem(
                $trashItem,
                $this->locationService->getLocationChildCount( $trashItem )
            );
        }

        return new Values\Trash(
            $trashItems,
            $this->request->path
        );
    }

    /**
     * Returns the trash item given by id
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestTrashItem
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    public function loadTrashItem()
    {
        $values = $this->urlHandler->parse( 'trash', $this->request->path );
        return new Values\RestTrashItem(
            $trashItem = $this->trashService->loadTrashItem( $values['trash'] ),
            $this->locationService->getLocationChildCount( $trashItem )
        );
    }

    /**
     * Empties the trash
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function emptyTrash()
    {
        $this->trashService->emptyTrash();

        return new Values\NoContent();
    }

    /**
     * Deletes the given trash item
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteTrashItem()
    {
        $values = $this->urlHandler->parse( 'trash', $this->request->path );
        $this->trashService->deleteTrashItem(
            $this->trashService->loadTrashItem( $values['trash'] )
        );

        return new Values\NoContent();
    }

    /**
     * Restores a trashItem
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function restoreTrashItem()
    {
        $requestDestination = null;
        try
        {
            $requestDestination = $this->request->destination;
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

            try
            {
                $parentLocation = $this->locationService->loadLocation( array_pop( $locationPathParts ) );
            }
            catch ( NotFoundException $e )
            {
                throw new ForbiddenException( $e->getMessage() );
            }
        }

        $values = $this->urlHandler->parse( 'trash', $this->request->path );
        $trashItem = $this->trashService->loadTrashItem( $values['trash'] );

        if ( $requestDestination === null )
        {
            // If we're recovering under the original location
            // check if it exists, to return "403 Forbidden" in case it does not
            try
            {
                $this->locationService->loadLocation( $trashItem->parentLocationId );
            }
            catch ( NotFoundException $e )
            {
                throw new ForbiddenException( $e->getMessage() );
            }
        }

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
