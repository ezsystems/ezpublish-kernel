<?php

/**
 * File containing the LazyRepositoryFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Publish\API\Repository\Repository;

/**
 * @deprecated This factory is not needed any more as ProxyManager is now used for lazy loading. Use ezpublish.api.repository instead.
 */
class LazyRepositoryFactory
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns a closure which returns ezpublish.api.repository when called.
     *
     * To be used when lazy loading is needed.
     *
     * @return \Closure
     */
    public function buildRepository()
    {
        $repository = $this->repository;

        return function () use ($repository) {
            return $repository;
        };
    }
}
