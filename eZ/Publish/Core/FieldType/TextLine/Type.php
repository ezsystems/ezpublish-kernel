<?php
/**
 * File containing the TextLine class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\TextLine;
use eZ\Publish\Core\FieldType\FieldType,
    eZ\Publish\Core\FieldType\ValidationError,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    protected $validatorConfigurationSchema = array(
        'StringLengthValidator' => array(
            'minStringLength' => array(
                'type' => 'int',
                'default' => 0
            ),
            'maxStringLength' => array(
                'type' => 'int',
                'default' => null
            )
        )
    );

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
            if ( $validatorIdentifier !== 'StringLengthValidator' )
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
                    case "minStringLength":
                    case "maxStringLength":
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
        $constraints = isset( $validatorConfiguration['StringLengthValidator'] )
            ? $validatorConfiguration['StringLengthValidator']
            : array();

        $validationErrors = array();

        if ( isset( $constraints['maxStringLength'] ) &&
            $constraints['maxStringLength'] !== false &&
            $constraints['maxStringLength'] !== 0 &&
            strlen( $fieldValue->text ) > $constraints['maxStringLength'] )
        {
            $validationErrors[] = new ValidationError(
                "The string can not exceed %size% character.",
                "The string can not exceed %size% characters.",
                array(
                    "size" => $constraints['maxStringLength']
                )
            );
        }

        if ( isset( $constraints['minStringLength'] ) &&
            $constraints['minStringLength'] !== false &&
            $constraints['minStringLength'] !== 0 &&
            strlen( $fieldValue->text ) < $constraints['minStringLength'] )
        {
            $validationErrors[] = new ValidationError(
                "The string can not be shorter than %size% character.",
                "The string can not be shorter than %size% characters.",
                array(
                    "size" => $constraints['minStringLength']
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
        return "ezstring";
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
        if ( $value === null )
        {
            return "";
        }
        return (string)$value->text;
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\TextLine\Value
     */
    public function getEmptyValue()
    {
        return new Value;
    }

    /**
     * Returns if the given $value is considered empty by the field type
     *
     * @param mixed $value
     * @return bool
     */
    public function isEmptyValue( $value )
    {
        return $value === null || $value->text === null || trim( $value->text ) === "";
    }

    /**
     * Implements the core of {@see acceptValue()}.
     *
     * @param mixed $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\TextLine\Value The potentially converted and structurally plausible value.
     */
    protected function internalAcceptValue( $inputValue )
    {
        if ( is_string( $inputValue ) )
        {
            if ( trim( $inputValue, " " ) === "" )
                return $this->getEmptyValue();

            $inputValue = new Value( $inputValue );
        }
        else if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\TextLine\\Value',
                $inputValue
            );
        }

        if ( $inputValue->text === null
            || ( is_string( $inputValue->text ) && trim( $inputValue->text, " " ) === "" ) )
        {
            return $this->getEmptyValue();
        }

        if ( !is_string( $inputValue->text ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->text',
                'string',
                $inputValue->text
            );
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @todo String normalization should occur here.
     * @return array
     */
    protected function getSortInfo( $value )
    {
        if ( $value === null )
        {
            return '';
        }
        return $value->text;
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\TextLine\Value $value
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
     * @param \eZ\Publish\Core\FieldType\TextLine\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        if ( $this->isEmptyValue( $value ) )
        {
            return null;
        }
        return $value->text;
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
