<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\URLWildcardService;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;

abstract class URLWildcardServiceDecorator implements URLWildcardService
{
    /** @var \eZ\Publish\API\Repository\URLWildcardService */
    protected $innerService;

    public function __construct(URLWildcardService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function create(
        $sourceUrl,
        $destinationUrl,
        $forward = false
    ) {
        return $this->innerService->create($sourceUrl, $destinationUrl, $forward);
    }

    public function remove(URLWildcard $urlWildcard)
    {
        return $this->innerService->remove($urlWildcard);
    }

    public function load($id)
    {
        return $this->innerService->load($id);
    }

    public function loadAll(
        $offset = 0,
        $limit = -1
    ) {
        return $this->innerService->loadAll($offset, $limit);
    }

    public function translate($url)
    {
        return $this->innerService->translate($url);
    }
}
