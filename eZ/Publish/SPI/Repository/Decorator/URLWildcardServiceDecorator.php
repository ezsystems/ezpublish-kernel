<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\URLWildcardService;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;

abstract class URLWildcardServiceDecorator implements URLWildcardService
{
    /** @var \eZ\Publish\API\Repository\URLWildcardService */
    protected $innerService;

    public function __construct(URLWildcardService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function create(
        string $sourceUrl,
        string $destinationUrl,
        bool $forward = false
    ): UrlWildcard {
        return $this->innerService->create($sourceUrl, $destinationUrl, $forward);
    }

    public function remove(URLWildcard $urlWildcard): void
    {
        $this->innerService->remove($urlWildcard);
    }

    public function load(int $id): UrlWildcard
    {
        return $this->innerService->load($id);
    }

    public function loadAll(
        int $offset = 0,
        int $limit = -1
    ): iterable {
        return $this->innerService->loadAll($offset, $limit);
    }

    public function translate(string $url): URLWildcardTranslationResult
    {
        return $this->innerService->translate($url);
    }
}
