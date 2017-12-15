<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;

/**
 * URL Service.
 */
interface URLService
{
    /**
     * Instantiates a new URL update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\URL\URLUpdateStruct
     */
    public function createUpdateStruct();

    /**
     * Find URLs.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @param \eZ\Publish\API\Repository\Values\URL\URLQuery $query
     * @return \eZ\Publish\API\Repository\Values\URL\SearchResult
     */
    public function findUrls(URLQuery $query);

    /**
     * Find content objects using URL.
     *
     * Content is filter by user permissions.
     *
     * @param \eZ\Publish\API\Repository\Values\URL\URL $url
     * @param int $offset
     * @param int $limit
     * @return \eZ\Publish\API\Repository\Values\URL\UsageSearchResult
     */
    public function findUsages(URL $url, $offset = 0, $limit = -1);

    /**
     * Load single URL (by ID).
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @param int $id ID of URL
     * @return \eZ\Publish\API\Repository\Values\URL\URL
     */
    public function loadById($id);

    /**
     * Load single URL (by URL).
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @param string $url url
     * @return \eZ\Publish\API\Repository\Values\URL\URL
     */
    public function loadByUrl($url);

    /**
     * Updates URL.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the url already exists
     *
     * @param \eZ\Publish\API\Repository\Values\URL\URL $url
     * @param \eZ\Publish\API\Repository\Values\URL\URLUpdateStruct $struct
     * @return \eZ\Publish\API\Repository\Values\URL\URL
     */
    public function updateUrl(URL $url, URLUpdateStruct $struct);
}
