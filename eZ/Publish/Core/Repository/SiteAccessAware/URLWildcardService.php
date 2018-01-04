<?php

/**
 * URLWildcardService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\URLWildcardService as URLWildcardServiceInterface;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;

/**
 * URLWildcardService for SiteAccessAware layer.
 *
 * Currently does nothing but hand over calls to aggregated service.
 */
class URLWildcardService implements URLWildcardServiceInterface
{
    /** @var \eZ\Publish\API\Repository\URLWildcardService */
    protected $service;

    /**
     * Construct service object from aggregated service.
     *
     * @param \eZ\Publish\API\Repository\URLWildcardService $service
     */
    public function __construct(
        URLWildcardServiceInterface $service
    ) {
        $this->service = $service;
    }

    public function create($sourceUrl, $destinationUrl, $forward = false)
    {
        return $this->service->create($sourceUrl, $destinationUrl, $forward);
    }

    public function remove(URLWildcard $urlWildcard)
    {
        return $this->service->remove($urlWildcard);
    }

    public function load($id)
    {
        return $this->service->load($id);
    }

    public function loadAll($offset = 0, $limit = -1)
    {
        return $this->service->loadAll($offset, $limit);
    }

    public function translate($url)
    {
        return $this->service->translate($url);
    }
}
