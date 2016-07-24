<?php
/**
 * This file is part of the ezplatform package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\ValueLoaders;

use eZ\Publish\API\Repository\Repository;

class RepositoryBased
{
    /**
     * @var Repository
     */
    private $repository;

    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getRepository()
    {
        return $this->repository;
    }
}
