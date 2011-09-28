<?php
/**
 * File contains LazyIdList Collection class
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
 * LazyIdList Collection class, lazy collection only accepts new elements of a certain type
 *
 * Takes a list of primary id's as input, these are loaded one by one on demand.
 *
 * @todo Remove this in favour of using array/collection with proxy objects to avoid getIterator "mess"
 */
class LazyIdList extends ArrayObject implements Collection
{
    /**
     * @var string The class name (including namespace) to accept as input
     */
    private $type;

    /**
     * Service used to load the object the proxy represents.
     *
     * @var Service
     */
    protected $service;

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
     * @param array $ids Primary id's to do lookup on
     * @param Service $service
     * @param string $method Optional, defines which function on handler to call, 'load' by default.
     */
    public function __construct( $type, array $ids = array(), Service $service, $method = 'load' )
    {
        $this->type = $type;
        $this->service = $service;
        $this->method = $method;
        parent::__construct( $ids );
    }

    /**
     * Load all items
     *
     * @return void
     */
    protected function loadAll()
    {
        $fn = $this->method;
        foreach ( parent::getArrayCopy() as $key => $value )
        {
            if ( $value instanceof $this->type )
                continue;

            $obj = $this->service->$fn( $value );
            parent::offsetSet( $key, $obj );
        }
    }

    /**
     * Overrides getIterator to lazy load items
     *
     * @internal
     * @return object
     */
    public function getIterator()
    {
        $this->loadAll();
        return parent::getIterator();
    }

    /**
     * Return a copy of the internal array
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $this->loadAll();
        return parent::getArrayCopy();
    }

    /**
     * Returns the first index at which a given element can be found in the array, or false if it is not present.
     *
     * Uses strict comparison.
     *
     * @param mixed $item
     * @return int|string|false False if nothing was found
     */
    public function indexOf( $item )
    {
        if ( !$item instanceof $this->type )
            return false;

        foreach ( $this as $key => $value )
            if ( $value->id === $item->id )
                return $key;
        return false;
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
        $value = parent::offsetGet( $index );
        if ( $value instanceof $this->type )
            return $value;

        // lazy load item
        $fn = $this->method;
        $obj = $this->service->$fn( $value );
        $this->offsetSet( $index, $obj );
        return $obj;
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
        // throw if wrong type
        if ( !$value instanceof $this->type )
            throw new InvalidArgumentType( 'value', $this->type, $value );

        // stop if value is already in array
        if ( $this->indexOf( $value ) !== false )
            return;

        parent::offsetSet( $offset, $value );
    }

    /**
     * Overloads exchangeArray() to do type checks on input.
     *
     * @throws InvalidArgumentType
     * @param array $input
     * @return array
     */
    public function exchangeArray( $input )
    {
        foreach ( $input as $item )
        {
            if ( !$item instanceof $this->type )
                throw new InvalidArgumentType( 'input', $this->type, $item );
        }
        return parent::exchangeArray( $input );
    }
}
