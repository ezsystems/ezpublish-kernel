<?php

/**
 * File containing the Tree Handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content;

use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper;

/**
 * The TreeHandler is an intersect between ContentHandler and LocationHandler,
 * used to avoid circular dependency between them.
 */
class TreeHandler
{
    /**
     * Gateway for handling location data.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Location Mapper.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected $locationMapper;

    /**
     * Content gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGateway;

    /**
     * Content handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $contentMapper;

    /**
     * FieldHandler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected $fieldHandler;

    /**
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway $locationGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper $locationMapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Mapper $contentMapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler $fieldHandler
     */
    public function __construct(
        LocationGateway $locationGateway,
        LocationMapper $locationMapper,
        ContentGateway $contentGateway,
        ContentMapper $contentMapper,
        FieldHandler $fieldHandler
    ) {
        $this->locationGateway = $locationGateway;
        $this->locationMapper = $locationMapper;
        $this->contentGateway = $contentGateway;
        $this->contentMapper = $contentMapper;
        $this->fieldHandler = $fieldHandler;
    }

    /**
     * Returns the metadata object for a content identified by $contentId.
     *
     * @param int|string $contentId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    public function loadContentInfo($contentId)
    {
        return $this->contentMapper->extractContentInfoFromRow(
            $this->contentGateway->loadContentInfo($contentId)
        );
    }

    /**
     * Deletes raw content data.
     *
     * @param int $contentId
     */
    public function removeRawContent($contentId)
    {
        $this->locationGateway->removeElementFromTrash(
            $this->loadContentInfo($contentId)->mainLocationId
        );

        foreach ($this->listVersions($contentId) as $versionInfo) {
            $this->fieldHandler->deleteFields($contentId, $versionInfo);
        }
        // Must be called before deleteRelations()
        $this->contentGateway->removeReverseFieldRelations($contentId);
        $this->contentGateway->deleteRelations($contentId);
        $this->contentGateway->deleteVersions($contentId);
        $this->contentGateway->deleteNames($contentId);
        $this->contentGateway->deleteContent($contentId);
    }

    /**
     * Returns the versions for $contentId.
     *
     * @param int $contentId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo[]
     */
    public function listVersions($contentId)
    {
        $rows = $this->contentGateway->listVersions($contentId);
        if (empty($rows)) {
            return array();
        }

        $idVersionPairs = array_map(
            function ($row) use ($contentId) {
                return array(
                    'id' => $contentId,
                    'version' => $row['ezcontentobject_version_version'],
                );
            },
            $rows
        );
        $nameRows = $this->contentGateway->loadVersionedNameData($idVersionPairs);

        return $this->contentMapper->extractVersionInfoListFromRows(
            $rows,
            $nameRows
        );
    }

    /**
     * Loads the data for the location identified by $locationId.
     *
     * @param int $locationId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function loadLocation($locationId)
    {
        $data = $this->locationGateway->getBasicNodeData($locationId);

        return $this->locationMapper->createLocationFromRow($data);
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
        $locationRow = $this->locationGateway->getBasicNodeData($locationId);
        $contentId = $locationRow['contentobject_id'];
        $mainLocationId = $locationRow['main_node_id'];

        $subLocations = $this->locationGateway->getChildren($locationId);
        foreach ($subLocations as $subLocation) {
            $this->removeSubtree($subLocation['node_id']);
        }

        if ($locationId == $mainLocationId) {
            if (1 == $this->locationGateway->countLocationsByContentId($contentId)) {
                $this->removeRawContent($contentId);
            } else {
                $newMainLocationRow = $this->locationGateway->getFallbackMainNodeData(
                    $contentId,
                    $locationId
                );

                $this->changeMainLocation(
                    $contentId,
                    $newMainLocationRow['node_id'],
                    $newMainLocationRow['contentobject_version'],
                    $newMainLocationRow['parent_node_id']
                );
            }
        }

        $this->locationGateway->removeLocation($locationId);
        $this->locationGateway->deleteNodeAssignment($contentId);
    }

    /**
     * Set section on all content objects in the subtree.
     *
     * @param mixed $locationId
     * @param mixed $sectionId
     */
    public function setSectionForSubtree($locationId, $sectionId)
    {
        $nodeData = $this->locationGateway->getBasicNodeData($locationId);

        $this->locationGateway->setSectionForSubtree($nodeData['path_string'], $sectionId);
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
        $parentLocationId = $this->loadLocation($locationId)->parentId;

        // Update ezcontentobject_tree and eznode_assignment tables
        $this->locationGateway->changeMainLocation(
            $contentId,
            $locationId,
            $this->loadContentInfo($contentId)->currentVersionNo,
            $parentLocationId
        );

        // Update subtree section to the one of the new main location parent location content
        $this->setSectionForSubtree(
            $locationId,
            $this->loadContentInfo($this->loadLocation($parentLocationId)->contentId)->sectionId
        );
    }
}
