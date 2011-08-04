<?php
/**
 * File contains abstract Service, for services attached to repository
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use ezp\Base\Repository,
    ezp\Persistence\Repository\Handler,
    ezp\Persistence\ValueObject;

/**
 * Abstract Repository Services
 *
 */
abstract class Service
{
    /**
     * @var \ezp\Base\Repository
     */
    protected $repository;

    /**
     * @var ezp\Persistence\Repository\Handler
     */
    protected $handler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param ezp\Base\Repository $repository
     * @param ezp\Persistence\Repository\Handler $handler
     */
    public function __construct( Repository $repository, Handler $handler )
    {
        $this->repository = $repository;
        $this->handler = $handler;
    }

    /**
     * Factory method to build a domain object from a value object $vo returned by Storage Engine.
     *
     * @param ValueObject $vo Value object returned by storage engine
     * @return ezp\Base\Model
     */
    abstract protected function buildDomainObject( ValueObject $vo );
}
