<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\URL\SearchResult;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;
use eZ\Publish\API\Repository\Values\URL\UsageSearchResult;

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
    public function createUpdateStruct(): URLUpdateStruct;

    /**
     * Find URLs.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @param \eZ\Publish\API\Repository\Values\URL\URLQuery $query
     *
     * @return \eZ\Publish\API\Repository\Values\URL\SearchResult
     */
    public function findUrls(URLQuery $query): SearchResult;

    /**
     * Find content objects using URL.
     *
     * Content is filter by user permissions.
     *
     * @param \eZ\Publish\API\Repository\Values\URL\URL $url
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\URL\UsageSearchResult
     */
    public function findUsages(URL $url, int $offset = 0, int $limit = -1): UsageSearchResult;

    /**
     * Load single URL (by ID).
     *
     * @param int $id ID of URL
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @return \eZ\Publish\API\Repository\Values\URL\URL
     */
    public function loadById(int $id): URL;

    /**
     * Load single URL (by URL).
     *
     * @param string $url URL
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @return \eZ\Publish\API\Repository\Values\URL\URL
     */
    public function loadByUrl(string $url): URL;

    /**
     * Updates URL.
     *
     * @param \eZ\Publish\API\Repository\Values\URL\URL $url
     * @param \eZ\Publish\API\Repository\Values\URL\URLUpdateStruct $struct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the url already exists
     *
     * @return \eZ\Publish\API\Repository\Values\URL\URL
     */
    public function updateUrl(URL $url, URLUpdateStruct $struct): URL;
}
