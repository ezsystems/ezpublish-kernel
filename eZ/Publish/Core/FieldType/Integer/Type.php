<?php
/**
 * File containing the Integer field type
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Integer;
use eZ\Publish\Core\FieldType\FieldType,
    ez\Publish\Core\Repository\ValidatorService,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    eZ\Publish\Core\FieldType\ValidationError;

/**
 * Integer field types
 *
 * Represents integers.
 */
class Type extends FieldType
{
    protected $validatorConfigurationSchema = array(
        "IntegerValueValidator" => array(
            "minIntegerValue" => array(
                "type" => "int",
                "default" => 0
            ),
            "maxIntegerValue" => array(
                "type" => "int",
                "default" => false
            )
        )
    );

    /**
     * Holds an instance of validator service
     *
     * @var \eZ\Publish\Core\Repository\ValidatorService
     */
    protected $validatorService;

    /**
     * Constructs field type object, initializing internal data structures.
     *
     * @param \eZ\Publish\Core\Repository\ValidatorService $validatorService
     */
    public function __construct( ValidatorService $validatorService )
    {
        $this->validatorService = $validatorService;
    }

    /**
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $value as value.
     *
     * @param int $value
     * @return \eZ\Publish\Core\FieldType\Integer\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( $value )
    {
        return new Value( $value );
    }

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezinteger';
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Integer\Value
     */
    public function getDefaultDefaultValue()
    {
        return new Value( 0 );
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\FieldType\Integer\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Integer\Value
     */
    public function acceptValue( $inputValue )
    {
        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\Integer\\Value',
                $inputValue
            );
        }

        if ( !is_integer( $inputValue->value ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->value',
                'integer',
                $inputValue->value
            );
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @return array
     */
    protected function getSortInfo( $value )
    {
        return array( 'sort_key_int' => $value->value );
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Integer\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\Integer\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return $value->value;
    }

    /**
     * Returns whether the field type is searchable
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }

    /**
     * Validates the validatorConfiguration of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct
     *
     * @param mixed $validatorConfiguration
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateValidatorConfiguration( $validatorConfiguration )
    {
        $validationErrors = array();

        foreach ( $validatorConfiguration as $validatorIdentifier => $parameters )
        {
            if ( isset( $this->validatorConfigurationSchema[$validatorIdentifier] ) )
            {
                foreach ( $parameters as $name => $value )
                {
                    switch ( $name )
                    {
                        case "minStringLength";
                        case "maxStringLength";
                            if ( !is_integer( $value ) )
                            {
                                $validationErrors[] = new ValidationError(
                                    "Validator parameter '%parameter%' must be of integer type",
                                    null,
                                    array(
                                        "parameter" => $name
                                    )
                                );
                            }
                            break;
                        default:
                            $validationErrors[] = new ValidationError(
                                "Validator parameter '%parameter%' is unknown",
                                null,
                                array(
                                    "parameter" => $name
                                )
                            );
                    }
                }
            }
            else
            {
                $validationErrors[] = new ValidationError(
                    "Validator '%validator%' is unknown",
                    null,
                    array(
                        "validator" => $validatorIdentifier
                    )
                );
            }
        }

        return $validationErrors;
    }
}
