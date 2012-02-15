<?php
/**
 * @package eZ\Publish\Core\Repository
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct,
    eZ\Publish\Core\Repository\Values\Content\LocationCreateStruct,
    eZ\Publish\API\Repository\Values\Content\LocationCreateStruct as APILocationCreateStruct,
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
    eZ\Publish\SPI\Persistence\Content\Query\Criterion\ParentLocationId as CriterionParentLocationId,

    ezp\Base\Exception\NotFound,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\Base\Exceptions\IllegalArgumentException;

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
        if ( empty( $subtree->id ) )
            throw new InvalidArgumentValue( "id", $subtree->id, "Location" );

        if ( empty( $targetParentLocation->id ) )
            throw new InvalidArgumentValue( "id", $targetParentLocation->id, "Location" );

        $loadedSubtree = $this->loadLocation( $subtree->id );
        $loadedTargetLocation = $this->loadLocation( $targetParentLocation->id );

        if ( stripos( $loadedTargetLocation->pathString, $loadedSubtree->pathString ) !== false )
            throw new IllegalArgumentException("targetParentLocation", "target parent location is a sub location of the given subtree");

        $newLocation = $this->persistenceHandler->locationHandler()->copySubtree( $subtree->id, $targetParentLocation->id );
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
        if ( empty( $locationId ) )
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
    public function loadLocationByRemoteId( $remoteId ){}

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
    public function loadMainLocation( ContentInfo $contentInfo ){}

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
    public function loadLocations( ContentInfo $contentInfo, APILocation $rootLocation = null ){}

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
    public function loadLocationChildren( APILocation $location, $offset = 0, $limit = -1 ){}

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
    public function createLocation( ContentInfo $contentInfo, APILocationCreateStruct $locationCreateStruct )
    {
        if ( empty( $contentInfo->contentId ) )
            throw new InvalidArgumentValue( "contentId", $contentInfo->contentId, "ContentInfo" );

        if ( empty( $locationCreateStruct->parentLocationId ) )
            throw new InvalidArgumentValue( "parentLocationId", $locationCreateStruct->parentLocationId, "LocationCreateStruct" );

        // check for existence of location with provided remote ID
        if ( $locationCreateStruct->remoteId !== null )
        {
            try
            {
                $this->loadLocationByRemoteId( $locationCreateStruct->remoteId );
                throw new IllegalArgumentException( "locationCreateStruct", "location with provided remote ID already exists" );
            }
            catch ( NotFoundException $e ) {}
        }

        // check if the content already has location below specified parent ID
        $searchResult = $this->persistenceHandler->searchHandler()->find( new CriterionLogicalAnd( array(
            new CriterionContentId( $contentInfo->contentId ),
            new CriterionParentLocationId( $locationCreateStruct->parentLocationId ),
        ) ) );

        if ( $searchResult->count > 0 )
            throw new IllegalArgumentException( "contentInfo", "content is already below the specified parent" );

        // check if the parent is a sub location of one of the existing content locations
        // this also solves the situation where parent location actually one of the content locations
        // NOTE: In 4.x it IS possible to add a location to the object below a parent which is below the existing content locations
        $loadedParentLocation = $this->loadLocation( $locationCreateStruct->parentLocationId );
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
        if ( $locationCreateStruct->priority !== null )
            $createStruct->priority = $locationCreateStruct->priority;

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
                $parentParentLocation = $this->loadLocation( $loadedParentLocation->parentId );
                if ( $parentParentLocation->hidden || $parentParentLocation->invisible )
                    $createStruct->invisible = true;
            }
            catch ( NotFoundException $e ) {}
        }

        $createStruct->remoteId = $locationCreateStruct->remoteId;
        $createStruct->contentId = $contentInfo->contentId;
        $createStruct->contentVersion = $contentInfo->currentVersionNo;

        // @todo: set main location
        // $createStruct->mainLocationId = $locationCreateStruct->isMainLocation;

        $createStruct->sortField = $locationCreateStruct->sortField;
        $createStruct->sortOrder = $locationCreateStruct->sortOrder;
        $createStruct->parentId = $locationCreateStruct->parentLocationId;

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
        if ( empty( $location->id ) )
            throw new InvalidArgumentValue( "id", $location->id, "Location" );

        $loadedLocation = $this->loadLocation( $location->id );

        if ( $locationUpdateStruct->remoteId !== null )
        {
            try
            {
                $this->loadLocationByRemoteId( $locationUpdateStruct->remoteId );
                throw new IllegalArgumentException( "locationUpdateStruct", "location with provided remote ID already exists" );
            }
            catch ( NotFoundException $e ) {}
        }

        $updateStruct = new UpdateStruct();
        $updateStruct->priority = $locationUpdateStruct->priority !== null ? $locationUpdateStruct->priority : $loadedLocation->priority;
        $updateStruct->remoteId = $locationUpdateStruct->remoteId !== null ? $locationUpdateStruct->remoteId : $loadedLocation->remoteId;
        $updateStruct->sortField = $locationUpdateStruct->sortField !== null ? $locationUpdateStruct->sortField : $loadedLocation->sortField;
        $updateStruct->sortOrder = $locationUpdateStruct->sortOrder !== null ? $locationUpdateStruct->sortOrder : $loadedLocation->sortOrder;

        $this->persistenceHandler->locationHandler()->update( $updateStruct, $loadedLocation->id );
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
        if ( empty( $location1->id ) )
            throw new InvalidArgumentValue( "id", $location1->id, "Location" );

        if ( empty( $location2->id ) )
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
        if ( empty( $location->id ) )
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
        if ( empty( $location->id ) )
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
        if ( empty( $location->id ) )
            throw new InvalidArgumentValue( "id", $location->id, "Location" );

        if ( empty( $newParentLocation->id ) )
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
        if ( empty( $location->id ) )
            throw new InvalidArgumentValue( "id", $location->id, "Location" );

        try
        {
            $this->persistenceHandler->locationHandler()->removeSubtree( $location->id );
        }
        catch ( NotFound $e )
        {
            throw new NotFoundException( "location", $e->identifier, $e );
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
            'parentLocationId' => $parentLocationId
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

    protected function buildDomainLocationObject( SPILocation $spiLocation )
    {
        return new Location( array(
            'id'                       => $spiLocation->id,
            'priority'                 => $spiLocation->priority,
            'hidden'                   => $spiLocation->hidden,
            'invisible'                => $spiLocation->invisible,
            'remoteId'                 => $spiLocation->remoteId,
            'contentId'                => $spiLocation->contentId,
            'parentId'                 => $spiLocation->parentId,
            'pathIdentificationString' => $spiLocation->pathIdentificationString,
            'pathString'               => $spiLocation->pathString,
            'modifiedSubLocation'      => $spiLocation->modifiedSubLocation,
            'mainLocationId'           => $spiLocation->mainLocationId,
            'depth'                    => $spiLocation->depth,
            'sortField'                => $spiLocation->sortField,
            'sortOrder'                => $spiLocation->sortOrder
        ) );
    }
}
