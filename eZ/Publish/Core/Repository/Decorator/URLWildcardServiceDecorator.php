<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

use eZ\Publish\API\Repository\URLWildcardService;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;

abstract class URLWildcardServiceDecorator implements URLWildcardService
{
    /**
     * @var \eZ\Publish\API\Repository\URLWildcardService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\URLWildcardService $service
     */
    public function __construct(URLWildcardService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function create($sourceUrl, $destinationUrl, $forward = false)
    {
        return $this->service->create($sourceUrl, $destinationUrl, $forward);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(URLWildcard $urlWildcard)
    {
        return $this->service->remove($urlWildcard);
    }

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        return $this->service->load($id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadAll($offset = 0, $limit = -1)
    {
        return $this->service->loadAll($offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function translate($url)
    {
        return $this->service->translate($url);
    }
}
