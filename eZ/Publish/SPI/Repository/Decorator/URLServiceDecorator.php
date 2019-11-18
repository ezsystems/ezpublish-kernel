<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\URLService;
use eZ\Publish\API\Repository\Values\URL\SearchResult;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;
use eZ\Publish\API\Repository\Values\URL\UsageSearchResult;

abstract class URLServiceDecorator implements URLService
{
    /** @var \eZ\Publish\API\Repository\URLService */
    protected $innerService;

    public function __construct(URLService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createUpdateStruct(): URLUpdateStruct
    {
        return $this->innerService->createUpdateStruct();
    }

    public function findUrls(URLQuery $query): SearchResult
    {
        return $this->innerService->findUrls($query);
    }

    public function findUsages(
        URL $url,
        int $offset = 0,
        int $limit = -1
    ): UsageSearchResult {
        return $this->innerService->findUsages($url, $offset, $limit);
    }

    public function loadById(int $id): URL
    {
        return $this->innerService->loadById($id);
    }

    public function loadByUrl(string $url): URL
    {
        return $this->innerService->loadByUrl($url);
    }

    public function updateUrl(
        URL $url,
        URLUpdateStruct $struct
    ): URL {
        return $this->innerService->updateUrl($url, $struct);
    }
}
