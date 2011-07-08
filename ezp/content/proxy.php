<?php
/**
 * Content model implementation for Proxy object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage content
 */

/**
 * Proxy class for content model objects
 *
 * @package ezp
 * @subpackage content
 */
namespace ezp\content;
class Proxy implements \ezp\base\Interfaces\Proxy
{
    /**
     * Service used to load the object the proxy represents.
     *
     * @var \ezp\base\Interfaces\Service
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
     * @param \ezp\base\ServiceInterface $service
     * @param int $id Primary id
     * @param string $method Optional, defines which function on handler to call, 'load' by default.
     * @throws \InvalidArgumentException If $id is not a int value above zero.
     */
    public function __construct( \ezp\base\Interfaces\Service $service, $id, $method = 'load' )
    {
        $this->service = $service;
        $this->id = (int) $id;
        $this->method = $method;
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
        $fn = $this->method;
        return $this->service->$fn( $this->id );
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

?>
