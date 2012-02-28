<?php
/**
 * @package eZ\Publish\Core\Repository
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct,
    eZ\Publish\API\Repository\Values\Content\LocationCreateStruct,
    eZ\Publish\API\Repository\Values\Content\ContentInfo,
    eZ\Publish\Core\Repository\Values\Content\Location,
    eZ\Publish\API\Repository\Values\Content\Location as APILocation,

    eZ\Publish\SPI\Persistence\Content\Location as SPILocation,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct,

    eZ\Publish\API\Repository\LocationService as LocationServiceInterface,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\Handler,

    eZ\Publish\SPI\Persistence\Content\Query\Criterion\LogicalAnd as CriterionLogicalAnd,
    eZ\Publish\SPI\Persistence\Content\Query\Criterion\ContentId as CriterionContentId,
    eZ\Publish\SPI\Persistence\Content\Query\Criterion\Status as CriterionStatus,
    eZ\Publish\SPI\Persistence\Content\Query\Criterion\ParentLocationId as CriterionParentLocationId,
    eZ\Publish\SPI\Persistence\Content\Query\Criterion\LocationRemoteId as CriterionLocationRemoteId,

    ezp\Base\Exception\NotFound,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\Base\Exceptions\IllegalArgumentException,
    eZ\Publish\Core\Base\Exceptions\BadStateException;

/**
 * Location service, used for complex subtree operations
 *
 * @example Examples/location.php
 *
 * @package eZ\Publish\Core\Repository
 */
class LocationService implements LocationServiceInterface
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
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     */
    public function __construct( RepositoryInterface $repository, Handler $handler )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
    }

    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     *
     * Only the items on which the user has read access are copied.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed copy the subtree to the given parent location
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException if the target location is a sub location of the given location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $subtree - the subtree denoted by the location to copy
     * @param \eZ\Publish\API\Repository\Values\Content\Location $targetParentLocation - the target parent location for the copy operation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location The newly created location of the copied subtree
     *
     * @todo enhancement - this method should return a result structure containing the new location and a list
     *       of locations which are not copied due to permission denials.
     */
    public function copySubtree( APILocation $subtree, APILocation $targetParentLocation )
    {
        if ( !is_numeric( $subtree->id ) )
            throw new InvalidArgumentValue( "id", $subtree->id, "Location" );

        if ( !is_numeric( $targetParentLocation->id ) )
            throw new InvalidArgumentValue( "id", $targetParentLocation->id, "Location" );

        $loadedSubtree = $this->loadLocation( $subtree->id );
        $loadedTargetLocation = $this->loadLocation( $targetParentLocation->id );

        if ( stripos( $loadedTargetLocation->pathString, $loadedSubtree->pathString ) !== false )
            throw new IllegalArgumentException("targetParentLocation", "target parent location is a sub location of the given subtree");

        $newLocation = $this->persistenceHandler->locationHandler()->copySubtree( $loadedSubtree->id, $loadedTargetLocation->id );
        return $this->buildDomainLocationObject( $newLocation );
    }

    /**
     * Loads a location object from its $locationId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read this location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified location is not found
     *
     * @param integer $locationId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function loadLocation( $locationId )
    {
        if ( !is_numeric( $locationId ) )
            throw new InvalidArgumentValue( "locationId", $locationId );

        try
        {
            $spiLocation = $this->persistenceHandler->locationHandler()->load( $locationId );
        }
        catch ( NotFound $e )
        {
            throw new NotFoundException( "location", $locationId, $e );
        }

        return $this->buildDomainLocationObject( $spiLocation );
    }

    /**
     * Loads a location object from its $remoteId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read this location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified location is not found
     *
     * @param string $remoteId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function loadLocationByRemoteId( $remoteId )
    {
        if ( !is_string( $remoteId ) )
            throw new InvalidArgumentValue( "remoteId", $remoteId );

        $searchCriterion = new CriterionLogicalAnd( array(
            new CriterionStatus( CriterionStatus::STATUS_PUBLISHED ),
            new CriterionLocationRemoteId( $remoteId )
        ) );

        $searchResult = $this->persistenceHandler->searchHandler()->find( $searchCriterion );

        if ( !$searchResult || $searchResult->count != 1 )
            throw new NotFoundException( "location", $remoteId );

        if ( is_array( $searchResult->content[0]->locations ) )
        {
            foreach ( $searchResult->content[0]->locations as $spiLocation )
            {
                if ( $spiLocation->remoteId == $remoteId )
                    return $this->buildDomainLocationObject( $spiLocation );
            }
        }

        throw new NotFoundException( "location", $remoteId );
    }

    /**
     * loads the main location of a content object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read this location
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if there is no published version yet
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location|null Null if no location exists
     */
    public function loadMainLocation( ContentInfo $contentInfo )
    {
        if ( !is_numeric( $contentInfo->contentId ) )
            throw new InvalidArgumentValue( "contentId", $contentInfo->contentId, "ContentInfo" );

        $searchCriterion = new CriterionLogicalAnd( array(
            new CriterionContentId( $contentInfo->contentId ),
            new CriterionStatus( CriterionStatus::STATUS_PUBLISHED ),
        ) );

        $searchResult = $this->persistenceHandler->searchHandler()->find( $searchCriterion );

        if ( !$searchResult || $searchResult->count == 0 )
            throw new BadStateException( "contentInfo" );

        $spiLocations = $searchResult->content[0]->locations;
        if ( !is_array( $spiLocations ) || empty( $spiLocations ) )
            return null;

        foreach ( $spiLocations as $spiLocation )
        {
            if ( $spiLocation->id == $spiLocation->mainLocationId )
                return $this->buildDomainLocationObject( $spiLocation );
        }

        return null;
    }

    /**
     * Loads the locations for the given content object.
     *
     * If a $rootLocation is given, only locations that belong to this location are returned.
     * The location list is also filtered by permissions on reading locations.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if there is no published version yet
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Location $rootLocation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public function loadLocations( ContentInfo $contentInfo, APILocation $rootLocation = null )
    {
        if ( !is_numeric( $contentInfo->contentId ) )
            throw new InvalidArgumentValue( "contentId", $contentInfo->contentId, "ContentInfo" );

        if ( $rootLocation !== null && !is_string( $rootLocation->pathString ) )
            throw new InvalidArgumentValue( "pathString", $rootLocation->pathString, "Location" );

        $searchCriterion = new CriterionLogicalAnd( array(
            new CriterionContentId( $contentInfo->contentId ),
            new CriterionStatus( CriterionStatus::STATUS_PUBLISHED ),
        ) );

        $searchResult = $this->persistenceHandler->searchHandler()->find( $searchCriterion );

        if ( !$searchResult || $searchResult->count == 0 )
            throw new BadStateException( "contentInfo" );

        $spiLocations = $searchResult->content[0]->locations;
        if ( !is_array( $spiLocations ) || empty( $spiLocations ) )
            return array();

        $contentLocations = array();
        foreach ( $spiLocations as $location )
        {
            if ( $rootLocation === null || stripos( $location->pathString, $rootLocation->pathString ) !== false )
                $contentLocations[] = $this->buildDomainLocationObject( $location );
        }

        return $contentLocations;
    }

    /**
     * Load children which are readable by the current user of a location object sorted by sortField and sortOrder
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @param int $offset the start offset for paging
     * @param int $limit the number of locations returned. If $limit = -1 all children starting at $offset are returned
     *
     * @return array Of {@link Location}
     */
    public function loadLocationChildren( APILocation $location, $offset = 0, $limit = -1 )
    {
        if ( !is_numeric( $location->id ) )
            throw new InvalidArgumentValue( "id", $location->id, "Location" );

        if ( !is_numeric( $location->sortField ) )
            throw new InvalidArgumentValue( "sortField", $location->sortField, "Location" );

        if ( !is_numeric( $location->sortOrder ) )
            throw new InvalidArgumentValue( "sortOrder", $location->sortOrder, "Location" );

        if ( !is_numeric( $offset ) )
            throw new InvalidArgumentValue( "offset", $offset );

        if ( !is_numeric( $limit ) )
            throw new InvalidArgumentValue( "limit", $limit );

        $searchResult = $this->searchChildrenLocations( $location->id, $location->sortField, $location->sortOrder, $offset, $limit );
        if ( !$searchResult || $searchResult->count == 0 )
            return array();

        $childLocations = array();
        foreach ( $searchResult->content as $spiContent )
        {
            if ( is_array( $spiContent->locations ) )
            {
                foreach ( $spiContent->locations as $spiLocation )
                {
                    if ( $spiLocation->parentId == $location->id )
                        $childLocations[] = $this->buildDomainLocationObject( $spiLocation );
                }
            }
        }

        return $childLocations;
    }

    /**
     * Searches children locations of the provided parent location id
     *
     * @param int $parentLocationId
     * @param int $sortField
     * @param int $sortOrder
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Result
     */
    protected function searchChildrenLocations( $parentLocationId, $sortField, $sortOrder, $offset = 0, $limit = -1 )
    {
        $searchCriterion = new CriterionLogicalAnd( array(
            new CriterionParentLocationId( $parentLocationId ),
            new CriterionStatus( CriterionStatus::STATUS_PUBLISHED ),
        ) );

        return $this->persistenceHandler->searchHandler()->find(
            $searchCriterion,
            $offset,
            $limit > 0 ? $limit : null,
            array( $this->getSortClauseBySortField( $sortField, $sortOrder ) )
        );
    }

    /**
     * Creates the new $location in the content repository for the given content
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create this location
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException if the content is already below the specified parent
     *                                        or the parent is a sub location of the location of the content
     *                                        or if set the remoteId exists already
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct $locationCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the newly created Location
     *
     */
    public function createLocation( ContentInfo $contentInfo, LocationCreateStruct $locationCreateStruct )
    {
        if ( !is_numeric( $contentInfo->contentId ) )
            throw new InvalidArgumentValue( "contentId", $contentInfo->contentId, "ContentInfo" );

        if ( !is_numeric( $contentInfo->currentVersionNo ) )
            throw new InvalidArgumentValue( "currentVersionNo", $contentInfo->currentVersionNo, "ContentInfo" );

        if ( !is_numeric( $locationCreateStruct->parentLocationId ) )
            throw new InvalidArgumentValue( "parentLocationId", $locationCreateStruct->parentLocationId, "LocationCreateStruct" );

        if ( $locationCreateStruct->priority !== null && !is_numeric( $locationCreateStruct->priority ) )
            throw new InvalidArgumentValue( "priority", $locationCreateStruct->priority, "LocationCreateStruct" );

        if ( $locationCreateStruct->hidden !== null && !is_bool( $locationCreateStruct->hidden ) )
            throw new InvalidArgumentValue( "hidden", $locationCreateStruct->hidden, "LocationCreateStruct" );

        if ( $locationCreateStruct->remoteId !== null && ( !is_string( $locationCreateStruct->remoteId ) || empty( $locationCreateStruct->remoteId ) ) )
            throw new InvalidArgumentValue( "remoteId", $locationCreateStruct->remoteId, "LocationCreateStruct" );

        if ( $locationCreateStruct->sortField !== null && !is_numeric( $locationCreateStruct->sortField ) )
            throw new InvalidArgumentValue( "sortField", $locationCreateStruct->sortField, "LocationCreateStruct" );

        if ( $locationCreateStruct->sortOrder !== null && !is_numeric( $locationCreateStruct->sortOrder ) )
            throw new InvalidArgumentValue( "sortOrder", $locationCreateStruct->sortOrder, "LocationCreateStruct" );

        // check for existence of location with provided remote ID
        if ( $locationCreateStruct->remoteId !== null )
        {
            try
            {
                $existingLocation = $this->loadLocationByRemoteId( $locationCreateStruct->remoteId );
                if ( $existingLocation !== null )
                    throw new IllegalArgumentException( "locationCreateStruct", "location with provided remote ID already exists" );
            }
            catch ( NotFoundException $e ) {}
        }
        else
        {
            $locationCreateStruct->remoteId = md5( uniqid( get_class( $this ), true ) );
        }

        $loadedParentLocation = $this->loadLocation( $locationCreateStruct->parentLocationId );

        // check if the content already has location below specified parent ID
        $searchResult = $this->persistenceHandler->searchHandler()->find( new CriterionLogicalAnd( array(
            new CriterionContentId( $contentInfo->contentId ),
            new CriterionParentLocationId( $loadedParentLocation->id ),
        ) ) );

        if ( $searchResult->count > 0 )
            throw new IllegalArgumentException( "contentInfo", "content is already below the specified parent" );

        // check if the parent is a sub location of one of the existing content locations
        // this also solves the situation where parent location actually one of the content locations
        // NOTE: In 4.x it IS possible to add a location to the object below a parent which is below the existing content locations
        $existingContentLocations = $this->loadLocations( $contentInfo );

        if ( !empty( $existingContentLocations ) )
        {
            foreach ( $existingContentLocations as $existingContentLocation )
            {
                if ( stripos( $existingContentLocation->pathString, $loadedParentLocation->pathString ) !== false )
                    throw new IllegalArgumentException( "locationCreateStruct", "specified parent is a sub location of one of the existing content locations" );
            }
        }

        $createStruct = new CreateStruct();
        $createStruct->priority = $locationCreateStruct->priority === null ?: (int) $locationCreateStruct->priority;

        // if we declare the new location as hidden, it is automatically invisible
        // otherwise, it remains unhidden, and picks up visibility from parent
        if ( $locationCreateStruct->hidden === true )
        {
            $createStruct->hidden = true;
            $createStruct->invisible = true;
        }
        else
        {
            try
            {
                $parentParentLocation = $this->loadLocation( $loadedParentLocation->parentLocationId );
                if ( $parentParentLocation->hidden || $parentParentLocation->invisible )
                    $createStruct->invisible = true;
            }
            catch ( NotFoundException $e ) {}
        }

        $createStruct->remoteId = trim( $locationCreateStruct->remoteId );
        $createStruct->contentId = (int) $contentInfo->contentId;
        $createStruct->contentVersion = (int) $contentInfo->currentVersionNo;

        $createStruct->sortField = $locationCreateStruct->sortField === null ? APILocation::SORT_FIELD_NAME : (int) $locationCreateStruct->sortField;
        $createStruct->sortOrder = $locationCreateStruct->sortOrder === null ? APILocation::SORT_ORDER_ASC : (int) $locationCreateStruct->sortOrder;
        $createStruct->parentId = $loadedParentLocation->id;

        $newLocation = $this->persistenceHandler->locationHandler()->create( $createStruct );
        return $this->buildDomainLocationObject( $newLocation );
    }

    /**
     * Updates $location in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to update this location
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException   if if set the remoteId exists already
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct $locationUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the updated Location
     */
    public function updateLocation( APILocation $location, LocationUpdateStruct $locationUpdateStruct )
    {
        if ( !is_numeric( $location->id ) )
            throw new InvalidArgumentValue( "id", $location->id, "Location" );

        if ( $locationUpdateStruct->priority !== null && !is_numeric( $locationUpdateStruct->priority ) )
            throw new InvalidArgumentValue( "priority", $locationUpdateStruct->priority, "LocationUpdateStruct" );

        if ( $locationUpdateStruct->remoteId !== null && ( !is_string( $locationUpdateStruct->remoteId ) || empty( $locationUpdateStruct->remoteId ) ) )
            throw new InvalidArgumentValue( "remoteId", $locationUpdateStruct->remoteId, "LocationUpdateStruct" );

        if ( $locationUpdateStruct->sortField !== null && !is_numeric( $locationUpdateStruct->sortField ) )
            throw new InvalidArgumentValue( "sortField", $locationUpdateStruct->sortField, "LocationUpdateStruct" );

        if ( $locationUpdateStruct->sortOrder !== null && !is_numeric( $locationUpdateStruct->sortOrder ) )
            throw new InvalidArgumentValue( "sortOrder", $locationUpdateStruct->sortOrder, "LocationUpdateStruct" );

        $loadedLocation = $this->loadLocation( $location->id );

        if ( $locationUpdateStruct->remoteId !== null )
        {
            try
            {
                $existingLocation = $this->loadLocationByRemoteId( $locationUpdateStruct->remoteId );
                if ( $existingLocation !== null )
                    throw new IllegalArgumentException( "locationUpdateStruct", "location with provided remote ID already exists" );
            }
            catch ( NotFoundException $e ) {}
        }

        $updateStruct = new UpdateStruct();
        $updateStruct->priority = $locationUpdateStruct->priority !== null ? (int) $locationUpdateStruct->priority : $loadedLocation->priority;
        $updateStruct->remoteId = $locationUpdateStruct->remoteId !== null ? trim( $locationUpdateStruct->remoteId ) : $loadedLocation->remoteId;
        $updateStruct->sortField = $locationUpdateStruct->sortField !== null ? (int) $locationUpdateStruct->sortField : $loadedLocation->sortField;
        $updateStruct->sortOrder = $locationUpdateStruct->sortOrder !== null ? (int) $locationUpdateStruct->sortOrder : $loadedLocation->sortOrder;

        $this->persistenceHandler->locationHandler()->update( $updateStruct, $loadedLocation->id );

        return $this->loadLocation( $loadedLocation->id );
    }

    /**
     * Swaps the contents held by the $location1 and $location2
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to swap content
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location1
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location2
     */
    public function swapLocation( APILocation $location1, APILocation $location2 )
    {
        if ( !is_numeric( $location1->id ) )
            throw new InvalidArgumentValue( "id", $location1->id, "Location" );

        if ( !is_numeric( $location2->id ) )
            throw new InvalidArgumentValue( "id", $location2->id, "Location" );

        $loadedLocation1 = $this->loadLocation( $location1->id );
        $loadedLocation2 = $this->loadLocation( $location2->id );

        $this->persistenceHandler->locationHandler()->swap( $loadedLocation1->id, $loadedLocation2->id );
    }

    /**
     * Hides the $location and marks invisible all descendants of $location.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to hide this location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location $location, with updated hidden value
     */
    public function hideLocation( APILocation $location )
    {
        if ( !is_numeric( $location->id ) )
            throw new InvalidArgumentValue( "id", $location->id, "Location" );

        try
        {
            $this->persistenceHandler->locationHandler()->hide( $location->id );
        }
        catch ( NotFound $e )
        {
            throw new NotFoundException( "location", $location->id, $e );
        }

        return $this->loadLocation( $location->id );
    }

    /**
     * Unhides the $location.
     *
     * This method and marks visible all descendants of $locations
     * until a hidden location is found.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to unhide this location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location $location, with updated hidden value
     */
    public function unhideLocation( APILocation $location )
    {
        if ( !is_numeric( $location->id ) )
            throw new InvalidArgumentValue( "id", $location->id, "Location" );

        try
        {
            $this->persistenceHandler->locationHandler()->unHide( $location->id );
        }
        catch ( NotFound $e )
        {
            throw new NotFoundException( "location", $location->id, $e );
        }

        return $this->loadLocation( $location->id );
    }

    /**
     * Moves the subtree to $newParentLocation
     *
     * If a user has the permission to move the location to a target location
     * he can do it regardless of an existing descendant on which the user has no permission.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to move this location to the target
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Location $newParentLocation
     */
    public function moveSubtree( APILocation $location, APILocation $newParentLocation )
    {
        if ( !is_numeric( $location->id ) )
            throw new InvalidArgumentValue( "id", $location->id, "Location" );

        if ( !is_numeric( $newParentLocation->id ) )
            throw new InvalidArgumentValue( "id", $newParentLocation->id, "Location" );

        try
        {
            $this->persistenceHandler->locationHandler()->move( $location->id, $newParentLocation->id );
        }
        catch ( NotFound $e )
        {
            throw new NotFoundException( "location", $e->identifier, $e );
        }
    }

    /**
     * Deletes $location and all its descendants.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to delete this location or a descendant
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     */
    public function deleteLocation( APILocation $location )
    {
        if ( !is_numeric( $location->id ) )
            throw new InvalidArgumentValue( "id", $location->id, "Location" );

        try
        {
            $this->persistenceHandler->locationHandler()->removeSubtree( $location->id );
        }
        catch ( NotFound $e )
        {
            throw new NotFoundException( "location", $location->id, $e );
        }
    }


    /**
     * Instantiates a new location create class
     *
     * @param int $parentLocationId the parent under which the new location should be created
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct
     */
    public function newLocationCreateStruct( $parentLocationId )
    {
        return new LocationCreateStruct( array(
            'parentLocationId' => (int) $parentLocationId
        ) );
    }

    /**
     * Instantiates a new location update class
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct
     */
    public function newLocationUpdateStruct()
    {
        return new LocationUpdateStruct();
    }

    /**
     * Builds domain location object from provided persistence location
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $spiLocation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected function buildDomainLocationObject( SPILocation $spiLocation )
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo( $spiLocation->contentId );
        $childrenLocations = $this->searchChildrenLocations( $spiLocation->id, $spiLocation->sortField, $spiLocation->sortOrder );

        return new Location( array(
            'contentInfo'              => $contentInfo,
            'contentId'                => $contentInfo->contentId,
            'id'                       => $spiLocation->id,
            'priority'                 => $spiLocation->priority,
            'hidden'                   => $spiLocation->hidden,
            'invisible'                => $spiLocation->invisible,
            'remoteId'                 => $spiLocation->remoteId,
            'parentLocationId'         => $spiLocation->parentId,
            'pathString'               => $spiLocation->pathString,
            'modifiedSubLocationDate'  => new \DateTime("{@$spiLocation->modifiedSubLocation}"),
            'depth'                    => $spiLocation->depth,
            'sortField'                => $spiLocation->sortField,
            'sortOrder'                => $spiLocation->sortOrder,
            'childCount'               => $childrenLocations ? $childrenLocations->count : 0
        ) );
    }

    /**
     * Instantiates a correct sort clause object based on provided location sort field and sort order
     *
     * @param int $sortField
     * @param int $sortOrder
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Query\SortClause
     */
    protected function getSortClauseBySortField( $sortField, $sortOrder = APILocation::SORT_ORDER_ASC )
    {
        //@todo: use consts for sort order instead of hardcoded values
        $sortOrder = $sortOrder == APILocation::SORT_ORDER_DESC ? 'descending' : 'ascending';
        switch ( $sortField )
        {
            case APILocation::SORT_FIELD_PATH:
                return new \eZ\Publish\SPI\Persistence\Content\Query\SortClause\LocationPath( $sortOrder );

            case APILocation::SORT_FIELD_PUBLISHED:
                return new \eZ\Publish\SPI\Persistence\Content\Query\SortClause\DateCreated( $sortOrder );

            case APILocation::SORT_FIELD_MODIFIED:
                return new \eZ\Publish\SPI\Persistence\Content\Query\SortClause\DateModified( $sortOrder );

            case APILocation::SORT_FIELD_SECTION:
                return new \eZ\Publish\SPI\Persistence\Content\Query\SortClause\SectionIdentifier( $sortOrder );

            case APILocation::SORT_FIELD_DEPTH:
                return new \eZ\Publish\SPI\Persistence\Content\Query\SortClause\LocationDepth( $sortOrder );

            //@todo: enable
            // case APILocation::SORT_FIELD_CLASS_IDENTIFIER:

            //@todo: enable
            // case APILocation::SORT_FIELD_CLASS_NAME:

            case APILocation::SORT_FIELD_PRIORITY:
                return new \eZ\Publish\SPI\Persistence\Content\Query\SortClause\LocationPriority( $sortOrder );

            case APILocation::SORT_FIELD_NAME:
                return new \eZ\Publish\SPI\Persistence\Content\Query\SortClause\ContentName( $sortOrder );

            //@todo: enable
            // case APILocation::SORT_FIELD_MODIFIED_SUBNODE:

            //@todo: enable
            // case APILocation::SORT_FIELD_NODE_ID:

            //@todo: enable
            // case APILocation::SORT_FIELD_CONTENTOBJECT_ID:

            default:
                return new \eZ\Publish\SPI\Persistence\Content\Query\SortClause\LocationPath( $sortOrder );
        }
    }
}
