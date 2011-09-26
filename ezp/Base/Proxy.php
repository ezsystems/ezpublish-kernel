<?php
/**
 * Service based model implementation for Proxy object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;

/**
 * Proxy class for model objects
 *
 */
abstract class Proxy
{
    /**
     * Service used to load the object the proxy represents.
     *
     * @var \ezp\Base\Service
     */
    protected $service;

    /**
     * Id of the object
     *
     * @var mixed
     */
    protected $id;

    /**
     * Concrete proxied object
     *
     * @var mixed
     */
    protected $proxiedObject = null;

    /**
     * Setup proxy object with enough info to be able to perform a load operation on the object it proxies.
     *
     * @param mixed $id Primary id
     * @param \ezp\Base\Service $service
     */
    public function __construct( $id, Service $service )
    {
        $this->id = $id;
        $this->service = $service;
    }

    /**
     * Loads the proxied object in the case it has not happened yet.
     */
    protected function lazyLoad()
    {
        if ( $this->proxiedObject === null )
        {
            $this->proxiedObject = $this->service->load( $this->id );
        }
    }

    /**
     * Provides read access to a $property
     *
     * @param string $property
     * @return mixed
     */
    public function __get( $property )
    {
        if ( $property === "id" )
            return $this->id;

        $this->lazyLoad();
        return $this->proxiedObject->$property;
    }

    /**
     * Provides write access to a $property
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set( $property, $value )
    {
        $this->lazyLoad();
        return $this->proxiedObject->$property = $value;
    }

    /**
     * Checks if a public virtual property is set
     *
     * @param string $property Property name
     * @return bool
     */
    public function __isset( $property )
    {
        $this->lazyLoad();
        return isset( $this->proxiedObject->$property );
    }
}
