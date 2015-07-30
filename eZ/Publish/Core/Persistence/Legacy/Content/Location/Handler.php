<?php

/**
 * File containing the Location Handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Location;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as BaseLocationHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Handler as ContentHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\TreeHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler as ObjectStateHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;

/**
 * The Location Handler interface defines operations on Location elements in the storage engine.
 */
class Handler implements BaseLocationHandler
{
    /**
     * Gateway for handling location data.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Location locationMapper.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected $locationMapper;

    /**
     * Content handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Handler
     */
    protected $contentHandler;

    /**
     * Object state handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler
     */
    protected $objectStateHandler;

    /**
     * Tree handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\TreeHandler
     */
    protected $treeHandler;

    /**
     * Construct from userGateway.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway $locationGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper $locationMapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Handler $contentHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler $objectStateHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\TreeHandler $treeHandler
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler
     */
    public function __construct(
        LocationGateway $locationGateway,
        LocationMapper $locationMapper,
        ContentHandler $contentHandler,
        ObjectStateHandler $objectStateHandler,
        TreeHandler $treeHandler
    ) {
        $this->locationGateway = $locationGateway;
        $this->locationMapper = $locationMapper;
        $this->contentHandler = $contentHandler;
        $this->objectStateHandler = $objectStateHandler;
        $this->treeHandler = $treeHandler;
    }

    /**
     * Returns parent path string for a path string.
     *
     * @param string $pathString
     *
     * @return string
     */
    protected function getParentPathString($pathString)
    {
        return implode('/', array_slice(explode('/', $pathString), 0, -2)) . '/';
    }

    /**
     * Loads the data for the location identified by $locationId.
     *
     * @param int $locationId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function load($locationId)
    {
        return $this->treeHandler->loadLocation($locationId);
    }

    /**
     * Loads the subtree ids of the location identified by $locationId.
     *
     * @param int $locationId
     *
     * @return array Location ids are in the index, Content ids in the value.
     */
    public function loadSubtreeIds($locationId)
    {
        return $this->locationGateway->getSubtreeContent($locationId, true);
    }

    /**
     * Loads the data for the location identified by $remoteId.
     *
     * @param string $remoteId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function loadByRemoteId($remoteId)
    {
        $data = $this->locationGateway->getBasicNodeDataByRemoteId($remoteId);

        return $this->locationMapper->createLocationFromRow($data);
    }

    /**
     * Loads all locations for $contentId, optionally limited to a sub tree
     * identified by $rootLocationId.
     *
     * @param int $contentId
     * @param int $rootLocationId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location[]
     */
    public function loadLocationsByContent($contentId, $rootLocationId = null)
    {
        $rows = $this->locationGateway->loadLocationDataByContent($contentId, $rootLocationId);

        return $this->locationMapper->createLocationsFromRows($rows);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler::loadParentLocationsForDraftContent
     */
    public function loadParentLocationsForDraftContent($contentId)
    {
        $rows = $this->locationGateway->loadParentLocationsDataForDraftContent($contentId);

        return $this->locationMapper->createLocationsFromRows($rows);
    }

    /**
     * Returns an array of default content states with content state group id as key.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState[]
     */
    protected function getDefaultContentStates()
    {
        $defaultObjectStatesMap = array();

        foreach ($this->objectStateHandler->loadAllGroups() as $objectStateGroup) {
            foreach ($this->objectStateHandler->loadObjectStates($objectStateGroup->id) as $objectState) {
                // Only register the first object state which is the default one.
                $defaultObjectStatesMap[$objectStateGroup->id] = $objectState;
                break;
            }
        }

        return $defaultObjectStatesMap;
    }

    /**
     * @param Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState[] $contentStates
     */
    protected function setContentStates(Content $content, array $contentStates)
    {
        foreach ($contentStates as $contentStateGroupId => $contentState) {
            $this->objectStateHandler->setContentState(
                $content->versionInfo->contentInfo->id,
                $contentStateGroupId,
                $contentState->id
            );
        }
    }

    /**
     * Copy location object identified by $sourceId, into destination identified by $destinationParentId.
     *
     * Performs a deep copy of the location identified by $sourceId and all of
     * its child locations, copying the most recent published content object
     * for each location to a new content object without any additional version
     * information. Relations are not copied. URLs are not touched at all.
     *
     * @todo update subtree modification time, optionally retain dates and set creator
     *
     * @param mixed $sourceId
     * @param mixed $destinationParentId
     *
     * @return Location the newly created Location.
     */
    public function copySubtree($sourceId, $destinationParentId)
    {
        $children = $this->locationGateway->getSubtreeContent($sourceId);
        $destinationParentData = $this->locationGateway->getBasicNodeData($destinationParentId);
        $defaultObjectStates = $this->getDefaultContentStates();
        $contentMap = array();
        $locationMap = array(
            $children[0]['parent_node_id'] => array(
                'id' => $destinationParentId,
                'hidden' => (boolean)$destinationParentData['is_hidden'],
                'invisible' => (boolean)$destinationParentData['is_invisible'],
                'path_identification_string' => $destinationParentData['path_identification_string'],
            ),
        );

        $locations = array();
        foreach ($children as $child) {
            $locations[$child['contentobject_id']][$child['node_id']] = true;
        }

        $time = time();
        $mainLocations = array();
        $mainLocationsUpdate = array();
        foreach ($children as $index => $child) {
            // Copy content
            if (!isset($contentMap[$child['contentobject_id']])) {
                $content = $this->contentHandler->copy(
                    $child['contentobject_id'],
                    $child['contentobject_version']
                );

                $this->setContentStates($content, $defaultObjectStates);

                $content = $this->contentHandler->publish(
                    $content->versionInfo->contentInfo->id,
                    $content->versionInfo->contentInfo->currentVersionNo,
                    new MetadataUpdateStruct(
                        array(
                            'publicationDate' => $time,
                            'modificationDate' => $time,
                        )
                    )
                );

                $contentMap[$child['contentobject_id']] = $content->versionInfo->contentInfo->id;
            }

            $createStruct = $this->locationMapper->getLocationCreateStruct($child);
            $createStruct->contentId = $contentMap[$child['contentobject_id']];
            $parentData = $locationMap[$child['parent_node_id']];
            $createStruct->parentId = $parentData['id'];
            $createStruct->invisible = $createStruct->hidden || $parentData['hidden'] || $parentData['invisible'];
            $pathString = explode('/', $child['path_identification_string']);
            $pathString = end($pathString);
            $createStruct->pathIdentificationString = strlen($pathString) > 0
                ? $parentData['path_identification_string'] . '/' . $pathString
                : null;

            // Use content main location if already set, otherwise create location as main
            if (isset($mainLocations[$child['contentobject_id']])) {
                $createStruct->mainLocationId = $locationMap[$mainLocations[$child['contentobject_id']]]['id'];
            } else {
                $createStruct->mainLocationId = true;
                $mainLocations[$child['contentobject_id']] = $child['node_id'];

                // If needed mark for update
                if (
                    isset($locations[$child['contentobject_id']][$child['main_node_id']]) &&
                    count($locations[$child['contentobject_id']]) > 1 &&
                    $child['node_id'] !== $child['main_node_id']
                ) {
                    $mainLocationsUpdate[$child['contentobject_id']] = $child['main_node_id'];
                }
            }

            $newLocation = $this->create($createStruct);

            $locationMap[$child['node_id']] = array(
                'id' => $newLocation->id,
                'hidden' => $newLocation->hidden,
                'invisible' => $newLocation->invisible,
                'path_identification_string' => $newLocation->pathIdentificationString,
            );
            if ($index === 0) {
                $copiedSubtreeRootLocation = $newLocation;
            }
        }

        // Update main locations
        foreach ($mainLocationsUpdate as $contentId => $mainLocationId) {
            $this->changeMainLocation(
                $contentMap[$contentId],
                $locationMap[$mainLocationId]['id']
            );
        }

        // If subtree root is main location for its content, update subtree section to the one of the
        // parent location content
        $subtreeRootContentInfo = $this->contentHandler->loadContentInfo($copiedSubtreeRootLocation->contentId);
        if ($subtreeRootContentInfo->mainLocationId === $copiedSubtreeRootLocation->id) {
            $this->setSectionForSubtree(
                $copiedSubtreeRootLocation->id,
                $this->contentHandler->loadContentInfo($this->load($destinationParentId)->contentId)->sectionId
            );
        }

        return $copiedSubtreeRootLocation;
    }

    /**
     * Moves location identified by $sourceId into new parent identified by $destinationParentId.
     *
     * Performs a full move of the location identified by $sourceId to a new
     * destination, identified by $destinationParentId. Relations do not need
     * to be updated, since they refer to Content. URLs are not touched.
     *
     * @param mixed $sourceId
     * @param mixed $destinationParentId
     *
     * @return bool
     */
    public function move($sourceId, $destinationParentId)
    {
        $sourceNodeData = $this->locationGateway->getBasicNodeData($sourceId);
        $destinationNodeData = $this->locationGateway->getBasicNodeData($destinationParentId);

        $this->locationGateway->moveSubtreeNodes(
            $sourceNodeData,
            $destinationNodeData
        );

        $this->locationGateway->updateNodeAssignment(
            $sourceNodeData['contentobject_id'],
            $sourceNodeData['parent_node_id'],
            $destinationParentId,
            Gateway::NODE_ASSIGNMENT_OP_CODE_MOVE
        );
    }

    /**
     * Marks the given nodes and all ancestors as modified.
     *
     * Optionally a time stamp with the modification date may be specified,
     * otherwise the current time is used.
     *
     * @param int|string $locationId
     * @param int $timestamp
     */
    public function markSubtreeModified($locationId, $timestamp = null)
    {
        $nodeData = $this->locationGateway->getBasicNodeData($locationId);
        $timestamp = $timestamp ?: time();
        $this->locationGateway->updateSubtreeModificationTime($nodeData['path_string'], $timestamp);
    }

    /**
     * Sets a location to be hidden, and it self + all children to invisible.
     *
     * @param mixed $id Location ID
     */
    public function hide($id)
    {
        $sourceNodeData = $this->locationGateway->getBasicNodeData($id);

        $this->locationGateway->hideSubtree($sourceNodeData['path_string']);
    }

    /**
     * Sets a location to be unhidden, and self + children to visible unless a parent is hiding the tree.
     * If not make sure only children down to first hidden node is marked visible.
     *
     * @param mixed $id
     */
    public function unHide($id)
    {
        $sourceNodeData = $this->locationGateway->getBasicNodeData($id);

        $this->locationGateway->unhideSubtree($sourceNodeData['path_string']);
    }

    /**
     * Swaps the content object being pointed to by a location object.
     *
     * Make the location identified by $locationId1 refer to the Content
     * referred to by $locationId2 and vice versa.
     *
     * @param mixed $locationId1
     * @param mixed $locationId2
     *
     * @return bool
     */
    public function swap($locationId1, $locationId2)
    {
        $this->locationGateway->swap($locationId1, $locationId2);
    }

    /**
     * Updates an existing location.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct $location
     * @param int $locationId
     */
    public function update(UpdateStruct $location, $locationId)
    {
        $this->locationGateway->update($location, $locationId);
    }

    /**
     * Creates a new location rooted at $location->parentId.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function create(CreateStruct $createStruct)
    {
        $parentNodeData = $this->locationGateway->getBasicNodeData($createStruct->parentId);
        $locationStruct = $this->locationGateway->create($createStruct, $parentNodeData);
        $this->locationGateway->createNodeAssignment(
            $createStruct,
            $parentNodeData['node_id'],
            LocationGateway::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP
        );

        return $locationStruct;
    }

    /**
     * Removes all Locations under and including $locationId.
     *
     * Performs a recursive delete on the location identified by $locationId,
     * including all of its child locations. Content which is not referred to
     * by any other location is automatically removed. Content which looses its
     * main Location will get the first of its other Locations assigned as the
     * new main Location.
     *
     * @param mixed $locationId
     *
     * @return bool
     */
    public function removeSubtree($locationId)
    {
        $this->treeHandler->removeSubtree($locationId);
    }

    /**
     * Set section on all content objects in the subtree.
     *
     * @param mixed $locationId
     * @param mixed $sectionId
     */
    public function setSectionForSubtree($locationId, $sectionId)
    {
        $this->treeHandler->setSectionForSubtree($locationId, $sectionId);
    }

    /**
     * Changes main location of content identified by given $contentId to location identified by given $locationId.
     *
     * Updates ezcontentobject_tree and eznode_assignment tables (eznode_assignment for content current version number).
     *
     * @param mixed $contentId
     * @param mixed $locationId
     */
    public function changeMainLocation($contentId, $locationId)
    {
        $this->treeHandler->changeMainLocation($contentId, $locationId);
    }
}
