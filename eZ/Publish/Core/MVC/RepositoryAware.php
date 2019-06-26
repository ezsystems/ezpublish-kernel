<?php

/**
 * File containing the RepositoryAware class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC;

use eZ\Publish\API\Repository\Repository;

abstract class RepositoryAware implements RepositoryAwareInterface
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }
}
