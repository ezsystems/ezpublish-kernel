<?php
/**
 * File containing the Value abstract class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType;
use ezp\Base\ModelInterface,
    ezp\Base\Exception\PropertyNotFound;

/**
 * Abstract class for all field value classes.
 * A field value object is to be understood with associated field type
 */
abstract class Value implements ModelInterface
{
    /**
     * Internal properties
     *
     * @internal
     * @var array
     */
    protected $properties = array();

    /**
     * Sets internal variables on object from array
     *
     * Key is property name and value is property value.
     *
     * @internal
     * @param array $state
     * @return mixed
     */
    public function setState( array $state )
    {
        $this->properties = $state + $this->properties;
    }

    /**
     * Gets internal variables on object as array
     *
     * Key is property name and value is property value.
     *
     * @internal
     * @param string|null $property Optional, lets you specify to only return one property by name
     * @return array|mixed Array if $property is null, else value of property
     * @throws \ezp\Base\Exception\PropertyNotFound If $property is not found (when not null)
     */
    public function getState( $property = null )
    {
        if ( $property === null )
            return $this->properties;

        if ( !array_key_exists( $property, $this->properties ) )
            throw new PropertyNotFound( $property, get_class() );

        return $this->properties[$property];
    }
}
