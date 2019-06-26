<?php

/**
 * File containing the LocationUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\LocationService as APILocationService;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\REST\Common\RequestParser;
use eZ\Publish\Core\REST\Common\Input\Dispatcher;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;

/**
 * Location service, used for complex subtree operations.
 *
 * @example Examples/location.php
 */
class LocationService implements APILocationService, Sessionable
{
    /** @var \eZ\Publish\Core\REST\Client\HttpClient */
    private $client;

    /** @var \eZ\Publish\Core\REST\Common\Input\Dispatcher */
    private $inputDispatcher;

    /** @var \eZ\Publish\Core\REST\Common\Output\Visitor */
    private $outputVisitor;

    /** @var \eZ\Publish\Core\REST\Common\RequestParser */
    private $requestParser;

    /**
     * @param \eZ\Publish\Core\REST\Client\HttpClient $client
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $outputVisitor
     * @param \eZ\Publish\Core\REST\Common\RequestParser $requestParser
     */
    public function __construct(HttpClient $client, Dispatcher $inputDispatcher, Visitor $outputVisitor, RequestParser $requestParser)
    {
        $this->client = $client;
        $this->inputDispatcher = $inputDispatcher;
        $this->outputVisitor = $outputVisitor;
        $this->requestParser = $requestParser;
    }

    /**
     * Set session ID.
     *
     * Only for testing
     *
     * @param mixed $id
     *
     * @private
     */
    public function setSession($id)
    {
        if ($this->outputVisitor instanceof Sessionable) {
            $this->outputVisitor->setSession($id);
        }
    }

    /**
     * Instantiates a new location create class.
     *
     * @param mixed $parentLocationId the parent under which the new location should be created
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType|null $contentType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct
     */
    public function newLocationCreateStruct($parentLocationId, ContentType $contentType = null)
    {
        $properties = [
            'parentLocationId' => $parentLocationId,
        ];
        if ($contentType) {
            $properties['sortField'] = $contentType->defaultSortField;
            $properties['sortOrder'] = $contentType->defaultSortOrder;
        }

        return new LocationCreateStruct($properties);
    }

    /**
     * Creates the new $location in the content repository for the given content.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create this location
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  if the content is already below the specified parent
     *                                        or the parent is a sub location of the location the content
     *                                        or if set the remoteId exists already
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct $locationCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the newly created Location
     */
    public function createLocation(ContentInfo $contentInfo, LocationCreateStruct $locationCreateStruct)
    {
        $inputMessage = $this->outputVisitor->visit($locationCreateStruct);
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType('Location');

        $values = $this->requestParser->parse('object', $contentInfo->id);
        $result = $this->client->request(
            'POST',
            $this->requestParser->generate('objectLocations', array('object' => $values['object'])),
            $inputMessage
        );

        return $this->inputDispatcher->parse($result);
    }

    /**
     * {@inheritdoc)
     */
    public function loadLocation($locationId, array $prioritizedLanguages = null, bool $useAlwaysAvailable = null)
    {
        $response = $this->client->request(
            'GET',
            $locationId,
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('Location'))
            )
        );

        return $this->inputDispatcher->parse($response);
    }

    /**
     * {@inheritdoc)
     */
    public function loadLocationList(array $locationIds, array $prioritizedLanguages = null, bool $useAlwaysAvailable = null): iterable
    {
        // @todo Implement server part, ala: https://gist.github.com/andrerom/f2f328029ae7a9d78b363282b3ddf4a4

        $response = $this->client->request(
            'GET',
            $this->requestParser->generate('locationsByIds', ['locations' => $locationIds]),
            new Message(['Accept' => $this->outputVisitor->getMediaType('LocationList')])
        );

        return $this->inputDispatcher->parse($response);
    }

    /**
     * {@inheritdoc)
     */
    public function loadLocationByRemoteId($remoteId, array $prioritizedLanguages = null, bool $useAlwaysAvailable = null)
    {
        $response = $this->client->request(
            'GET',
            $this->requestParser->generate('locationByRemote', array('location' => $remoteId)),
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('LocationList'))
            )
        );

        return reset($this->inputDispatcher->parse($response));
    }

    /**
     * Instantiates a new location update class.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct
     */
    public function newLocationUpdateStruct()
    {
        return new LocationUpdateStruct();
    }

    /**
     * Updates $location in the content repository.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to update this location
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException   if if set the remoteId exists already
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct $locationUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the updated Location
     */
    public function updateLocation(Location $location, LocationUpdateStruct $locationUpdateStruct)
    {
        $inputMessage = $this->outputVisitor->visit($locationUpdateStruct);
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType('Location');
        $inputMessage->headers['X-HTTP-Method-Override'] = 'PATCH';

        $result = $this->client->request(
            'POST',
            $location->id,
            $inputMessage
        );

        return $this->inputDispatcher->parse($result);
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
     * @param string[]|null $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public function loadLocations(ContentInfo $contentInfo, Location $rootLocation = null, array $prioritizedLanguages = null)
    {
        $values = $this->requestParser->parse('object', $contentInfo->id);
        $response = $this->client->request(
            'GET',
            $this->requestParser->generate('objectLocations', array('object' => $values['object'])),
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('LocationList'))
            )
        );

        return $this->inputDispatcher->parse($response);
    }

    /**
     * Loads children which are readable by the current user of a location object sorted by sortField and sortOrder.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param int $offset the start offset for paging
     * @param int $limit the number of locations returned
     * @param string[]|null $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationList
     */
    public function loadLocationChildren(Location $location, $offset = 0, $limit = 25, array $prioritizedLanguages = null)
    {
        $values = $this->requestParser->parse('location', $location->id);
        $response = $this->client->request(
            'GET',
            $this->requestParser->generate('locationChildren', array('location' => $values['location'])),
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('LocationList'))
            )
        );

        return $this->inputDispatcher->parse($response);
    }

    /**
     * Load parent Locations for Content Draft.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param string[]|null $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[] List of parent Locations
     */
    public function loadParentLocationsForDraftContent(VersionInfo $versionInfo, array $prioritizedLanguages = null)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Returns the number of children which are readable by the current user of a location object.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return int
     */
    public function getLocationChildCount(Location $location)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Swaps the contents hold by the $location1 and $location2.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to swap content
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location1
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location2
     */
    public function swapLocation(Location $location1, Location $location2)
    {
        throw new \Exception('@todo: Implement.');
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
    public function hideLocation(Location $location)
    {
        throw new \Exception('@todo: Implement.');
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
    public function unhideLocation(Location $location)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Deletes $location and all its descendants.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to delete this location or a descendant
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     */
    public function deleteLocation(Location $location)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation.
     *
     * Only the items on which the user has read access are copied.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed copy the subtree to the given parent location
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  if the target location is a sub location of the given location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $subtree - the subtree denoted by the location to copy
     * @param \eZ\Publish\API\Repository\Values\Content\Location $targetParentLocation - the target parent location for the copy operation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location The newly created location of the copied subtree
     *
     * @todo enhancement - this method should return a result structure containing the new location and a list
     *       of locations which are not copied due to permission denials.
     */
    public function copySubtree(Location $subtree, Location $targetParentLocation)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Moves the subtree to $newParentLocation.
     *
     * If a user has the permission to move the location to a target location
     * he can do it regardless of an existing descendant on which the user has no permission.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to move this location to the target
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Location $newParentLocation
     */
    public function moveSubtree(Location $location, Location $newParentLocation)
    {
        throw new \Exception('@todo: Implement.');
    }

    public function getAllLocationsCount(): int
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     *
     * @throws \Exception
     */
    public function loadAllLocations(int $offset = 0, int $limit = 25): array
    {
        throw new \Exception('@todo: Implement.');
    }
}
