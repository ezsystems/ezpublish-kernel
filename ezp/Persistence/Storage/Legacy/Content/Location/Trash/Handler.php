<?php
/**
 * File containing the Location Handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Location\Trash;
use ezp\Persistence\Content\Location\Trash,
    ezp\Persistence\Content\Location\Trashed,
    ezp\Persistence\Content\Location\Trash\CreateStruct,
    ezp\Persistence\Content\Location\Trash\UpdateStruct,
    ezp\Persistence\Content\Location\Trash\Handler as BaseTrashHandler,
    ezp\Persistence\Content\Query\Criterion,
    ezp\Persistence\Storage\Legacy\Content\Location\Gateway as LocationGateway,
    ezp\Persistence\Storage\Legacy\Content\Location\Mapper as LocationMapper;

/**
 * The Location Handler interface defines operations on Location elements in the storage engine.
 */
class Handler implements BaseTrashHandler
{
    /**
     * Gaateway for handling location data
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Mapper for handling location data
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Mapper
     */
    protected $locationMapper;

    /**
     * Construct from userGateway
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Location\Gateway $locationGateway
     * @param \ezp\Persistence\Storage\Legacy\Content\Location\Mapper $locationMapper
     * @return void
     */
    public function __construct( LocationGateway $locationGateway, LocationMapper $locationMapper )
    {
        $this->locationGateway = $locationGateway;
        $this->locationMapper  = $locationMapper;
    }

    /**
     * Loads the data for the trashed location identified by $id.
     *
     * @param int $id
     * @return \ezp\Persistence\Content\Location\Trashed
     */
    public function load( $id )
    {
        throw new \RuntimeException( '@TODO: There is no such thing as a trash ID -- pending clarification.' );
    }

    /**
     * Loads the data for the trashed location identified by $locationId.
     * $locationId is the original Id for trashed location
     *
     * @param int $locationId
     * @return \ezp\Persistence\Content\Location\Trashed
     */
    public function loadFromLocationId( $locationId )
    {
        $data = $this->locationGateway->loadTrashByLocation( $locationId );
        return $this->locationMapper->createLocationFromRow( $data, null, new Trashed() );
    }

    /**
     * Sends a subtree to the trash
     *
     * Moves all locations in the subtree to the Trash. The associated content
     * objects are left untouched.
     *
     * @param mixed $locationId
     * @return boolean
     */
    public function trashSubtree( $locationId )
    {
        $sourceNodeData = $this->locationGateway->getBasicNodeData( $locationId );

        $this->locationGateway->trashSubtree( $sourceNodeData['path_string'] );
    }

    /**
     * Returns a trashed location to normal state.
     *
     * Recreates the originally trashed location in the new position.
     * If this is not possible (because the old location does not exist any more),
     * a ParentNotFound exception is thrown.
     *
     * Returns newly restored location Id.
     *
     * @param mixed $locationId
     * @param mixed $newParentId
     * @return int Newly restored location id
     * @throws \ezp\Content\Location\Exception\ParentNotFound
     */
    public function untrashLocation( $trashedId, $newParentId )
    {
        $this->locationGateway->untrashLocation( $trashedId, $newParentId );
    }

    /**
     * Returns an array of all trashed locations satisfying the $criterion (if provided),
     * sorted with SortClause objects contained in $sort (if any).
     * If no criterion is provided (null), no filter is applied
     *
     * @param \ezp\Persistence\Content\Query\Criterion $criterion
     * @param $offset Offset to start listing from, 0 by default
     * @param $limit Limit for the listing. Null by default (no limit)
     * @param \ezp\Persistence\Content\Query\SortClause[] $sort
     * @return \ezp\Persistence\Content\Location\Trashed[]
     */
    public function listTrashed( Criterion $criterion = null, $offset = 0, $limit = null, array $sort = null )
    {
        // CBA: Ignore criterion for now.
        throw new \RuntimeException( '@TODO: Implement.' );
    }

    /**
     * Empties the trash
     * Everything contained in the trash must be removed
     */
    public function emptyTrash()
    {
        throw new \RuntimeException( '@TODO: Implement.' );
    }

    /**
     * Removes a trashed location identified by $trashedLocationId from trash
     * Associated content has to be deleted
     *
     * @param int $trashedId
     */
    public function emptyOne( $trashedId )
    {
        throw new \RuntimeException( '@TODO: Implement.' );
    }
}

