<?php
/**
 * File containing the Validator base class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType;
use ezp\Base\Exception\PropertyNotFound;

/**
 * Base field type validator validator.
 */
abstract class Validator
{
    protected $errors = array();

    /**
     * Returns the name of the validator.
     *
     * @abstract
     * @return string
     */
    abstract public function name();

    /**
     * Perform validation on $value.
     *
     * Will return true when all constraints are matched. If one or more
     * constraints fail, the method will return false.
     *
     * When a check against aconstaint has failed, an entry will be added to the
     * $errors array.
     *
     * @abstract
     * @param mixed $value
     * @return bool
     */
    abstract public function validate( $value );

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
     * @abstract
     * @internal
     * @return array
     */
    abstract public function getValidatorConstraints();


    /**
     * Initialized an instance of Validator, with earlier configured constraints.
     *
     * @internal
     * @throws \ezp\Base\Exception\PropertyNotFound
     * @param array $constraints
     * @return void
     */
    public function initializeWithConstraints( array $constraints )
    {
        foreach ( $constraints as $constraint => $value )
        {
            if ( !property_exists( $this, $constraint ) )
            {
                throw new  PropertyNotFound( "The constraint, {$constraint}, is not valid for for this validator." );
            }

            $this->$constraint = $value;
        }
    }
}
