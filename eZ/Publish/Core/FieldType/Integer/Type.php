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
    eZ\Publish\Core\FieldType\ValidationError,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * Integer field types
 *
 * Represents integers.
 */
class Type extends FieldType
{
    protected $validatorConfigurationSchema = array(
        'IntegerValueValidator' => array(
            'minIntegerValue' => array(
                'type' => 'int',
                'default' => 0
            ),
            'maxIntegerValue' => array(
                'type' => 'int',
                'default' => false
            )
        )
    );

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
     * The validator identifier is mapped to a Validator class which can be retrieved via the
     * ValidatorService.
     */
    public function getValidatorConfigurationSchema()
    {
        return $this->validatorConfigurationSchema;
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

        foreach ( (array)$validatorConfiguration as $validatorIdentifier => $constraints )
        {
            if ( $validatorIdentifier !== 'IntegerValueValidator' )
            {
                $validationErrors[] = new ValidationError(
                    "Validator '%validator%' is unknown",
                    null,
                    array(
                        "validator" => $validatorIdentifier
                    )
                );

                continue;
            }
            foreach ( $constraints as $name => $value )
            {
                switch ( $name )
                {
                    case "minIntegerValue":
                    case "maxIntegerValue":
                        if ( $value !== false && !is_integer( $value ) )
                        {
                            $validationErrors[] = new ValidationError(
                                "Validator parameter '%parameter%' value must be of integer type",
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

        return $validationErrors;
    }

    /**
     * Validates a field based on the validators in the field definition
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\Value $fieldValue The field for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate( FieldDefinition $fieldDefinition, $fieldValue )
    {
        $validatorConfiguration = $fieldDefinition->getValidatorConfiguration();
        $constraints = $validatorConfiguration['IntegerValueValidator'];

        $validationErrors = array();

        if ( $constraints['maxIntegerValue'] !== false && $fieldValue->value > $constraints['maxIntegerValue'] )
        {
            $validationErrors[] = new ValidationError(
                "The value can not be higher than %size%.",
                null,
                array(
                    "size" => $constraints['maxIntegerValue']
                )
            );
        }

        if ( $constraints['minIntegerValue'] !== false && $fieldValue->value < $constraints['minIntegerValue'] )
        {
            $validationErrors[] = new ValidationError(
                "The value can not be lower than %size%.",
                null,
                array(
                    "size" => $constraints['minIntegerValue']
                )
            );
        }

        return $validationErrors;
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
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getName( $value )
    {
        $value = $this->acceptValue( $value );

        return (string) $value->value;
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Integer\Value
     */
    public function getEmptyValue()
    {
        return null;
    }

    /**
     * Potentially builds and checks the type and structure of the $inputValue.
     *
     * This method first inspects $inputValue, if it needs to convert it, e.g.
     * into a dedicated value object. An example would be, that the field type
     * uses values of MyCustomFieldTypeValue, but can also accept strings as
     * the input. In that case, $inputValue first needs to be converted into a
     * MyCustomFieldTypeClass instance.
     *
     * After that, the (possibly converted) value is checked for structural
     * validity. Note that this does not include validation after the rules
     * from validators, but only plausibility checks for the general data
     * format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param mixed $inputValue
     *
     * @return mixed The potentially converted and structurally plausible value.
     */
    public function acceptValue( $inputValue )
    {
        if ( $inputValue === null )
        {
            return null;
        }

        if ( is_int( $inputValue ) )
        {
            $inputValue = new Value( $inputValue );
        }

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
        if ( $value === null )
        {
            return null;
        }
        return $value->value;
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
        if ( $hash === null )
        {
            return null;
        }
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
        if ( $value === null )
        {
            return null;
        }
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
}
