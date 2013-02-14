<?php
/**
 * File containing the Validator base class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType;

use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException as PropertyNotFound;

/**
 * Base field type validator validator.
 */
abstract class Validator
{
    /**
     * The errors collected during validation
     *
     * @var \eZ\Publish\SPI\FieldType\ValidationError[]
     */
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
     * A one dimensional map with validator parameters
     *
     * @var mixed
     */
    protected $constraintsSchema = array();

    /**
     * Returns a schema for supported validator configurations.
     *
     * This implementation returns a three dimensional map containing for each validator configuration
     * referenced by identifier a map of supported parameters which are defined by a type and a default value
     * (see example).
     * Example:
     * <code>
     *  array(
     *      'stringLength' => array(
     *          'minStringLength' => array(
     *              'type'    => 'int',
     *              'default' => 0,
     *          ),
     *          'maxStringLength' => array(
     *              'type'    => 'int'
     *              'default' => null,
     *          )
     *      ),
     *  );
     * </code>
     * The validator identifier.
     */
    public function getConstraintsSchema()
    {
        return $this->constraintsSchema;
    }

    /**
     * @param mixed $constraints
     *
     * @return mixed
     */
    abstract public function validateConstraints( $constraints );

    /**
     * Perform validation on $value.
     *
     * Will return true when all constraints are matched. If one or more
     * constraints fail, the method will return false.
     *
     * When a check against a constraint has failed, an entry will be added to the
     * $errors array.
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     *
     * @return boolean
     */
    abstract public function validate( Value $value );

    /**
     * Returns array of messages on performed validations.
     *
     * When no validation errors occurred, the returned array should be empty.
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function getMessage()
    {
        return $this->errors;
    }

    /**
     * Initialized an instance of Validator, with earlier configured constraints.
     *
     * @internal
     * @throws \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     * @param array $constraints
     *
     * @return void
     */
    public function initializeWithConstraints( array $constraints )
    {
        // Reset errors
        $this->errors = array();
        // Set existing constraint values to false
        foreach ( $this->constraints as $constraint => $value )
        {
            $this->constraints[$constraint] = false;
        }
        // Initialize constraints with new values
        foreach ( $constraints as $constraint => $value )
        {
            if ( !isset( $this->constraints[$constraint] ) )
            {
                throw new PropertyNotFound( "The constraint '{$constraint}' is not valid for this validator." );
            }

            $this->constraints[$constraint] = $value;
        }
    }

    /**
     * Magic getter.
     * Returns constraint value, from its $name
     *
     * @param string $name
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     *
     * @return mixed
     */
    public function __get( $name )
    {
        if ( !isset( $this->constraints[$name] ) )
        {
            throw new PropertyNotFound( "The constraint '{$name}' is not valid for this validator." );
        }

        return $this->constraints[$name];
    }

    /**
     * Magic setter.
     * Sets $value to constraint, identified by $name
     *
     * @param string $name
     * @param mixed $value
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function __set( $name, $value )
    {
        if ( !isset( $this->constraints[$name] ) )
        {
            throw new PropertyNotFound( "The constraint '{$name}' is not valid for this validator." );
        }

        $this->constraints[$name] = $value;
    }
}
