<?php

/**
 * File containing the RepositoryAwareInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC;

use eZ\Publish\API\Repository\Repository;

interface RepositoryAwareInterface
{
    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function setRepository(Repository $repository);
}
