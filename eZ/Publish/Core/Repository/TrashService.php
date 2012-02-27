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

    ezp\Base\Exception\NotFound,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,

    eZ\Publish\API\Repository\Values\Content\SearchResult,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator,
    eZ\Publish\API\Repository\Values\Content\Query\SortClause,
    eZ\Publish\API\Repository\Values\Content\Query\SortClause\FieldSortClause;

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
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository  $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     */
    public function __construct( RepositoryInterface $repository, Handler $handler )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
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

        try
        {
            $spiTrashItem = $this->persistenceHandler->trashHandler()->load( $trashItemId );
        }
        catch ( NotFound $e )
        {
            throw new NotFoundException( "trashed location", $trashItemId, $e );
        }

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

        try
        {
            $spiTrashItem = $this->persistenceHandler->trashHandler()->trashSubtree( $location->id );
        }
        catch ( NotFound $e )
        {
            throw new NotFoundException( "location", $location->id, $e );
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

        try
        {
            $locationParentId = $newParentLocation !== null ? $newParentLocation->parentLocationId : $trashItem->parentLocationId;
            $newLocationId = $this->persistenceHandler->trashHandler()->untrashLocation( $trashItem->id, $locationParentId );
        }
        catch ( NotFound $e )
        {
            throw new NotFoundException( $e->what, $e->identifier, $e );
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

        try
        {
            // Persistence layer takes care of deleting corresponding content object
            $this->persistenceHandler->trashHandler()->emptyOne( $trashItem->id );
        }
        catch ( NotFound $e )
        {
            throw new NotFoundException( "trashed location", $trashItem->id, $e );
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
        $criterion = null;
        if ( $query->criterion instanceof Criterion )
            $criterion = $this->convertCriterion( $query->criterion );

        $sortClauses = null;
        if ( is_array( $query->sortClauses ) )
        {
            $sortClauses = array();
            foreach ( $query->sortClauses as $sortClause )
            {
                $sortClauses[] = $this->convertSortClause( $sortClause );
            }
        }

        $offset = $query->offset >= 0 ? (int) $query->offset : 0;
        $limit = $query->limit > 0 ? (int) $query->limit : null;

        $spiTrashItems = $this->persistenceHandler->trashHandler()->listTrashed( $criterion, $offset, $limit, $sortClauses );

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

        return new TrashItem( array(
            'contentInfo'              => $contentInfo,
            'contentId'                => $contentInfo->contentId,
            'id'                       => $spiTrashItem->id,
            'priority'                 => $spiTrashItem->priority,
            'hidden'                   => $spiTrashItem->hidden,
            'invisible'                => $spiTrashItem->invisible,
            'remoteId'                 => $spiTrashItem->remoteId,
            'parentLocationId'         => $spiTrashItem->parentId,
            'pathString'               => $spiTrashItem->pathString,
            'modifiedSubLocationDate'  => new \DateTime("{@$spiTrashItem->modifiedSubLocation}"),
            'depth'                    => $spiTrashItem->depth,
            'sortField'                => $spiTrashItem->sortField,
            'sortOrder'                => $spiTrashItem->sortOrder,
            'childCount'               => 0
        ) );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Query\Criterion
     */
    protected function convertCriterion( Criterion $criterion )
    {
        $criterionClass = get_class( $criterion );
        $persistenceCriterionClass = explode( "\\", $criterionClass );
        $persistenceCriterionClass = "eZ\\Publish\\SPI\\Persistence\\Content\\Query\\Criterion\\" .
                                     $persistenceCriterionClass[count( $persistenceCriterionClass ) - 1];

        /** @var $persistenceCriterionClass \eZ\Publish\SPI\Persistence\Content\Query\Criterion */
        if ( !class_exists( $persistenceCriterionClass ) )
            throw new InvalidArgumentException( "criterion", "Criterion $persistenceCriterionClass not found" );

        if ( $criterion instanceof LogicalOperator )
        {
            /** @var $criterion \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator */
            $reflectionMethod = new \ReflectionMethod( $persistenceCriterionClass, "__construct" );
            $params = $reflectionMethod->getParameters();
            if ( empty( $params ) )
                return new $persistenceCriterionClass();

            if ( $params[0]->isArray() )
            {
                $criteria = array();
                foreach ( $criterion->criteria as $singleCriterion )
                {
                    $criteria[] = $this->convertCriterion( $singleCriterion );
                }
            }
            else
            {
                $criteria = $this->convertCriterion( $criterion->criteria[0] );
            }

            return new $persistenceCriterionClass( $criteria );
        }

        return $persistenceCriterionClass::createFromQueryBuilder( $criterion->target, $criterion->operator, $criterion->value );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Query\SortClause
     */
    protected function convertSortClause( SortClause $sortClause )
    {
        $sortClauseClass = get_class( $sortClause );
        $persistenceSortClauseClass = explode( "\\", $sortClauseClass );
        $persistenceSortClauseClass = "eZ\\Publish\\SPI\\Persistence\\Content\\Query\\SortClause\\" .
                                      $persistenceSortClauseClass[count( $persistenceSortClauseClass ) - 1];

        if ( !class_exists( $persistenceSortClauseClass ) )
            throw new InvalidArgumentException( "sort clause", "Sort clause $persistenceSortClauseClass not found" );

        if ( $sortClause instanceof FieldSortClause )
        {
            $targetData = $sortClause->targetData;
            return new $persistenceSortClauseClass( $targetData->typeIdentifier, $targetData->fieldIdentifier, $sortClause->direction );
        }

        return new $persistenceSortClauseClass( $sortClause->direction );
    }
}
