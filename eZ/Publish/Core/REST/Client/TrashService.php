<?php
/**
 * File containing the TrashService class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\TrashService as APITrashService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\TrashItem as APITrashItem;
use eZ\Publish\Core\Repository\Values\Content\TrashItem;

use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input\Dispatcher;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Common\Message;

/**
 * Trash service used for content/location trash handling.
 *
 * @package eZ\Publish\API\Repository
 */
class TrashService implements APITrashService, Sessionable
{
    /**
     * @var \eZ\Publish\Core\REST\Client\LocationService
     */
    private $locationService;

    /**
     * @var \eZ\Publish\Core\REST\Client\HttpClient
     */
    private $client;

    /**
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    private $inputDispatcher;

    /**
     * @var \eZ\Publish\Core\REST\Common\Output\Visitor
     */
    private $outputVisitor;

    /**
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    private $urlHandler;

    /**
     * @param \eZ\Publish\Core\REST\Client\LocationService $locationService
     * @param \eZ\Publish\Core\REST\Client\HttpClient $client
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $outputVisitor
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     */
    public function __construct( LocationService $locationService, HttpClient $client, Dispatcher $inputDispatcher, Visitor $outputVisitor, UrlHandler $urlHandler )
    {
        $this->locationService = $locationService;
        $this->client          = $client;
        $this->inputDispatcher = $inputDispatcher;
        $this->outputVisitor   = $outputVisitor;
        $this->urlHandler      = $urlHandler;
    }

    /**
     * Set session ID
     *
     * Only for testing
     *
     * @param mixed $id
     */
    public function setSession( $id )
    {
        if ( $this->outputVisitor instanceof Sessionable )
        {
            $this->outputVisitor->setSession( $id );
        }
    }

    /**
     * Loads a trashed location object from its $id.
     *
     * Note that $id is identical to original location, which has been previously trashed
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to read the trashed location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the location with the given id does not exist
     *
     * @param int $trashItemId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    public function loadTrashItem( $trashItemId )
    {
        $response = $this->client->request(
            'GET',
            $trashItemId,
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'Location' ) )
            )
        );

        $location = $this->inputDispatcher->parse( $response );
        return $this->buildTrashItem( $location );
    }

    /**
     * Sends $location and all its children to trash and returns the corresponding trash item.
     *
     * Content is left untouched.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to trash the given location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    public function trash( Location $location )
    {
        throw new \Exception( "@todo: Implement." );
    }

    /**
     * Recovers the $trashedLocation at its original place if possible.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to recover the trash item at the parent location location
     *
     * If $newParentLocation is provided, $trashedLocation will be restored under it.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem
     * @param \eZ\Publish\API\Repository\Values\Content\Location $newParentLocation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the newly created or recovered location
     */
    public function recover( APITrashItem $trashItem, Location $newParentLocation = null )
    {
        throw new \Exception( "@todo: Implement." );
    }

    /**
     * Empties trash.
     *
     * All locations contained in the trash will be removed. Content objects will be removed
     * if all locations of the content are gone.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to empty the trash
     */
    public function emptyTrash()
    {
        $response = $this->client->request(
            'DELETE',
            $this->urlHandler->generate( 'trashItems' ),
            new Message(
                // @todo: What media-type should we set here? Actually, it should be
                // all expected exceptions + none? Or is "Location" correct,
                // since this is what is to be expected by the resource
                // identified by the URL?
                array( 'Accept' => $this->outputVisitor->getMediaType( 'Location' ) )
            )
        );

        if ( !empty( $response->body ) )
            $this->inputDispatcher->parse( $response );
    }

    /**
     * Deletes a trash item.
     *
     * The corresponding content object will be removed
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete this trash item
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem
     */
    public function deleteTrashItem( APITrashItem $trashItem )
    {
        $response = $this->client->request(
            'DELETE',
            $trashItem->id,
            new Message(
                // @todo: What media-type should we set here? Actually, it should be
                // all expected exceptions + none? Or is "Location" correct,
                // since this is what is to be expected by the resource
                // identified by the URL?
                array( 'Accept' => $this->outputVisitor->getMediaType( 'Location' ) )
            )
        );

        if ( !empty( $response->body ) )
            $this->inputDispatcher->parse( $response );
    }

    /**
     * Returns a collection of Trashed locations contained in the trash.
     *
     * $query allows to filter/sort the elements to be contained in the collection.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SearchResult
     */
    public function findTrashItems( Query $query )
    {
        $response = $this->client->request(
            'GET',
            $this->urlHandler->generate( 'trashItems' ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'LocationList' ) )
            )
        );

        $locations = $this->inputDispatcher->parse( $response );

        $trashItems = array();
        foreach ( $locations as $location )
        {
            $trashItems[] = $this->buildTrashItem( $location );
        }
        return $trashItems;
    }

    /**
     * Converts the Location value object to TrashItem value object
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    protected function buildTrashItem( Location $location )
    {
        return new TrashItem(
            array(
                'contentInfo' => $location->contentInfo,
                'id' => $location->id,
                'priority' => $location->priority,
                'hidden' => $location->hidden,
                'invisible' => $location->invisible,
                'remoteId' => $location->remoteId,
                'parentLocationId' => $location->parentLocationId,
                'pathString' => $location->pathString,
                'depth' => (int)$location->depth,
                'sortField' => $location->sortField,
                'sortOrder' => $location->sortOrder,
            )
        );
    }
}
