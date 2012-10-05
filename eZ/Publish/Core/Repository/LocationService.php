<?php
/**
 * File containing the eZ\Publish\Core\Repository\LocationService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct,
    eZ\Publish\API\Repository\Values\Content\LocationCreateStruct,
    eZ\Publish\API\Repository\Values\Content\ContentInfo as APIContentInfo,
    eZ\Publish\Core\Repository\Values\Content\Location,
    eZ\Publish\Core\Repository\Values\Content\ContentInfo,
    eZ\Publish\Core\Repository\Values\ContentType\ContentType,
    eZ\Publish\API\Repository\Values\Content\Location as APILocation,

    eZ\Publish\SPI\Persistence\Content\Location as SPILocation,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct,

    eZ\Publish\API\Repository\LocationService as LocationServiceInterface,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\Handler,

    eZ\Publish\API\Repository\Values\Content\Query,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as CriterionLogicalAnd,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId as CriterionContentId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\Status as CriterionStatus,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId as CriterionParentLocationId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationRemoteId as CriterionLocationRemoteId,
    eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException,

    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\Core\Base\Exceptions\BadStateException,
    eZ\Publish\Core\Base\Exceptions\UnauthorizedException,

    DateTime;

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
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $handler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
        $this->settings = $settings + array(// Union makes sure default settings are ignored if provided in argument
            //'defaultSetting' => array(),
        );
    }

    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     *
     * Only the items on which the user has read access are copied.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed copy the subtree to the given parent location
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the target location is a sub location of the given location
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
            throw new InvalidArgumentException("targetParentLocation", "target parent location is a sub location of the given subtree");

        if ( !$this->repository->canUser( 'content', 'create', $loadedSubtree->getContentInfo(), $loadedTargetLocation ) )
            throw new UnauthorizedException( 'content', 'create' );

        $this->repository->beginTransaction();
        try
        {
            $newLocation = $this->persistenceHandler->locationHandler()->copySubtree(
                $loadedSubtree->id,
                $loadedTargetLocation->id
            );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

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

        $spiLocation = $this->persistenceHandler->locationHandler()->load( $locationId );
        $location = $this->buildDomainLocationObject( $spiLocation );
        if ( !$this->repository->canUser( 'content', 'read', $location->getContentInfo(), $location ) )
            throw new UnauthorizedException( 'content', 'read' );

        return $location;
    }

    /**
     * Loads a location object from its $remoteId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read this location
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If more than one location with same remote ID was found
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

        $query = new Query(
            array(
                'criterion' => new CriterionLogicalAnd(
                    array(
                        new CriterionStatus( CriterionStatus::STATUS_PUBLISHED ),
                        new CriterionLocationRemoteId( $remoteId )
                    )
                )
            )
        );

        $searchResult = $this->persistenceHandler->searchHandler()->findContent( $query );
        if ( $searchResult->totalCount == 0 )
            throw new NotFoundException( "location", $remoteId );

        if ( $searchResult->totalCount > 1 )
            throw new BadStateException( "remoteId", "more than one location with specified remote ID found" );

        if ( is_array( $searchResult->searchHits[0]->valueObject->locations ) )
        {
            foreach ( $searchResult->searchHits[0]->valueObject->locations as $spiLocation )
            {
                if ( $spiLocation->remoteId === $remoteId )
                {
                    $location = $this->buildDomainLocationObject( $spiLocation );
                    if ( !$this->repository->canUser( 'content', 'read', $location->getContentInfo(), $location ) )
                        throw new UnauthorizedException( 'content', 'read' );

                    return $location;
                }
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
    public function loadMainLocation( APIContentInfo $contentInfo )
    {
        if ( !is_numeric( $contentInfo->id ) )
            throw new InvalidArgumentValue( "id", $contentInfo->id, "ContentInfo" );

        $query = new Query(
            array(
                'criterion' => new CriterionLogicalAnd(
                    array(
                        new CriterionContentId( $contentInfo->id ),
                        new CriterionStatus( CriterionStatus::STATUS_PUBLISHED ),
                    )
                )
            )
        );

        $searchResult = $this->persistenceHandler->searchHandler()->findContent( $query );
        if ( $searchResult->totalCount == 0 )
            throw new BadStateException( "contentInfo", 'content info has no published versions yet' );
        if ( $searchResult->totalCount > 1 )
            throw new BadStateException( "contentInfo", 'several content info exists for id:' . $contentInfo->id );

        $spiLocations = $searchResult->searchHits[0]->valueObject->locations;
        if ( empty( $spiLocations ) || !is_array( $spiLocations ) )
            return null;

        foreach ( $spiLocations as $spiLocation )
        {
            if ( $spiLocation->id == $spiLocation->mainLocationId )
            {
                $location = $this->buildDomainLocationObject( $spiLocation );
                if ( !$this->repository->canUser( 'content', 'read', $location->getContentInfo(), $location ) )
                    throw new UnauthorizedException( 'content', 'read' );

                return $location;
            }
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
    public function loadLocations( APIContentInfo $contentInfo, APILocation $rootLocation = null )
    {
        if ( !is_numeric( $contentInfo->id ) )
            throw new InvalidArgumentValue( "id", $contentInfo->id, "ContentInfo" );

        if ( $rootLocation !== null && !is_string( $rootLocation->pathString ) )
            throw new InvalidArgumentValue( "pathString", $rootLocation->pathString, "Location" );

        $query = new Query(
            array(
                'criterion' => new CriterionLogicalAnd(
                    array(
                        new CriterionContentId( $contentInfo->id ),
                        new CriterionStatus( CriterionStatus::STATUS_PUBLISHED ),
                    )
                )
            )
        );

        $searchResult = $this->persistenceHandler->searchHandler()->findContent( $query );
        if ( $searchResult->totalCount == 0 )
            throw new BadStateException( "contentInfo", 'content info has no published versions yet' );
        if ( $searchResult->totalCount > 1 )
            throw new BadStateException( "contentInfo", 'several content info exists for id:' . $contentInfo->id );

        $spiLocations = $searchResult->searchHits[0]->valueObject->locations;
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
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
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

        $searchResult = $this->searchChildrenLocations(
            $location->id,
            $location->sortField,
            $location->sortOrder,
            $offset,
            $limit
        );

        if ( $searchResult->totalCount == 0 )
            return array();

        $childLocations = array();
        foreach ( $searchResult->searchHits as $spiSearchHit )
        {
            if ( is_array( $spiSearchHit->valueObject->locations ) )
            {
                foreach ( $spiSearchHit->valueObject->locations as $spiLocation )
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
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    protected function searchChildrenLocations( $parentLocationId, $sortField = null, $sortOrder = APILocation::SORT_ORDER_ASC, $offset = 0, $limit = -1 )
    {
        $query = new Query(
            array(
                'criterion' => new CriterionLogicalAnd(
                    array(
                        new CriterionParentLocationId( $parentLocationId ),
                        new CriterionStatus( CriterionStatus::STATUS_PUBLISHED ),
                    )
                ),
                'offset' => ( $offset >= 0 ? (int) $offset : 0 ),
                'limit' => ( $limit  >= 0 ? (int) $limit  : null )
            )
        );

        if ( $sortField !== null )
            $query->sortClauses = array( $this->getSortClauseBySortField( $sortField, $sortOrder ) );

        if ( !$this->repository->getSearchService()->addPermissionsCriterion( $query->criterion ) )
        {
            return array();
        }

        return $this->persistenceHandler->searchHandler()->findContent( $query );
    }

    /**
     * Creates the new $location in the content repository for the given content
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create this location
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the content is already below the specified parent
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
    public function createLocation( APIContentInfo $contentInfo, LocationCreateStruct $locationCreateStruct )
    {
        if ( !is_numeric( $contentInfo->id ) )
            throw new InvalidArgumentValue( "id", $contentInfo->id, "ContentInfo" );

        if ( !is_numeric( $contentInfo->currentVersionNo ) )
            throw new InvalidArgumentValue( "currentVersionNo", $contentInfo->currentVersionNo, "ContentInfo" );

        if ( !is_numeric( $locationCreateStruct->parentLocationId ) )
            throw new InvalidArgumentValue( "parentLocationId", $locationCreateStruct->parentLocationId, "LocationCreateStruct" );

        if ( $locationCreateStruct->priority !== null && !is_numeric( $locationCreateStruct->priority ) )
            throw new InvalidArgumentValue( "priority", $locationCreateStruct->priority, "LocationCreateStruct" );

        if ( !is_bool( $locationCreateStruct->hidden ) )
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
                    throw new InvalidArgumentException( "locationCreateStruct", "location with provided remote ID already exists" );
            }
            catch ( APINotFoundException $e ) {}
        }
        else
        {
            $locationCreateStruct->remoteId = md5( uniqid( get_class( $this ), true ) );
        }

        $loadedParentLocation = $this->loadLocation( $locationCreateStruct->parentLocationId );

        // Check if the parent is a sub location of one of the existing content locations (this also solves the
        // situation where parent location actually one of the content locations),
        // or if the content already has location below given location create struct parent
        $existingContentLocations = $this->loadLocations( $contentInfo );
        if ( !empty( $existingContentLocations ) )
        {
            foreach ( $existingContentLocations as $existingContentLocation )
            {
                if ( stripos( $loadedParentLocation->pathString, $existingContentLocation->pathString ) !== false )
                    throw new InvalidArgumentException( "locationCreateStruct", "specified parent is a sub location of one of the existing content locations" );
                if ( $loadedParentLocation->id == $existingContentLocation->parentLocationId )
                    throw new InvalidArgumentException( "locationCreateStruct", "content is already below the specified parent" );
            }
        }

        if ( !$this->repository->canUser( 'content', 'create', $contentInfo, $loadedParentLocation ) )
            throw new UnauthorizedException( 'content', 'create' );

        $createStruct = new CreateStruct();
        $createStruct->priority = $locationCreateStruct->priority !== null ? (int) $locationCreateStruct->priority : null;

        // if we declare the new location as hidden, it is automatically invisible
        // otherwise, it remains unhidden, and picks up visibility from parent
        if ( $locationCreateStruct->hidden === true )
        {
            $createStruct->hidden = true;
            $createStruct->invisible = true;
        }
        elseif ( $loadedParentLocation->hidden || $loadedParentLocation->invisible )
        {
            $createStruct->invisible = true;
        }

        $createStruct->remoteId = trim( $locationCreateStruct->remoteId );
        $createStruct->contentId = (int) $contentInfo->id;
        $createStruct->contentVersion = (int) $contentInfo->currentVersionNo;

        // @todo: set pathIdentificationString
        // $createStruct->pathIdentificationString = null;

        $mainLocation = $this->loadMainLocation( $contentInfo );
        if ( $mainLocation !== null )
            $createStruct->mainLocationId = $mainLocation->id;

        $createStruct->sortField = $locationCreateStruct->sortField !== null ? (int) $locationCreateStruct->sortField : APILocation::SORT_FIELD_NAME;
        $createStruct->sortOrder = $locationCreateStruct->sortOrder !== null ? (int) $locationCreateStruct->sortOrder : APILocation::SORT_ORDER_ASC;
        $createStruct->parentId = $loadedParentLocation->id;

        $this->repository->beginTransaction();
        try
        {
            $newLocation = $this->persistenceHandler->locationHandler()->create( $createStruct );
            $content = $this->repository->getContentService()->loadContent( $newLocation->contentId );

            $urlAliasNames = $this->repository->getNameSchemaService()->resolveUrlAliasSchema( $content );
            foreach ( $urlAliasNames as $languageCode => $name )
            {
                $this->persistenceHandler->urlAliasHandler()->publishUrlAliasForLocation(
                    $newLocation->id,
                    $newLocation->parentId,
                    $name,
                    $languageCode,
                    $content->contentInfo->alwaysAvailable
                );
            }

            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainLocationObject( $newLocation );
    }

    /**
     * Updates $location in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to update this location
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException   if if set the remoteId exists already
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
                    throw new InvalidArgumentException( "locationUpdateStruct", "location with provided remote ID already exists" );
            }
            catch ( APINotFoundException $e ) {}
        }

        if ( !$this->repository->canUser( 'content', 'edit', $loadedLocation->getContentInfo(), $loadedLocation ) )
            throw new UnauthorizedException( 'content', 'edit' );

        $updateStruct = new UpdateStruct();
        $updateStruct->priority = $locationUpdateStruct->priority !== null ? (int) $locationUpdateStruct->priority : $loadedLocation->priority;
        $updateStruct->remoteId = $locationUpdateStruct->remoteId !== null ? trim( $locationUpdateStruct->remoteId ) : $loadedLocation->remoteId;
        $updateStruct->sortField = $locationUpdateStruct->sortField !== null ? (int) $locationUpdateStruct->sortField : $loadedLocation->sortField;
        $updateStruct->sortOrder = $locationUpdateStruct->sortOrder !== null ? (int) $locationUpdateStruct->sortOrder : $loadedLocation->sortOrder;

        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->locationHandler()->update( $updateStruct, $loadedLocation->id );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

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

        if ( !$this->repository->canUser( 'content', 'edit', $loadedLocation1->getContentInfo(), $loadedLocation1 ) )
            throw new UnauthorizedException( 'content', 'edit' );
        if ( !$this->repository->canUser( 'content', 'edit', $loadedLocation2->getContentInfo(), $loadedLocation2 ) )
            throw new UnauthorizedException( 'content', 'edit' );

        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->locationHandler()->swap( $loadedLocation1->id, $loadedLocation2->id );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
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

        if ( !$this->repository->canUser( 'content', 'hide', $location->getContentInfo(), $location ) )
            throw new UnauthorizedException( 'content', 'hide' );

        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->locationHandler()->hide( $location->id );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
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

        if ( !$this->repository->canUser( 'content', 'hide', $location->getContentInfo(), $location ) )
            throw new UnauthorizedException( 'content', 'hide' );

        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->locationHandler()->unHide( $location->id );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
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

        if ( !$this->repository->canUser( 'content', 'create', $location->getContentInfo(), $newParentLocation ) )
            throw new UnauthorizedException( 'content', 'create' );

        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->locationHandler()->move( $location->id, $newParentLocation->id );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
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

        if ( !$this->repository->canUser( 'content', 'manage_locations', $location->getContentInfo() ) )
            throw new UnauthorizedException( 'content', 'manage_locations' );
        if ( !$this->repository->canUser( 'content', 'remove', $location->getContentInfo(), $location ) )
            throw new UnauthorizedException( 'content', 'remove' );

        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->locationHandler()->removeSubtree( $location->id );
            $this->persistenceHandler->urlAliasHandler()->locationDeleted( $location->id );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }


    /**
     * Instantiates a new location create class
     *
     * @param mixed $parentLocationId the parent under which the new location should be created
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct
     */
    public function newLocationCreateStruct( $parentLocationId )
    {
        return new LocationCreateStruct(
            array(
                'parentLocationId' => (int) $parentLocationId
            )
        );
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
        if ( $spiLocation->id == 1 )// Workaround for missing ContentInfo on root location
            $contentInfo = new ContentInfo(
                array(
                    'id' => 0,
                    'name' => 'Top Level Nodes',
                    'sectionId' => 1,
                    'mainLocationId' => 1,
                    'contentType' => new ContentType(
                        array(
                             'identifier' => 'folder',
                             'isContainer' => true,
                             'fieldDefinitions' => array()
                        )
                    )
                )
            );
        else // @todo This should not be loaded separately, SPI need to change to fix this.
            $contentInfo = $this->repository->getContentService()->internalLoadContentInfo( $spiLocation->contentId );

        $childrenLocations = $this->searchChildrenLocations( $spiLocation->id, null, APILocation::SORT_ORDER_ASC, 0, 0 );

        return new Location(
            array(
                'contentInfo' => $contentInfo,
                'id' => (int) $spiLocation->id,
                'priority' => (int) $spiLocation->priority,
                'hidden' => (bool) $spiLocation->hidden,
                'invisible' => (bool) $spiLocation->invisible,
                'remoteId' => $spiLocation->remoteId,
                'parentLocationId' => (int) $spiLocation->parentId,
                'pathString' => $spiLocation->pathString,
                'modifiedSubLocationDate' => $this->getDateTime( $spiLocation->modifiedSubLocation ),
                'depth' => (int) $spiLocation->depth,
                'sortField' => (int) $spiLocation->sortField,
                'sortOrder' => (int) $spiLocation->sortOrder,
                'childCount' => $childrenLocations->totalCount
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

    /**
     * Instantiates a correct sort clause object based on provided location sort field and sort order
     *
     * @param int $sortField
     * @param int $sortOrder
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause
     */
    protected function getSortClauseBySortField( $sortField, $sortOrder = APILocation::SORT_ORDER_ASC )
    {
        $sortOrder = $sortOrder == APILocation::SORT_ORDER_DESC ? Query::SORT_DESC : Query::SORT_ASC;
        switch ( $sortField )
        {
            case APILocation::SORT_FIELD_PATH:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\LocationPathString( $sortOrder );

            case APILocation::SORT_FIELD_PUBLISHED:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\DatePublished( $sortOrder );

            case APILocation::SORT_FIELD_MODIFIED:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\DateModified( $sortOrder );

            case APILocation::SORT_FIELD_SECTION:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\SectionIdentifier( $sortOrder );

            case APILocation::SORT_FIELD_DEPTH:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\LocationDepth( $sortOrder );

            //@todo: enable
            // case APILocation::SORT_FIELD_CLASS_IDENTIFIER:

            //@todo: enable
            // case APILocation::SORT_FIELD_CLASS_NAME:

            case APILocation::SORT_FIELD_PRIORITY:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\LocationPriority( $sortOrder );

            case APILocation::SORT_FIELD_NAME:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentName( $sortOrder );

            //@todo: enable
            // case APILocation::SORT_FIELD_MODIFIED_SUBNODE:

            //@todo: enable
            // case APILocation::SORT_FIELD_NODE_ID:

            //@todo: enable
            // case APILocation::SORT_FIELD_CONTENTOBJECT_ID:

            default:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\LocationPathString( $sortOrder );
        }
    }
}
