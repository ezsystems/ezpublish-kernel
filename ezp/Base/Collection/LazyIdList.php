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
        if ( in_array( $value, $this->getArrayCopy(), true ) )
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


?>
