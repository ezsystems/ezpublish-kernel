<?php

/**
 * File containing the abstract Url Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Url\UrlStorage;

use eZ\Publish\SPI\FieldType\StorageGateway;

/**
 * Abstract gateway class for Url field type.
 * Handles URL data.
 */
abstract class Gateway extends StorageGateway
{
    /**
     * Returns a list of URLs for a list of URL ids.
     *
     * Non-existent ids are ignored.
     *
     * @param int[]|string[] $ids An array of URL ids
     *
     * @return array An array of URLs, with ids as keys
     */
    abstract public function getIdUrlMap(array $ids);

    /**
     * Returns a list of URL ids for a list of URLs.
     *
     * Non-existent URLs are ignored.
     *
     * @param string[] $urls An array of URLs
     *
     * @return array An array of URL ids, with URLs as keys
     */
    abstract public function getUrlIdMap(array $urls);

    /**
     * Inserts a new $url and returns its id.
     *
     * @param string $url The URL to insert in the database
     *
     * @return int|string
     */
    abstract public function insertUrl($url);

    /**
     * Creates link to URL with $urlId for field with $fieldId in $versionNo.
     *
     * @param int|string $urlId
     * @param int|string $fieldId
     * @param int $versionNo
     */
    abstract public function linkUrl($urlId, $fieldId, $versionNo);

    /**
     * Removes link to URL for $fieldId in $versionNo and cleans up possibly orphaned URLs.
     *
     * @param int|string $fieldId
     * @param int $versionNo
     */
    abstract public function unlinkUrl($fieldId, $versionNo);
}
