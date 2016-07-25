<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common;

use eZ\Publish\SPI\Search\IndexerDataProvider as SearchIndexerDataProvider;
use eZ\Publish\SPI\Persistence\Handler;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use PDO;

class IndexerDataProvider implements SearchIndexerDataProvider
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    private $persistenceHandler;
    /**
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    private $databaseHandler;

    public function __construct(Handler $persistenceHandler, DatabaseHandler $databaseHandler)
    {
        $this->persistenceHandler = $persistenceHandler;
        $this->databaseHandler = $databaseHandler;
    }

    /**
     * Get a total number of published content objects.
     *
     * @return int
     */
    public function getPublishedContentCount()
    {
        $query = $this->databaseHandler->createSelectQuery();
        $query->select('count(id)')
            ->from('ezcontentobject')
            ->where($query->expr->eq('status', ContentInfo::STATUS_PUBLISHED));
        $stmt = $query->prepare();
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Get a number of nodes in content object tree.
     *
     * @return int
     */
    public function getLocationsCount()
    {
        $query = $this->databaseHandler->createSelectQuery();
        $query
            ->select('count(node_id)')
            ->from('ezcontentobject_tree')
            ->where(
                $query->expr->neq(
                    $this->databaseHandler->quoteColumn('contentobject_id'),
                    $query->bindValue(0, null, PDO::PARAM_INT)
                )
            );
        $stmt = $query->prepare();
        $stmt->execute();

        return intval($stmt->fetchColumn());
    }

    /**
     * Get content objects ids (and version ids) generator.
     *
     * @return \Generator generating an associative array ('id' => ..., 'current_version' => ...)
     */
    public function getContentObjects()
    {
        $query = $this->databaseHandler->createSelectQuery();
        $query->select('id', 'current_version')
            ->from('ezcontentobject')
            ->where($query->expr->eq('status', ContentInfo::STATUS_PUBLISHED));

        $stmt = $query->prepare();
        $stmt->execute();

        while (false !== ($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
            yield $row;
        }
    }

    /**
     * Get location node ids generator.
     *
     * @return \Generator generating node ids (int)
     */
    public function getLocations()
    {
        $query = $this->databaseHandler->createSelectQuery();
        $query
            ->select('node_id')
            ->from('ezcontentobject_tree')
            ->where(
                $query->expr->neq(
                    $this->databaseHandler->quoteColumn('contentobject_id'),
                    $query->bindValue(0, null, PDO::PARAM_INT)
                )
            );

        $stmt = $query->prepare();
        $stmt->execute();

        while (false !== ($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
            yield $row['node_id'];
        }
    }

    /**
     * Get the raw data of a content object identified by $id and $version, in a struct.
     *
     * @param int $id
     * @param int $currentVersion version number
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    public function loadContentObjectVersion($id, $currentVersion)
    {
        return $this->persistenceHandler->contentHandler()->load($id, $currentVersion);
    }

    /**
     * Load the data for the location identified by $locationId.
     *
     * @param int $locationId
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function loadLocation($locationId)
    {
        return $this->persistenceHandler->locationHandler()->load($locationId);
    }
}
