<?php
/**
 * File containing the eZ\Publish\Core\Repository\TrashService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\TrashService as TrashServiceInterface,

    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\Handler,

    eZ\Publish\API\Repository\Values\Content\Location,
    eZ\Publish\Core\Repository\Values\Content\TrashItem,
    eZ\Publish\API\Repository\Values\Content\TrashItem as APITrashItem,
    eZ\Publish\API\Repository\Values\Content\Query,

    eZ\Publish\SPI\Persistence\Content\Location\Trashed,

    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,

    eZ\Publish\API\Repository\Values\Content\SearchResult,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\Query\SortClause,
    DateTime;

/**
 * Trash service, used for managing trashed content
 *
 * @package eZ\Publish\Core\Repository
 */
class TrashService implements TrashServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository  $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $handler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
        $this->settings = $settings;
    }

    /**
     * Loads a trashed location object from its $id.
     *
     * Note that $id is identical to original location, which has been previously trashed
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to read the trashed location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the location with the given id does not exist
     *
     * @param integer $trashItemId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    public function loadTrashItem( $trashItemId )
    {
        if ( !is_numeric( $trashItemId ) )
            throw new InvalidArgumentValue( "trashItemId", $trashItemId );

        $spiTrashItem = $this->persistenceHandler->trashHandler()->loadTrashItem( $trashItemId );
        return $this->buildDomainTrashItemObject( $spiTrashItem );
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
        if ( !is_numeric( $location->id ) )
            throw new InvalidArgumentValue( "id", $location->id, "Location" );

        $this->repository->beginTransaction();
        try
        {
            $spiTrashItem = $this->persistenceHandler->trashHandler()->trash( $location->id );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainTrashItemObject( $spiTrashItem );
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
        if ( !is_numeric( $trashItem->id ) )
            throw new InvalidArgumentValue( "id", $trashItem->id, "TrashItem" );

        if ( $newParentLocation === null && !is_numeric( $trashItem->parentLocationId ) )
            throw new InvalidArgumentValue( "parentLocationId", $trashItem->parentLocationId, "TrashItem" );

        if ( $newParentLocation !== null && !is_numeric( $newParentLocation->id ) )
            throw new InvalidArgumentValue( "parentLocationId", $newParentLocation->id, "Location" );

        $this->repository->beginTransaction();
        try
        {
            $newLocationId = $this->persistenceHandler->trashHandler()->recover(
                $trashItem->id,
                $newParentLocation ? $newParentLocation->id : $trashItem->parentLocationId
            );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->repository->getLocationService()->loadLocation( $newLocationId );
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
        $this->repository->beginTransaction();
        try
        {
            // Persistence layer takes care of deleting content objects
            $this->persistenceHandler->trashHandler()->emptyTrash();
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
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
        if ( !is_numeric( $trashItem->id ) )
            throw new InvalidArgumentValue( "id", $trashItem->id, "TrashItem" );

        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->trashHandler()->deleteTrashItem( $trashItem->id );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
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
        if ( $query->criterion !== null && !$query->criterion instanceof Criterion )
            throw new InvalidArgumentValue( "query->criterion", $query->criterion, "Query" );

        if ( $query->sortClauses !== null )
        {
            if ( !is_array( $query->sortClauses ) )
                throw new InvalidArgumentValue( "query->sortClauses", $query->sortClauses, "Query" );

            foreach ( $query->sortClauses as $sortClause )
            {
                if ( !$sortClause instanceof SortClause )
                    throw new InvalidArgumentValue( "query->sortClauses", "only instances of SortClause class are allowed" );
            }
        }

        if ( $query->offset !== null && !is_numeric( $query->offset ) )
            throw new InvalidArgumentValue( "query->offset", $query->offset, "Query" );

        if ( $query->limit !== null && !is_numeric( $query->limit ) )
            throw new InvalidArgumentValue( "query->limit", $query->limit, "Query" );

        $spiTrashItems = $this->persistenceHandler->trashHandler()->findTrashItems(
            $query->criterion !== null ? $query->criterion : null,
            $query->offset !== null && $query->offset > 0 ? (int) $query->offset : 0,
            $query->limit !== null && $query->limit >= 1 ? (int) $query->limit : null,
            $query->sortClauses !== null ? $query->sortClauses : null
        );

        $trashItems = array();
        foreach ( $spiTrashItems as $spiTrashItem )
        {
            $trashItems[] = $this->buildDomainTrashItemObject( $spiTrashItem );
        }

        $searchResult = new SearchResult();
        $searchResult->count = count( $trashItems );
        $searchResult->items = $trashItems;
        $searchResult->query = $query;

        return $searchResult;
    }

    /**
     * Builds the domain TrashItem object from provided persistence trash item
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Trashed $spiTrashItem
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    protected function buildDomainTrashItemObject( Trashed $spiTrashItem )
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo( $spiTrashItem->contentId );

        return new TrashItem(
            array(
                'contentInfo' => $contentInfo,
                'id' => (int) $spiTrashItem->id,
                'priority' => (int) $spiTrashItem->priority,
                'hidden' => (bool) $spiTrashItem->hidden,
                'invisible' => (bool) $spiTrashItem->invisible,
                'remoteId' => $spiTrashItem->remoteId,
                'parentLocationId' => (int) $spiTrashItem->parentId,
                'pathString' => $spiTrashItem->pathString,
                'modifiedSubLocationDate' => $this->getDateTime( $spiTrashItem->modifiedSubLocation ),
                'depth' => (int) $spiTrashItem->depth,
                'sortField' => (int) $spiTrashItem->sortField,
                'sortOrder' => (int) $spiTrashItem->sortOrder,
                //@todo: this has to be 0?
                'childCount' => 0
            )
        );
    }

    /**
     *
     *
     * @param int|null $timestamp
     *
     * @return \DateTime|null
     */
    protected function getDateTime( $timestamp )
    {
        $dateTime = new DateTime();
        $dateTime->setTimestamp( $timestamp );
        return $dateTime;
    }
}
