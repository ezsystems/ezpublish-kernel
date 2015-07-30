<?php

/**
 * File containing the UrlWildcard Handler interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\Persistence\Content\UrlWildcard;

/**
 * The UrlWildcard Handler interface provides nice urls with wildcards management.
 *
 * Its methods operate on a representation of the url alias data structure held
 * inside a storage engine.
 */
interface Handler
{
    /**
     * Creates a new url wildcard.
     *
     * @param string $sourceUrl
     * @param string $destinationUrl
     * @param bool $forward
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard
     */
    public function create($sourceUrl, $destinationUrl, $forward = false);

    /**
     * removes an url wildcard.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     *
     * @param mixed $id
     */
    public function remove($id);

    /**
     * Loads a url wild card.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard
     */
    public function load($id);

    /**
     * Loads all url wild card (paged).
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard[]
     */
    public function loadAll($offset = 0, $limit = -1);
}
