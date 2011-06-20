<?php
/**
 * User Service
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage user
 */

/**
 * User Service, extends repository with user specific operations
 */
namespace ezx\user;
class UserService implements \ezx\base\Interfaces\Service
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * Setups current instance with reference to repository object that created it.
     *
     * @param Repository $repository
     */
    public function __construct( \ezx\base\Interfaces\Repository $repository )
    {
        $this->repository = $repository;
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
        $content = $this->repository->em->find( "ezx\user\User", (int) $id );
        if ( !$content )
            throw new \InvalidArgumentException( "Could not find 'User' with id: {$id}" );
        return $content;
    }
}
