<?php
/**
 * File containing the RichText Gateway abstract class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RichText\RichTextStorage;

use eZ\Publish\Core\FieldType\StorageGateway;

/**
 * Abstract gateway class for RichText type.
 * Handles data that is not directly included in raw XML value from the field (i.e. URLs)
 */
abstract class Gateway extends StorageGateway
{
    /**
     * For given array of URL ids returns a hash of corresponding URLs,
     * with URL ids as keys.
     *
     * Non-existent ids are ignored.
     *
     * @param array $urlIds Array of link Ids
     *
     * @return array
     */
    abstract public function getIdUrls( array $urlIds );

    /**
     * For given array of URLs returns a hash of corresponding ids,
     * with URLs as keys.
     *
     * Non-existent URLs are ignored.
     *
     * @param array $urls
     *
     * @return array
     */
    abstract public function getUrlIds( array $urls );

    /**
     * For given array of Content remote ids returns a hash of corresponding
     * Content ids, with remote ids as keys.
     *
     * Non-existent ids are ignored.
     *
     * @param array $linkRemoteIds
     *
     * @return array
     */
    abstract public function getContentIds( array $linkRemoteIds );

    /**
     * Inserts a new $url and returns its id.
     *
     * @param string $url The URL to insert in the database
     *
     * @return mixed
     */
    abstract public function insertUrl( $url );

    /**
     * Creates link to URL with $urlId for field with $fieldId in $versionNo.
     *
     * @param int $urlId
     * @param int $fieldId
     * @param int $versionNo
     *
     * @return void
     */
    abstract public function linkUrl( $urlId, $fieldId, $versionNo );
}
