<?php

/**
 * File containing the MigrationHandlerInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration;

use eZ\Publish\SPI\IO\BinaryFile;

interface MigrationHandlerInterface
{
    /**
     * Set the from/to handlers based on identifiers.
     * Returns the MigrationHandler.
     *
     * @param string $fromMetadataHandlerIdentifier
     * @param string $fromBinarydataHandlerIdentifier
     * @param string $toMetadataHandlerIdentifier
     * @param string $toBinarydataHandlerIdentifier
     *
     * @return MigrationHandlerInterface
     */
    public function setIODataHandlersByIdentifiers(
        $fromMetadataHandlerIdentifier,
        $fromBinarydataHandlerIdentifier,
        $toMetadataHandlerIdentifier,
        $toBinarydataHandlerIdentifier
    );

    /**
     * Count the number of existing files.
     *
     * @return int|null Number of files, or null if they cannot be counted
     */
    public function countFiles();

    /**
     * Loads and returns metadata for files, optionally limited by $limit and $offset.
     *
     * @param int|null $limit The number of files to load data for, or null
     * @param int|null $offset The offset used when loading in batches, or null
     *
     * @return BinaryFile[]
     */
    public function loadMetadataList($limit = null, $offset = null);

    /**
     * Migrate a file.
     *
     * @param BinaryFile $binaryFile Information about the file
     *
     * @return bool Success or failure
     */
    public function migrateFile(BinaryFile $binaryFile);
}
