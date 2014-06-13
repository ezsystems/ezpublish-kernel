<?php
/**
 * File containing the abstract Url Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Url\UrlStorage;

use eZ\Publish\Core\FieldType\StorageGateway;

/**
 * Abstract gateway class for Url field type.
 * Handles URL data.
 */
abstract class Gateway extends StorageGateway
{
    /**
     * For given array of URL ids returns a hash of corresponding URLs,
     * with URL ids as keys.
     *
     * Non-existent ids are ignored.
     *
     * @param int[]|string[] $ids An array of link Ids
     *
     * @return array
     */
    abstract public function getIdUrlMap( array $ids );

    /**
     * For given array of URLs returns a hash of corresponding ids,
     * with URLs as keys.
     *
     * Non-existent URLs are ignored.
     *
     * @param string[] $urls An array of URLs
     *
     * @return array
     */
    abstract public function getUrlIdMap( array $urls );

    /**
     * Inserts a new $url and returns its id.
     *
     * @param string $url The URL to insert in the database
     *
     * @return int|string
     */
    abstract public function insertUrl( $url );

    /**
     * Creates link to URL with $urlId for field with $fieldId in $versionNo.
     *
     * @param int|string $urlId
     * @param int|string $fieldId
     * @param int $versionNo
     */
    abstract public function linkUrl( $urlId, $fieldId, $versionNo );

    /**
     * Removes link to URL for $fieldId in $versionNo and cleans up possibly orphaned URLs.
     *
     * @param int|string $fieldId
     * @param int $versionNo
     */
    abstract public function unlinkUrl( $fieldId, $versionNo );
}
