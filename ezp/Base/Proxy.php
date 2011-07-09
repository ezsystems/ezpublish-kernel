<?php
/**
 * Generic implementation for Proxy object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage base
 */

/**
 * Proxy class for content model objects
 *
 * @package ezp
 * @subpackage base
 */
namespace ezp\Base;
class Proxy implements Interfaces\Proxy
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
     * @throws Exception\InvalidArgumentType If $id is not a int value above zero.
     */
    public function __construct( Repository $repository, $type, $id )
    {
        $this->repository = $repository;
        $this->type = $type;
        $this->id = (int) $id;
        if ( $this->id === 0 )
            throw new Exception\InvalidArgumentType( 'id', 'int' );
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
     * @throws Exception\PropertyNotFound
     * @param  string $name
     * @return int
     */
    public function __get( $name )
    {
        if ( $name === 'id' )
            return $this->id;
        throw new Exception\PropertyNotFound( $name );
    }
}
