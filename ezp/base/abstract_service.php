<?php
/**
 * File contains Service Abstract, for services attached to repository
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

namespace ezp\base;

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
     * @var \ezp\persistence\Interfaces\RepositoryHandler
     */
    protected $handler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param Repository $repository
     * @param \ezp\persistence\Interfaces\RepositoryHandler $handler
     */
    public function __construct( Repository $repository, \ezp\persistence\Interfaces\RepositoryHandler $handler )
    {
        $this->repository = $repository;
        $this->handler = $handler;
    }
}
