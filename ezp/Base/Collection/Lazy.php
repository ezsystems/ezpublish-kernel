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
     * The variable used for collection lookup
     *
     * @var mixed
     */
    protected $primary;

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
     * @param mixed $primary Primary key to do lookup on
     * @param string $method Optional, defines which function on handler to call, 'load' by default.
     */
    public function __construct( $type, Service $service, $primary, $method = 'load' )
    {
        $this->service = $service;
        $this->primary = $primary;
        $this->method = $method;
        parent::__construct( $type );
    }

    /**
     * Hint to know if collection has been loaded (including partly loaded)
     *
     * Useful for lazy collection to signal that a collection has not been loaded thus
     * skipping updating a collection as it will be correct the moment it is loaded anyway.
     *
     * @access private This is private api for use in service layer!
     * @return bool
     */
    public function isLoaded()
    {
        return $this->primary === false;
    }

    /**
     * Load the objects this proxy object represent
     *
     * @return \ezp\Base\Model
     */
    protected function load()
    {
        if ( $this->primary === false )
            return;
        $fn = $this->method;
        $this->exchangeArray( $this->service->$fn( $this->primary ) );
        $this->primary = false;// signal that loading is done
    }

    /**
     * Overrides getIterator to lazy load items
     *
     * @internal
     * @return object
     */
    public function getIterator()
    {
        $this->load();
        return parent::getIterator();
    }

    /**
     * Overrides offsetGet to lazy load items
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
     * Overrides offsetExists to lazy load items
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
     * Overrides offsetUnset to lazy load items
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
     * Overrides offsetSet to lazy load items
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
