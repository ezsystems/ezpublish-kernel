<?php
/**
 * Content model implementation for Proxy object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\ProxyInterface,
    ezp\Base\Service as BaseService,
    InvalidArgumentException;

/**
 * Proxy class for content model objects
 *
 */
class Proxy implements ProxyInterface
{
    /**
     * Service used to load the object the proxy represents.
     *
     * @var Service
     */
    protected $service;

    /**
     * Id of the object
     *
     * @var int
     */
    protected $id;

    /**
     * Method to use on the service to load the object
     *
     * @var string
     */
    protected $method;

    /**
     * Setup proxy object with enough info to be able to perform a load operation on the object it proxies.
     *
     * @param Service $service
     * @param int $id Primary id
     * @param string $method Optional, defines which function on handler to call, 'load' by default.
     * @throws InvalidArgumentException If $id is not a int value above zero.
     */
    public function __construct( BaseService $service, $id, $method = 'load' )
    {
        $this->service = $service;
        $this->id = (int)$id;
        $this->method = $method;
        if ( $this->id === 0 )
            throw new InvalidArgumentException( "Id parameter needs to be a valid integer above 0!" );
    }

    /**
     * Load the object this proxy object represent
     *
     * @return \ezp\Base\Model
     */
    public function load()
    {
        $fn = $this->method;
        return $this->service->$fn( $this->id );
    }

    /**
     * Provides access to id property
     *
     * @throws InvalidArgumentException
     * @param  string $name
     * @return int
     */
    public function __get( $name )
    {
        if ( $name === 'id' )
            return $this->id;
        throw new InvalidArgumentException( "{$name} is not a valid property on Proxy class" );
    }
}

?>
