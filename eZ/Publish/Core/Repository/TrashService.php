<?php
/**
 * @package eZ\Publish\Core\Repository
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\TrashService as TrashServiceInterface,

    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\Handler,

    eZ\Publish\API\Repository\Values\Content\Location,
    eZ\Publish\Core\Repository\Values\Content\TrashItem,
    eZ\Publish\API\Repository\Values\Content\TrashItem as APITrashItem,
    eZ\Publish\API\Repository\Values\Content\LocationCreateStruct,
    eZ\Publish\API\Repository\Values\Content\Query,

    eZ\Publish\SPI\Persistence\Content\Location\Trashed,

    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,

    eZ\Publish\API\Repository\Values\Content\SearchResult,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\Query\SortClause;

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

        $spiTrashItem = $this->persistenceHandler->trashHandler()->load( $trashItemId );
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

        $spiTrashItem = $this->persistenceHandler->trashHandler()->trashSubtree( $location->id );
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
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct $newParentLocation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the newly created or recovered location
     */
    public function recover( APITrashItem $trashItem, LocationCreateStruct $newParentLocation = null )
    {
        if ( !is_numeric( $trashItem->id ) )
            throw new InvalidArgumentValue( "id", $trashItem->id, "TrashItem" );

        if ( $newParentLocation === null && !is_numeric( $trashItem->parentLocationId ) )
            throw new InvalidArgumentValue( "parentLocationId", $trashItem->parentLocationId, "TrashItem" );

        if ( $newParentLocation !== null && !is_numeric( $newParentLocation->parentLocationId ) )
            throw new InvalidArgumentValue( "parentLocationId", $newParentLocation->parentLocationId, "LocationCreateStruct" );

        $newLocationId = $this->persistenceHandler->trashHandler()->untrashLocation(
            $trashItem->id,
            $newParentLocation ? $newParentLocation->parentLocationId : $trashItem->parentLocationId
        );

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
        // Persistence layer takes care of deleting content objects
        $this->persistenceHandler->trashHandler()->emptyTrash();
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

        $this->persistenceHandler->trashHandler()->emptyOne( $trashItem->id );
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
        if ( is_array( $query->sortClauses ) )
        {
            foreach ( $query->sortClauses as $sortClause )
            {
                if ( !$sortClause instanceof SortClause )
                    throw new InvalidArgumentValue( "query->sortClauses", "only instances of SortClause class are allowed" );
            }
        }

        $spiTrashItems = $this->persistenceHandler->trashHandler()->listTrashed(
            $query->criterion instanceof Criterion ? $query->criterion : null,
            $query->offset >= 0 ? (int) $query->offset : 0,
            $query->limit > 0 ? (int) $query->limit : null,
            is_array( $query->sortClauses ) ? $query->sortClauses : null
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
                'contentInfo'             => $contentInfo,
                'id'                      => $spiTrashItem->id,
                'priority'                => $spiTrashItem->priority,
                'hidden'                  => $spiTrashItem->hidden,
                'invisible'               => $spiTrashItem->invisible,
                'remoteId'                => $spiTrashItem->remoteId,
                'parentLocationId'        => $spiTrashItem->parentId,
                'pathString'              => $spiTrashItem->pathString,
                'modifiedSubLocationDate' => new \DateTime("{@$spiTrashItem->modifiedSubLocation}"),
                'depth'                   => $spiTrashItem->depth,
                'sortField'               => $spiTrashItem->sortField,
                'sortOrder'               => $spiTrashItem->sortOrder,
                //@todo: this has to be 0?
                'childCount'              => 0
            )
        );
    }
}
