<?php

declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\URLWildcardService;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;

abstract class URLWildcardServiceDecorator implements URLWildcardService
{
    /** @var eZ\Publish\API\Repository\URLWildcardService */
    protected $innerService;

    /**
     * @param eZ\Publish\API\Repository\URLWildcardService
     */
    public function __construct(URLWildcardService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function create($sourceUrl, $destinationUrl, $forward = false)
    {
        $this->innerService->create($sourceUrl, $destinationUrl, $forward);
    }

    public function remove(URLWildcard $urlWildcard)
    {
        $this->innerService->remove($urlWildcard);
    }

    public function load($id)
    {
        $this->innerService->load($id);
    }

    public function loadAll($offset = 0, $limit = -1)
    {
        $this->innerService->loadAll($offset, $limit);
    }

    public function translate($url)
    {
        $this->innerService->translate($url);
    }
}
