<?php
/**
 * File containing the Validator base class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType;
use ezp\Base\Exception\PropertyNotFound,
    eZ\Publish\Core\Repository\FieldType\Value;

/**
 * Base field type validator validator.
 */
abstract class Validator
{
    protected $errors = array();

    /**
     * Hash of constraints handled by the validator.
     * Key is the constraint name, value is the default value.
     * If no default value is needed, just set to false.
     *
     * Example:
     * <code>
     * // With no default value
     * protected $constraints = array(
     *     "maxStringLength" => false
     * );
     *
     * // With a default value
     * protected $constraints = array(
     *     "minIntegerValue" => 0,
     *     "maxIntegerValue" => 40
     * );
     * </code>
     *
     * @var array
     */
    protected $constraints = array();

    /**
     * Perform validation on $value.
     *
     * Will return true when all constraints are matched. If one or more
     * constraints fail, the method will return false.
     *
     * When a check against a constraint has failed, an entry will be added to the
     * $errors array.
     *
     * @abstract
     * @param \eZ\Publish\Core\Repository\FieldType\Value $value
     * @return bool
     */
    abstract public function validate( Value $value );

    /**
     * Return array of messages on performed validations.
     *
     * When no validation errors occured, the returned array should be empty.
     *
     * @return array
     */
    public function getMessage()
    {
        return $this->errors;
    }

    /**
     * Combines configurable constraints in the validator and creates a map.
     *
     * This map is then supposed to be used inside a FieldDefinition.
     *
     * @internal
     * @return array
     */
    public function getValidatorConstraints()
    {
        return $this->constraints;
    }

    /**
     * Initialized an instance of Validator, with earlier configured constraints.
     *
     * @internal
     * @throws \ezp\Base\Exception\PropertyNotFound
     * @param array $constraints
     * @return void
     */
    public final function initializeWithConstraints( array $constraints )
    {
        foreach ( $constraints as $constraint => $value )
        {
            if ( !isset( $this->constraints[$constraint] ) )
            {
                throw new PropertyNotFound( "The constraint, {$constraint}, is not valid for this validator." );
            }

            $this->constraints[$constraint] = $value;
        }
    }

    /**
     * Magic getter.
     * Returns constraint value, from its $name
     *
     * @param string $name
     * @return mixed
     * @throws \ezp\Base\Exception\PropertyNotFound
     */
    public function __get( $name )
    {
        if ( !isset( $this->constraints[$name] ) )
            throw new PropertyNotFound( "The constraint, {$name}, is not valid for this validator." );

        return $this->constraints[$name];
    }

    /**
     * Magic setter.
     * Sets $value to constraint, identified by $name
     *
     * @param string $name
     * @param mixed $value
     * @throws \ezp\Base\Exception\PropertyNotFound
     */
    public function __set( $name, $value )
    {
        if ( !isset( $this->constraints[$name] ) )
            throw new PropertyNotFound( "The constraint, {$name}, is not valid for this validator." );

        $this->constraints[$name] = $value;
    }
}
