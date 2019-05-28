<?php

declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\URLService;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;

abstract class URLServiceDecorator implements URLService
{
    /** @var eZ\Publish\API\Repository\URLService */
    protected $innerService;

    /**
     * @param eZ\Publish\API\Repository\URLService
     */
    public function __construct(URLService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createUpdateStruct()
    {
        $this->innerService->createUpdateStruct();
    }

    public function findUrls(URLQuery $query)
    {
        $this->innerService->findUrls($query);
    }

    public function findUsages(URL $url, $offset = 0, $limit = -1)
    {
        $this->innerService->findUsages($url, $offset, $limit);
    }

    public function loadById($id)
    {
        $this->innerService->loadById($id);
    }

    public function loadByUrl($url)
    {
        $this->innerService->loadByUrl($url);
    }

    public function updateUrl(URL $url, URLUpdateStruct $struct)
    {
        $this->innerService->updateUrl($url, $struct);
    }
}
