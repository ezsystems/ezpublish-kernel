<?php
/**
 * File containing the TrashHandler implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace eZ\Publish\Core\Persistence\InMemory;
use eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler as TrashHandlerInterface,
    eZ\Publish\SPI\Persistence\Content\Location\Trashed as TrashedValue,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\Location as LocationValue,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
 */
class TrashHandler implements TrashHandlerInterface
{
    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to Handler object that created it.
     *
     * @param Handler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( Handler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function loadTrashItem( $id )
    {
        return $this->backend->load( 'Content\\Location\\Trashed', $id );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     * @todo Handle field types actions
     */
    public function trash( $locationId )
    {
        $trashedLocation = $this->trashLocation( $locationId );

        // Begin recursive call on children, if any
        $directChildren = $this->backend->find( 'Content\\Location', array( 'parentId' => $locationId ) );
        if ( !empty( $directChildren ) )
        {
            foreach ( $directChildren as $child )
            {
                $this->trash( $child->id );
            }
        }

        return $trashedLocation;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    private function trashLocation( $locationId )
    {
        $location = $this->handler->locationHandler()->load( $locationId );

        // First delete location from tree
        // If there are remaining locations for content, update the mainLocationId
        $this->backend->delete( 'Content\\Location', $locationId );
        $remainingLocations = $this->backend->find( 'Content\\Location', array( 'contentId' => $location->contentId ) );
        if ( !empty( $remainingLocations ) )
        {
            $this->backend->updateByMatch(
                'Content\\Location',
                array( 'contentId' => $location->contentId ),
                array( 'mainLocationId' => $remainingLocations[0]->id )
            );
        }

        $this->updateSubtreeModificationTime( $this->getParentPathString( $location->pathString ) );

        // Create new trashed location and return it
        $params = (array)$location;
        return $this->backend->create( 'Content\\Location\\Trashed', $params, false );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     * @todo Handle field types actions
     */
    public function recover( $trashedId, $newParentId )
    {
        $trashedLocation = $this->loadTrashItem( $trashedId );
        $newParent = $this->handler->locationHandler()->load( $newParentId );

        // Restore location under $newParent
        $struct = new CreateStruct;
        foreach ( $struct as $property => $value )
        {
            if ( isset( $trashedLocation->$property ) )
            {
                $struct->$property = $trashedLocation->$property;
            }
        }

        $struct->parentId = $newParent->id;
        return $this->handler->locationHandler()->create( $struct )->id;
    }

    /**
     * Limited implementation (no criterion/sort support).
     * Will return all trashed locations, regardless criterion filter or sort clauses provided.
     * Offset/Limit is however supported
     *
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function findTrashItems( Criterion $criterion = null, $offset = 0, $limit = null, array $sort = null )
    {
        return array_slice(
            $this->backend->find( 'Content\\Location\\Trashed' ),
            $offset,
            $limit
        );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function emptyTrash()
    {
        $trashedIds = array();
        $contentIds = array();
        foreach ( $this->backend->find( 'Content\\Location\\Trashed' ) as $trashed )
        {
            $trashedIds[] = $trashed->id;
            $contentIds[] = $trashed->contentId;
        }

        if ( !empty( $trashedIds ) )
        {
            // Remove associated content for trashed locations
            foreach ( $contentIds as $contentId )
            {
                $this->handler->contentHandler()->deleteContent( $contentId );
            }

            // Remove trashed locations
            $this->backend->deleteByMatch( 'Content\\Location\\Trashed', array( 'id' => $trashedIds ) );
        }
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function deleteTrashItem( $trashedId )
    {
        $vo = $this->loadTrashItem( $trashedId );
        $this->handler->contentHandler()->deleteContent( $vo->contentId );
        $this->backend->delete( 'Content\\Location\\Trashed', $trashedId );
    }

    /**
     * Updates subtree modification time for all locations starting from $startPathString
     * @param string $startPathString
     */
    private function updateSubtreeModificationTime( $startPathString )
    {
        $this->backend->updateByMatch(
            'Content\\Location',
            array( 'pathString' => $startPathString . '%' ),
            array( 'modifiedSubLocation' => time() )
        );
    }

    /**
     * Returns parent path string for $pathString
     * @param string $pathString
     * @return string
     */
    private function getParentPathString( $pathString )
    {
        return substr( $pathString, 0, -2 );
    }
}
