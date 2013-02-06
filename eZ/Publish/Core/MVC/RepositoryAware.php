<?php
/**
 * File containing the RepositoryAware class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC;

use eZ\Publish\API\Repository\Repository;

abstract class RepositoryAware implements RepositoryAwareInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function setRepository( Repository $repository )
    {
        $this->repository = $repository;
    }
}
