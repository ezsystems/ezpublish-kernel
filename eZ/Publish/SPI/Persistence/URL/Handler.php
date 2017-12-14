<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\URL;

use eZ\Publish\API\Repository\Values\URL\URLQuery;

/**
 * The URL Handler interface defines operations on URLs in the storage engine.
 */
interface Handler
{
    /**
     * Updates a existing URL.
     *
     * @param int $id
     * @param \eZ\Publish\SPI\Persistence\URL\URLUpdateStruct $urlUpdateStruct
     * @return \eZ\Publish\SPI\Persistence\URL\URL
     */
    public function updateUrl($id, URLUpdateStruct $urlUpdateStruct);

    /**
     * Selects URLs data using $query.
     *
     * @param \eZ\Publish\API\Repository\Values\URL\URLQuery $query
     * @return array
     */
    public function find(URLQuery $query);

    /**
     * Returns IDs of Content Objects using URL identified by $id.
     *
     * @param int $id
     * @return array
     */
    public function findUsages($id);

    /**
     * Loads the data for the URL identified by $id.
     *
     * @param int $id
     * @return \eZ\Publish\SPI\Persistence\URL\URL
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function loadById($id);

    /**
     * Loads the data for the URL identified by $url.
     *
     * @param string $url
     * @return \eZ\Publish\SPI\Persistence\URL\URL
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function loadByUrl($url);
}
