<?php
/**
 * File contains User Service
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage user
 */

/**
 * User Service, extends repository with user specific operations
 *
 * @package ezp
 * @subpackage user
 */
namespace ezp\user;
class UserService implements \ezp\base\ServiceInterface
{
    /**
     * @var \ezp\base\Repository
     */
    protected $repository;

    /**
     * @var \ezp\base\StorageEngineInterface
     */
    protected $se;

    /**
     * Setups service with reference to repository object that created it & corresponding storage engine handler
     *
     * @param \ezp\base\Repository $repository
     * @param \ezp\base\StorageEngineInterface $se
     */
    public function __construct( \ezp\base\Repository $repository,
                                 \ezp\base\StorageEngineInterface $se )
    {
        $this->repository = $repository;
        $this->se = $se;
    }

    /**
     * Get an User object by id
     *
     * @param int $id
     * @return User
     * @throws \InvalidArgumentException
     */
    public function load( $id )
    {
        $user = $this->se->getUserHandler()->load( (int) $id );
        if ( !$user )
            throw new \InvalidArgumentException( "Could not find 'User' with id: {$id}" );
        return $user;
    }
}
