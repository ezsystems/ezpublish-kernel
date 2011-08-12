<?php
/**
 * File contains Lazy Collection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Collection;
use ezp\Base\Collection,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Model,
    ezp\Base\Service,
    ezp\Base\Collection\Type as TypeCollection,
    ArrayObject;

/**
 * Lazy Collection class, lazy collection only accepts new elements of a certain type
 *
 * Takes a primary id as input, items connected to this id will be loaded when collection is first accessed.
 *
 */
class Lazy extends TypeCollection
{
    /**
     * Service used to load the object the proxy represents.
     *
     * @var Service
     */
    protected $service;

    /**
     * Primary id used for collection lookup
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
     * Construct object and assign internal array values
     *
     * A type strict collection that throws exception if type is wrong when appended to.
     *
     * @throws InvalidArgumentType If elements contains item of wrong type
     * @param string $type
     * @param Service $service
     * @param array $id Primary id to do lookup on
     * @param string $method Optional, defines which function on handler to call, 'load' by default.
     */
    public function __construct( $type, Service $service, $id, $method = 'load' )
    {
        $this->service = $service;
        $this->id = $id;
        $this->method = $method;
        parent::__construct( $type );
    }

    /**
     * Load the objects this proxy object represent
     *
     * @return \ezp\Base\Model
     */
    protected function load()
    {
        if ( $this->id === false )
            return;
        $fn = $this->method;
        $this->exchangeArray( $this->service->$fn( $this->id ) );
        $this->id = false;// signal that loading is done
    }

    /**
     * Overrides offsetGet to lazy load item
     *
     * @internal
     * @param string|int $index
     * @return object
     */
    public function offsetGet( $index )
    {
        $this->load();
        return parent::offsetGet( $index );
    }

    /**
     * Overrides offsetExists to lazy load item
     *
     * @internal
     * @param string|int $index
     * @return bool
     */
    public function offsetExists( $index )
    {
        $this->load();
        return parent::offsetExists( $index );
    }

    /**
     * Overrides offsetUnset to lazy load item
     *
     * @internal
     * @param string|int $index
     */
    public function offsetUnset( $index )
    {
        $this->load();
        parent::offsetUnset( $index );
    }

    /**
     * Overrides offsetSet to check type and allow if correct
     *
     * @internal
     * @throws InvalidArgumentType On wrong type
     * @param string|int $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        $this->load();
        parent::offsetSet( $offset, $value );
    }

    /**
     * Overrides count to lazy load items
     *
     * @internal
     * @return int
     */
    public function count()
    {
        $this->load();
        return parent::count();
    }
}


?>
