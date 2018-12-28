<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

use eZ\Publish\API\Repository\URLService;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;

class URLServiceDecorator implements URLService
{
    /**
     * @var \eZ\Publish\API\Repository\URLService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\URLService  $service
     */
    public function __construct(URLService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function createUpdateStruct()
    {
        return $this->service->createUpdateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function findUrls(URLQuery $query)
    {
        return $this->service->findUrls($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findUsages(URL $url, $offset = 0, $limit = -1)
    {
        return $this->service->findUsages($url, $offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function loadById($id)
    {
        return $this->service->loadById($id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadByUrl($url)
    {
        return $this->service->loadByUrl($url);
    }

    /**
     * {@inheritdoc}
     */
    public function updateUrl(URL $url, URLUpdateStruct $struct)
    {
        return $this->service->updateUrl($url, $struct);
    }
}
