<?php
/**
 * File containing the Observable Proxy class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Proxy;
use ezp\Base\ModelState,
    ezp\Base\Observable as ObservableInterface,
    ezp\Base\Observer,
    ezp\Base\Proxy;

/**
 * Model Proxy class.
 *
 * Because of lack of traits in PHP < 5.4 we introduce an intermediate class
 * for Proxy objects that need to implement Observable and ModelState.
 * This might change in the future, so never check if a class implemented this abstract, only it's interfaces!
 *
 * @internal
 * @see \ezp\Base\Model
 */
abstract class Model extends Proxy implements ObservableInterface, ModelState
{
    /**
     * List of event listeners
     *
     * @var \ezp\Base\Observer[]
     */
    private $observers = array();

    /**
     * Loads the proxied object and moves observers over to it
     */
    protected function lazyLoad()
    {
        if ( $this->proxiedObject === null )
        {
            parent::lazyLoad();
            $this->moveObservers();
        }
    }

    /**
     * Move observers from proxy to proxiedObject, must be done after load and
     * proxiedObject must be a observable
     *
     * @return void
     */
    protected function moveObservers()
    {
        if ( empty( $this->observers ) )
            return;

        foreach ( $this->observers as $event => $observers )
        {
            foreach ( $observers as $observer )
                $this->proxiedObject->attach( $observer, $event );
        }
        $this->observers = array();
    }

    /**
     * Attaches $observer for $event to the Model
     *
     * @param \ezp\Base\Observer $observer
     * @param string $event
     * @return \ezp\Base\Model
     */
    public function attach( Observer $observer, $event = "update" )
    {
        if ( $this->proxiedObject !== null )
        {
            return $this->proxiedObject->attach( $observer, $event );
        }

        if ( isset( $this->observers[$event] ) )
        {
            $this->observers[$event][] = $observer;
        }
        else
        {
            $this->observers[$event] = array( $observer );
        }
        return $this;
    }

    /**
     * Detaches $observer for $event from the Model
     *
     * @param \ezp\Base\Observer $observer
     * @param string $event
     * @return \ezp\Base\Model
     */
    public function detach( Observer $observer, $event = "update" )
    {
        if ( $this->proxiedObject !== null )
        {
            return $this->proxiedObject->detach( $observer, $event );
        }

        if ( !empty( $this->observers[$event] ) )
        {
            foreach ( $this->observers[$event] as $key => $obj )
            {
                if ( $obj === $observer )
                    unset( $this->observers[$event][$key] );
            }
        }
        return $this;
    }

    /**
     * Notifies registered observers about $event
     *
     * @param string $event
     * @param array|null $arguments
     * @return \ezp\Base\Model
     */
    public function notify( $event = "update", array $arguments = null )
    {
        $this->lazyLoad();
        return $this->proxiedObject->notify( $event, $arguments );
    }

    /**
     * Sets internal variables on object from array
     *
     * Key is property name and value is property value.
     *
     * @access private
     * @param array $state
     * @return \ezp\Base\Model
     * @throws \ezp\Base\Exception\PropertyNotFound If one of the properties in $state is not found
     */
    public function setState( array $state )
    {
        $this->lazyLoad();
        return $this->proxiedObject->setState( $state );
    }

    /**
     * Gets internal variables on object as array
     *
     * Key is property name and value is property value.
     *
     * @access private
     * @param string|null $property Optional, lets you specify to only return one property by name
     * @return array|mixed Array if $property is null, else value of property
     * @throws \ezp\Base\Exception\PropertyNotFound If $property is not found (when not null)
     */
    public function getState( $property = null )
    {
        $this->lazyLoad();
        return $this->proxiedObject->getState( $property );
    }
}
