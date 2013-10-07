<?php
/**
 * File containing the eZ\Publish\Core\Repository\LocationService class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\Core\Repository\DomainMapper;
use eZ\Publish\Core\Repository\NameSchemaService;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\API\Repository\Values\Content\LocationList;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\SPI\Persistence\Handler;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as CriterionLogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot as CriterionLogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree as CriterionSubtree;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId as CriterionParentLocationId;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use DateTime;
use Exception;

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
     * @var \eZ\Publish\Core\Repository\Repository
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
     * @var \eZ\Publish\Core\Repository\DomainMapper
     */
    protected $domainMapper;

    /**
     * @var \eZ\Publish\Core\Repository\NameSchemaService
     */
    protected $nameSchemaService;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     * @param \eZ\Publish\Core\Repository\DomainMapper $domainMapper
     * @param \eZ\Publish\Core\Repository\NameSchemaService $nameSchemaService
     * @param array $settings
     */
    public function __construct(
        RepositoryInterface $repository,
        Handler $handler,
        DomainMapper $domainMapper,
        NameSchemaService $nameSchemaService,
        array $settings = array()
    )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
        $this->domainMapper = $domainMapper;
        $this->nameSchemaService = $nameSchemaService;
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + array(
            //'defaultSetting' => array(),
        );
    }

    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     *
     * Only the items on which the user has read access are copied.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed copy the subtree to the given parent location
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user does not have read access to the whole source subtree
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the target location is a sub location of the given location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $subtree - the subtree denoted by the location to copy
     * @param \eZ\Publish\API\Repository\Values\Content\Location $targetParentLocation - the target parent location for the copy operation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location The newly created location of the copied subtree
     */
    public function copySubtree( APILocation $subtree, APILocation $targetParentLocation )
    {
        $loadedSubtree = $this->loadLocation( $subtree->id );
        $loadedTargetLocation = $this->loadLocation( $targetParentLocation->id );

        if ( stripos( $loadedTargetLocation->pathString, $loadedSubtree->pathString ) !== false )
            throw new InvalidArgumentException( "targetParentLocation", "target parent location is a sub location of the given subtree" );

        // check create permission on target
        if ( !$this->repository->canUser( 'content', 'create', $loadedSubtree->getContentInfo(), $loadedTargetLocation ) )
            throw new UnauthorizedException( 'content', 'create' );

        /** Check read access to whole source subtree
         * @var boolean|\eZ\Publish\API\Repository\Values\Content\Query\Criterion $contentReadCriterion
         */
        $contentReadCriterion = $this->repository->getSearchService()->getPermissionsCriterion();
        if ( $contentReadCriterion === false )
        {
            throw new UnauthorizedException( 'content', 'read' );
        }
        else if ( $contentReadCriterion !== true )
        {
            // Query if there are any content in subtree current user don't have access to
            $query = new Query(
                array(
                    'limit' => 0,
                    'criterion' => new CriterionLogicalAnd(
                        new CriterionSubtree( $loadedSubtree->pathString ),
                        new CriterionLogicalNot( $contentReadCriterion )
                    )
                )
            );
            $result = $this->repository->getSearchService()->findContent( $query, array(), false );
            if ( $result->totalCount > 0 )
            {
                throw new UnauthorizedException( 'content', 'read' );
            }
        }

        $this->repository->beginTransaction();
        try
        {
            $newLocation = $this->persistenceHandler->locationHandler()->copySubtree(
                $loadedSubtree->id,
                $loadedTargetLocation->id
            );

            $content = $this->repository->getContentService()->loadContent( $newLocation->contentId );
            $urlAliasNames = $this->nameSchemaService->resolveUrlAliasSchema( $content );
            foreach ( $urlAliasNames as $languageCode => $name )
            {
                $this->persistenceHandler->urlAliasHandler()->publishUrlAliasForLocation(
                    $newLocation->id,
                    $loadedTargetLocation->id,
                    $name,
                    $languageCode,
                    $content->contentInfo->alwaysAvailable
                );
            }

            $this->persistenceHandler->urlAliasHandler()->locationCopied(
                $newLocation->id,
                $loadedSubtree->parentLocationId,
                $loadedTargetLocation->id
            );

            $this->repository->commit();
        }
        catch ( Exception $e )
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
     * @param mixed $locationId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function loadLocation( $locationId )
    {
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

        $spiLocation = $this->persistenceHandler->locationHandler()->loadByRemoteId( $remoteId );
        $location = $this->buildDomainLocationObject( $spiLocation );
        if ( !$this->repository->canUser( 'content', 'read', $location->getContentInfo(), $location ) )
            throw new UnauthorizedException( 'content', 'read' );

        return $location;
    }

    /**
     * Loads the locations for the given content object.
     *
     * If a $rootLocation is given, only locations that belong to this location are returned.
     * The location list is also filtered by permissions on reading locations.
     *
     * @todo permissions check is missing
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if there is no published version yet
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Location $rootLocation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[] An array of {@link Location}
     */
    public function loadLocations( ContentInfo $contentInfo, APILocation $rootLocation = null )
    {
        if ( !$contentInfo->published )
        {
            throw new BadStateException( "\$contentInfo", "ContentInfo has no published versions" );
        }

        $spiLocations = $this->persistenceHandler->locationHandler()->loadLocationsByContent(
            $contentInfo->id,
            $rootLocation !== null ? $rootLocation->id : null
        );

        $locations = array();
        foreach ( $spiLocations as $spiLocation )
        {
            $locations[] = $this->buildDomainLocationObject( $spiLocation );
        }

        return $locations;
    }

    /**
     * Loads children which are readable by the current user of a location object sorted by sortField and sortOrder
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @param int $offset the start offset for paging
     * @param int $limit the number of locations returned. If $limit = -1 all children starting at $offset are returned
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationList
     */
    public function loadLocationChildren( APILocation $location, $offset = 0, $limit = -1 )
    {
        if ( !$this->domainMapper->isValidLocationSortField( $location->sortField ) )
            throw new InvalidArgumentValue( "sortField", $location->sortField, "Location" );

        if ( !$this->domainMapper->isValidLocationSortOrder( $location->sortOrder ) )
            throw new InvalidArgumentValue( "sortOrder", $location->sortOrder, "Location" );

        if ( !is_int( $offset ) )
            throw new InvalidArgumentValue( "offset", $offset );

        if ( !is_int( $limit ) )
            throw new InvalidArgumentValue( "limit", $limit );

        $searchResult = $this->searchChildrenLocations(
            $location->id,
            $location->sortField,
            $location->sortOrder,
            $offset,
            $limit
        );

        $childLocations = array();
        foreach ( $searchResult->searchHits as $spiSearchHit )
        {
            $spiContentLocations = $this->persistenceHandler->locationHandler()->loadLocationsByContent(
                $spiSearchHit->valueObject->versionInfo->contentInfo->id,
                $location->id
            );
            foreach ( $spiContentLocations as $spiLocation )
            {
                if ( $spiLocation->parentId == $location->id )
                {
                    $childLocation = $this->buildDomainLocationObject( $spiLocation );
                    if ( $this->repository->canUser( 'content', 'read', $childLocation->getContentInfo(), $childLocation ) )
                    {
                        $childLocations[] = $childLocation;
                    }
                }
            }
        }

        return new LocationList(
            array(
                "locations" => $childLocations,
                "totalCount" => (int)$searchResult->totalCount
            )
        );
    }

    /**
     * Returns the number of children which are readable by the current user of a location object
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return int
     */
    public function getLocationChildCount( APILocation $location )
    {
        return $this->searchChildrenLocations(
            $location->id,
            null,
            APILocation::SORT_ORDER_ASC,
            0,
            0
        )->totalCount;
    }

    /**
     * Searches children locations of the provided parent location id
     *
     * @param mixed $parentLocationId
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
                'criterion' => new CriterionParentLocationId( $parentLocationId ),
                'offset' => ( $offset >= 0 ? (int)$offset : 0 ),
                'limit' => ( $limit >= 0 ? (int)$limit  : null )
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
     *
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
     */
    public function createLocation( ContentInfo $contentInfo, LocationCreateStruct $locationCreateStruct )
    {
        $content = $this->repository->getContentService()->loadContent( $contentInfo->id );
        $parentLocation = $this->loadLocation( $locationCreateStruct->parentLocationId );

        if ( !$this->repository->canUser( 'content', 'create', $content->contentInfo, $parentLocation ) )
        {
            throw new UnauthorizedException( 'content', 'create' );
        }

        // Check if the parent is a sub location of one of the existing content locations (this also solves the
        // situation where parent location actually one of the content locations),
        // or if the content already has location below given location create struct parent
        $existingContentLocations = $this->loadLocations( $content->contentInfo );
        if ( !empty( $existingContentLocations ) )
        {
            foreach ( $existingContentLocations as $existingContentLocation )
            {
                if ( stripos( $parentLocation->pathString, $existingContentLocation->pathString ) !== false )
                {
                    throw new InvalidArgumentException(
                        "\$locationCreateStruct",
                        "Specified parent is a sub location of one of the existing content locations."
                    );
                }
                if ( $parentLocation->id == $existingContentLocation->parentLocationId )
                {
                    throw new InvalidArgumentException(
                        "\$locationCreateStruct",
                        "Content is already below the specified parent."
                    );
                }
            }
        }

        $spiLocationCreateStruct = $this->domainMapper->buildSPILocationCreateStruct(
            $locationCreateStruct,
            $parentLocation,
            $content->contentInfo->mainLocationId !== null ? $content->contentInfo->mainLocationId : true,
            $content->contentInfo->id,
            $content->contentInfo->currentVersionNo
        );

        $this->repository->beginTransaction();
        try
        {
            $newLocation = $this->persistenceHandler->locationHandler()->create( $spiLocationCreateStruct );

            $urlAliasNames = $this->nameSchemaService->resolveUrlAliasSchema( $content );
            foreach ( $urlAliasNames as $languageCode => $name )
            {
                $this->persistenceHandler->urlAliasHandler()->publishUrlAliasForLocation(
                    $newLocation->id,
                    $newLocation->parentId,
                    $name,
                    $languageCode,
                    $content->contentInfo->alwaysAvailable,
                    // @todo: this is legacy storage specific for updating ezcontentobject_tree.path_identification_string, to be removed
                    $languageCode === $content->contentInfo->mainLanguageCode
                );
            }

            $this->repository->commit();
        }
        catch ( Exception $e )
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
        if ( $locationUpdateStruct->priority !== null && !is_int( $locationUpdateStruct->priority ) )
            throw new InvalidArgumentValue( "priority", $locationUpdateStruct->priority, "LocationUpdateStruct" );

        if ( $locationUpdateStruct->remoteId !== null && ( !is_string( $locationUpdateStruct->remoteId ) || empty( $locationUpdateStruct->remoteId ) ) )
            throw new InvalidArgumentValue( "remoteId", $locationUpdateStruct->remoteId, "LocationUpdateStruct" );

        if ( $locationUpdateStruct->sortField !== null && !$this->domainMapper->isValidLocationSortField( $locationUpdateStruct->sortField ) )
            throw new InvalidArgumentValue( "sortField", $locationUpdateStruct->sortField, "LocationUpdateStruct" );

        if ( $locationUpdateStruct->sortOrder !== null && !$this->domainMapper->isValidLocationSortOrder( $locationUpdateStruct->sortOrder ) )
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
            catch ( APINotFoundException $e )
            {
            }
        }

        if ( !$this->repository->canUser( 'content', 'edit', $loadedLocation->getContentInfo(), $loadedLocation ) )
            throw new UnauthorizedException( 'content', 'edit' );

        $updateStruct = new UpdateStruct();
        $updateStruct->priority = $locationUpdateStruct->priority !== null ? $locationUpdateStruct->priority : $loadedLocation->priority;
        $updateStruct->remoteId = $locationUpdateStruct->remoteId !== null ? trim( $locationUpdateStruct->remoteId ) : $loadedLocation->remoteId;
        $updateStruct->sortField = $locationUpdateStruct->sortField !== null ? $locationUpdateStruct->sortField : $loadedLocation->sortField;
        $updateStruct->sortOrder = $locationUpdateStruct->sortOrder !== null ? $locationUpdateStruct->sortOrder : $loadedLocation->sortOrder;

        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->locationHandler()->update( $updateStruct, $loadedLocation->id );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadLocation( $loadedLocation->id );
    }

    /**
     * Swaps the contents held by $location1 and $location2
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to swap content
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location1
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location2
     */
    public function swapLocation( APILocation $location1, APILocation $location2 )
    {
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
        catch ( Exception $e )
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
        if ( !$this->repository->canUser( 'content', 'hide', $location->getContentInfo(), $location ) )
            throw new UnauthorizedException( 'content', 'hide' );

        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->locationHandler()->hide( $location->id );
            $this->repository->commit();
        }
        catch ( Exception $e )
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
        if ( !$this->repository->canUser( 'content', 'hide', $location->getContentInfo(), $location ) )
            throw new UnauthorizedException( 'content', 'hide' );

        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->locationHandler()->unHide( $location->id );
            $this->repository->commit();
        }
        catch ( Exception $e )
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user does not have read access to the whole source subtree
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the new parent is in a subtree of the location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Location $newParentLocation
     */
    public function moveSubtree( APILocation $location, APILocation $newParentLocation )
    {
        $location = $this->loadLocation( $location->id );
        $newParentLocation = $this->loadLocation( $newParentLocation->id );

        // check create permission on target location
        if ( !$this->repository->canUser( 'content', 'create', $location->getContentInfo(), $newParentLocation ) )
            throw new UnauthorizedException( 'content', 'create' );

        /** Check read access to whole source subtree
         * @var boolean|\eZ\Publish\API\Repository\Values\Content\Query\Criterion $contentReadCriterion
         */
        $contentReadCriterion = $this->repository->getSearchService()->getPermissionsCriterion();
        if ( $contentReadCriterion === false )
        {
            throw new UnauthorizedException( 'content', 'read' );
        }
        else if ( $contentReadCriterion !== true )
        {
            // Query if there are any content in subtree current user don't have access to
            $query = new Query(
                array(
                    'limit' => 0,
                    'criterion' => new CriterionLogicalAnd(
                        new CriterionSubtree( $location->pathString ),
                        new CriterionLogicalNot( $contentReadCriterion )
                    )
                )
            );
            $result = $this->repository->getSearchService()->findContent( $query, array(), false );
            if ( $result->totalCount > 0 )
            {
                throw new UnauthorizedException( 'content', 'read' );
            }
        }

        if ( strpos( $newParentLocation->pathString, $location->pathString ) === 0 )
        {
            throw new InvalidArgumentException(
                "\$newParentLocation",
                "new parent location is in a subtree of the given \$location"
            );
        }

        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->locationHandler()->move( $location->id, $newParentLocation->id );

            $content = $this->repository->getContentService()->loadContent( $location->contentId );
            $urlAliasNames = $this->nameSchemaService->resolveUrlAliasSchema( $content );
            foreach ( $urlAliasNames as $languageCode => $name )
            {
                $this->persistenceHandler->urlAliasHandler()->publishUrlAliasForLocation(
                    $location->id,
                    $newParentLocation->id,
                    $name,
                    $languageCode,
                    $content->contentInfo->alwaysAvailable
                );
            }

            $this->persistenceHandler->urlAliasHandler()->locationMoved(
                $location->id,
                $location->parentLocationId,
                $newParentLocation->id
            );

            $this->repository->commit();
        }
        catch ( Exception $e )
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
        $location = $this->loadLocation( $location->id );

        if ( !$this->repository->canUser( 'content', 'manage_locations', $location->getContentInfo() ) )
            throw new UnauthorizedException( 'content', 'manage_locations' );
        if ( !$this->repository->canUser( 'content', 'remove', $location->getContentInfo(), $location ) )
            throw new UnauthorizedException( 'content', 'remove' );

        /** Check remove access to descendants
         * @var boolean|\eZ\Publish\API\Repository\Values\Content\Query\Criterion $contentReadCriterion
         */
        $contentReadCriterion = $this->repository->getSearchService()->getPermissionsCriterion( 'content', 'remove' );
        if ( $contentReadCriterion === false )
        {
            throw new UnauthorizedException( 'content', 'remove' );
        }
        else if ( $contentReadCriterion !== true )
        {
            // Query if there are any content in subtree current user don't have access to
            $query = new Query(
                array(
                    'limit' => 0,
                    'criterion' => new CriterionLogicalAnd(
                        new CriterionSubtree( $location->pathString ),
                        new CriterionLogicalNot( $contentReadCriterion )
                    )
                )
            );
            $result = $this->repository->getSearchService()->findContent( $query, array(), false );
            if ( $result->totalCount > 0 )
            {
                throw new UnauthorizedException( 'content', 'remove' );
            }
        }

        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->locationHandler()->removeSubtree( $location->id );
            $this->persistenceHandler->urlAliasHandler()->locationDeleted( $location->id );
            $this->repository->commit();
        }
        catch ( Exception $e )
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
                'parentLocationId' => $parentLocationId
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
        // TODO: this is hardcoded workaround for missing ContentInfo on root location
        if ( $spiLocation->id == 1 )
            $contentInfo = new ContentInfo(
                array(
                    'id' => 0,
                    'name' => 'Top Level Nodes',
                    'sectionId' => 1,
                    'mainLocationId' => 1,
                    'contentTypeId' => 1,
                )
            );
        else
            $contentInfo = $this->repository->getContentService()->internalLoadContentInfo( $spiLocation->contentId );

        return new Location(
            array(
                'contentInfo' => $contentInfo,
                'id' => $spiLocation->id,
                'priority' => $spiLocation->priority,
                'hidden' => $spiLocation->hidden,
                'invisible' => $spiLocation->invisible,
                'remoteId' => $spiLocation->remoteId,
                'parentLocationId' => $spiLocation->parentId,
                'pathString' => $spiLocation->pathString,
                'depth' => $spiLocation->depth,
                'sortField' => $spiLocation->sortField,
                'sortOrder' => $spiLocation->sortOrder,
            )
        );
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
                return new SortClause\LocationPathString( $sortOrder );

            case APILocation::SORT_FIELD_PUBLISHED:
                return new SortClause\DatePublished( $sortOrder );

            case APILocation::SORT_FIELD_MODIFIED:
                return new SortClause\DateModified( $sortOrder );

            case APILocation::SORT_FIELD_SECTION:
                return new SortClause\SectionIdentifier( $sortOrder );

            case APILocation::SORT_FIELD_DEPTH:
                return new SortClause\LocationDepth( $sortOrder );

            //@todo: enable
            // case APILocation::SORT_FIELD_CLASS_IDENTIFIER:

            //@todo: enable
            // case APILocation::SORT_FIELD_CLASS_NAME:

            case APILocation::SORT_FIELD_PRIORITY:
                return new SortClause\LocationPriority( $sortOrder );

            case APILocation::SORT_FIELD_NAME:
                return new SortClause\ContentName( $sortOrder );

            //@todo: enable
            // case APILocation::SORT_FIELD_MODIFIED_SUBNODE:

            //@todo: enable
            // case APILocation::SORT_FIELD_NODE_ID:

            //@todo: enable
            // case APILocation::SORT_FIELD_CONTENTOBJECT_ID:

            default:
                return new SortClause\LocationPathString( $sortOrder );
        }
    }
}
