<?php

/**
 * URLWildcardService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\URLService as URLServiceInterface;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;

/**
 * URLWildcardService for SiteAccessAware layer.
 *
 * Currently does nothing but hand over calls to aggregated service.
 */
class URLService implements URLServiceInterface
{
    /** @var \eZ\Publish\API\Repository\URLService */
    protected $service;

    /**
     * Construct service object from aggregated service.
     *
     * @param \eZ\Publish\API\Repository\URLService $service
     */
    public function __construct(
        URLServiceInterface $service
    )
    {
        $this->service = $service;
    }

    function createUpdateStruct()
    {
        return $this->service->createUpdateStruct();
    }

    public function findUrls(URLQuery $query)
    {
        return $this->service->findUrls($query);
    }

    public function findUsages(URL $url, $offset = 0, $limit = -1)
    {
        return $this->service->findUsages($url, $offset, $limit);
    }

    public function loadById($id)
    {
        return $this->service->loadById($id);
    }

    public function loadByUrl($url)
    {
        return $this->service->loadByUrl($url);
    }

    public function updateUrl(URL $url, URLUpdateStruct $struct)
    {
        return $this->service->updateUrl($url, $struct);
    }
}
