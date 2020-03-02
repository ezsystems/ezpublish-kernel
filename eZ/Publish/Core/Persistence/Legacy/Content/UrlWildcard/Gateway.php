<?php

/**
 * File containing the UrlWildcard Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard;

use eZ\Publish\SPI\Persistence\Content\UrlWildcard;

/**
 * UrlWildcard Gateway.
 *
 * @internal For internal use by Persistence Handlers.
 */
abstract class Gateway
{
    public const URL_WILDCARD_TABLE = 'ezurlwildcard';
    public const URL_WILDCARD_SEQ = 'ezurlwildcard_id_seq';

    /**
     * Inserts the given UrlWildcard.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard $urlWildcard
     *
     * @return mixed UrlWildcard id
     */
    abstract public function insertUrlWildcard(UrlWildcard $urlWildcard): int;

    /**
     * Deletes the UrlWildcard with given $id.
     *
     * @param mixed $id
     */
    abstract public function deleteUrlWildcard(int $id): void;

    /**
     * Loads an array with data about UrlWildcard with $id.
     *
     * @param mixed $id
     *
     * @return array
     */
    abstract public function loadUrlWildcardData(int $id): array;

    /**
     * Loads an array with data about UrlWildcards (paged).
     *
     * @param mixed $offset
     * @param mixed $limit
     *
     * @return array
     */
    abstract public function loadUrlWildcardsData(int $offset = 0, int $limit = -1): array;

    /**
     * Loads the UrlWildcard by source url $sourceUrl.
     *
     * @param string $sourceUrl
     *
     * @return array
     */
    abstract public function loadUrlWildcardBySourceUrl(string $sourceUrl): array;
}
