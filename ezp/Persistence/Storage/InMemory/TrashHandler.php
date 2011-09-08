<?php
/**
 * File containing the TrashHandler implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\InMemory;
use ezp\Persistence\Content\Location\Trash\Handler as TrashHandlerInterface,
    ezp\Persistence\Content\Location\Trashed as TrashedValue,
    ezp\Persistence\Content\Location\CreateStruct,
    ezp\Persistence\Content\Location as LocationValue,
    ezp\Persistence\Content\Criterion,
    ezp\Base\Exception\NotFound,
    ezp\Content\Location\Exception\ParentNotFound;

/**
 * @see ezp\Persistence\Content\Location\Trash\Handler
 */
class TrashHandler implements TrashHandlerInterface
{
    /**
     * @var RepositoryHandler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to RepositoryHandler object that created it.
     *
     * @param RepositoryHandler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( RepositoryHandler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

    /**
     * @see ezp\Persistence\Content\Location\Trash\Handler
     */
    public function load( $id )
    {
        return $this->backend->load( 'Content\\Location\\Trashed', $id );
    }

    /**
     * @see ezp\Persistence\Content\Location\Trash\Handler
     */
    public function loadFromLocationId( $locationId )
    {
        $aTrashed = $this->backend->find( 'Content\\Location\\Trashed', array( 'locationId' => $locationId ) );
        if ( empty( $aTrashed ) )
            return;
        return $aTrashed[0];
    }

    /**
     * @see ezp\Persistence\Content\Location\Trash\Handler
     */
    public function trashSubtree( $locationId )
    {
        $location = $this->loadFromLocationId( $locationId );
        $trashedLocation = $this->trash( $locationId );

        // Begin recursive call on children, if any
        $directChildren = $this->backend->find( 'Content\\Location', array( 'parentId' => $locationId ) );
        if ( !empty( $directChildren ) )
        {
            foreach ( $directChildren as $child )
            {
                $this->trashSubtree( $child->id );
            }
        }

        return $trashedLocation;
    }

    /**
     * @see ezp\Persistence\Content\Location\Trash\Handler
     */
    private function trash( $locationId )
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
        $params['locationId'] = $locationId;
        // Be sure to not overlap id
        unset( $params['id'] );
        return $this->backend->create( 'Content\\Location\\Trashed', $params );
    }

    /**
     * @see ezp\Persistence\Content\Location\Trash\Handler
     */
    public function untrashLocation( $trashedId, $newParentId )
    {
        $trashedLocation = $this->load( $trashedId );
        try
        {
            $newParent = $this->handler->locationHandler()->load( $newParentId );
        }
        catch ( NotFound $e )
        {
            throw new ParentNotFound( $trashedLocation->locationId, $newParentId, $e );
        }

        // Restore location under $newParent
        $struct = new CreateStruct;
        foreach ( $struct as $property => $value )
        {
            if ( isset( $trashedLocation->$property ) )
            {
                $struct->$property = $trashedLocation->$property;
            }
        }

        $struct->parentId = $newParentId;
        return $this->handler->locationHandler()->create( $struct )->id;
    }

    /**
     * Limited implementation (no criterion/sort support).
     * Will return all trashed locations, regardless criterion filter or sort clauses provided.
     * Offset/Limit is however supported
     *
     * @see ezp\Persistence\Content\Location\Trash\Handler
     */
    public function listTrashed( Criterion $criterion = null, $offset = 0, $limit = null, array $sort = null )
    {
        return array_splice(
            $this->backend->find( 'Content\\Location\\Trashed' ),
            $offset,
            $limit
        );
    }

    /**
     * @see ezp\Persistence\Content\Location\Trash\Handler
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
                $this->handler->contentHandler()->delete( $contentId );
            }

            // Remove trashed locations
            $this->backend->deleteByMatch( 'Content\\Location\\Trashed', array( 'id' => $trashedIds ) );
        }
    }

    /**
     * @see ezp\Persistence\Content\Location\Trash\Handler
     */
    public function emptyOne( $trashedId )
    {
        $vo = $this->load( $trashedId );
        $this->handler->contentHandler()->delete( $vo->contentId );
        $this->backend->delete( 'Content\\Location\\Trashed' , $trashedId );
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
