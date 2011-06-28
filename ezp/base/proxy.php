<?php
/**
 * Generic implementation for Proxy object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

/**
 * Proxy class for content model objects
 *
 * @package ezp
 * @subpackage base
 */
namespace ezp\base;
class Proxy implements ProxyInterface
{
    /**
     * Instance of repository for fetching the object
     *
     * @var Repository
     */
    protected $repository;

    /**
     * Id of the object
     *
     * @var int
     */
    protected $id;

    /**
     * The model class
     *
     * @var string
     */
    protected $type;

    /**
     * Setup proxy object with enough info to be able to perform a load operation on the object it proxies.
     *
     * @param Repository $repository
     * @param string $type The type of object this Proxy object represent
     * @param int $id Primary id
     * @throws \InvalidArgumentException If $id is not a int value above zero.
     */
    public function __construct( Repository $repository, $type, $id )
    {
        $this->repository = $repository;
        $this->type = $type;
        $this->id = (int) $id;
        if ( $this->id === 0 )
            throw new \InvalidArgumentException( "Id parameter needs to be a valid integer above 0!" );
    }

    /**
     * Load the object this proxy object represent
     *
     * @return AbstractModel
     */
    public function load()
    {
        return $this->repository->load( $this->type, $this->id );
    }

    /**
     * Provides access to id property
     *
     * @throws \InvalidArgumentException
     * @param  string $name
     * @return int
     */
    public function __get( $name )
    {
        if ( $name === 'id' )
            return $this->id;
        throw new \InvalidArgumentException( "{$name} is not a valid property on Proxy class" );
    }
}
