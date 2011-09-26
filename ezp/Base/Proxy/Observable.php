<?php
/**
 * File containing the Observable Proxy class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Proxy;
use ezp\Base\Proxy,
    ezp\Base\Observer,
    ezp\Base\Observable as ObservableBase;

/**
 * Observable Proxy class.
 *
 * Because of lack of traits in PHP < 5.4 we introduce an intermediate class
 * for Proxy objects that need to implement Observable.
 *
 * @internal
 * @see \ezp\Base\Model
 */
abstract class Observable extends Proxy implements ObservableBase
{
    /**
     * List of event listeners
     *
     * @var \ezp\Base\Observer[]
     */
    private $observers = array();

    /**
     * Attaches $observer for $event to the Model
     *
     * @param \ezp\Base\Observer $observer
     * @param string $event
     * @return \ezp\Base\Model
     */
    public function attach( Observer $observer, $event = "update" )
    {
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
}
