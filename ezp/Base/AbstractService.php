<?php
/**
 * File contains Service Abstract, for services attached to repository
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base
 */

namespace ezp\Base;

/**
 * Abstract Repository Services
 *
 * @package ezp
 * @subpackage base
 */
abstract class AbstractService
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var \ezp\Persistence\Interfaces\RepositoryHandler
     */
    protected $handler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param Repository $repository
     * @param \ezp\Persistence\Interfaces\RepositoryHandler $handler
     */
    public function __construct( Repository $repository, \ezp\Persistence\Interfaces\RepositoryHandler $handler )
    {
        $this->repository = $repository;
        $this->handler = $handler;
    }
}
