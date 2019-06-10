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
 */
abstract class Gateway
{
    /**
     * Inserts the given UrlWildcard.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard $urlWildcard
     *
     * @return mixed UrlWildcard id
     */
    abstract public function insertUrlWildcard(UrlWildcard $urlWildcard);

    /**
     * Deletes the UrlWildcard with given $id.
     *
     * @param mixed $id
     */
    abstract public function deleteUrlWildcard($id);

    /**
     * Loads an array with data about UrlWildcard with $id.
     *
     * @param mixed $id
     *
     * @return array
     */
    abstract public function loadUrlWildcardData($id);

    /**
     * Loads an array with data about UrlWildcards (paged).
     *
     * @param mixed $offset
     * @param mixed $limit
     *
     * @return array
     */
    abstract public function loadUrlWildcardsData($offset = 0, $limit = -1);

    /**
     * Loads the UrlWildcard by source url $sourceUrl.
     *
     * @param string $sourceUrl
     *
     * @return array
     */
    abstract public function loadUrlWildcardBySourceUrl(string $sourceUrl): array;
}
