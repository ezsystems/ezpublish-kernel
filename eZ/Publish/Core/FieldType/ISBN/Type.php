<?php
/**
 * File containing the ISBN class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU General Public License v2.0
 * @version 
 */

namespace eZ\Publish\Core\FieldType\ISBN;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\ISBN\Exception\InvalidValue;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * The ISBN field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    const PREFIX_LENGTH = 3;
    const CHECK_LENGTH = 1;
    const LENGTH = 13;
    const PREFIX_978 = 978;
    const PREFIX_979 = 979;
    
    protected $settingsSchema = array(
        "isISBN13" => array(
            "type" => "boolean",
            "default" => true
        )
    );

    /**
     * Validates a field based on the validators in the field definition
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\ISBN\Value $fieldValue The field value for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate( FieldDefinition $fieldDefinition, SPIValue $fieldValue )
    {
        $validationErrors = array();

        if ( $this->isEmptyValue( $fieldValue ) )
        {
            return $validationErrors;
        }

        $fieldSettings = $fieldDefinition->fieldSettings;

        if ( ( !isset( $fieldSettings["isISBN13"] ) || $fieldSettings["isISBN13"] === false )
            && strlen( $fieldValue ) > 10 )
        {
            $validationErrors[] = new ValidationError(
                "Field definition limits ISBN to ISBN10.",
                null,
                array()
            );
        }

        return $validationErrors;
    }

    /**
     * Returns the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezisbn";
    }

    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param \eZ\Publish\Core\FieldType\ISBN\Value $value
     *
     * @return string
     */
    public function getName( SPIValue $value )
    {
        return (string)$value->isbn;
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\ISBN\Value
     */
    public function getEmptyValue()
    {
        return new Value;
    }

    /**
     * Returns if the given $value is considered empty by the field type
     *
     * @param mixed $value
     *
     * @return boolean
     */
    public function isEmptyValue( SPIValue $value )
    {
        return $value->isbn === null || trim( $value->isbn ) === "";
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param string|\eZ\Publish\Core\FieldType\ISBN\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\ISBN\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput( $inputValue )
    {
        if ( is_string( $inputValue ) )
        {
            $inputValue = $this->fromHash( $inputValue );
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\ISBN\Value $value
     *
     * @return void
     */
    protected function checkValueStructure( BaseValue $value )
    {
        if ( !is_string( $value->isbn ) )
        {
            throw new InvalidArgumentType(
                '$value->isbn',
                'string',
                $value->isbn
            );
        }
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \eZ\Publish\Core\FieldType\ISBN\Value $value
     *
     * @return array
     */
    protected function getSortInfo( BaseValue $value )
    {
        return $this->transformationProcessor->transformByGroup( (string)$value, "lowercase" );
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\ISBN\Value $value
     */
    public function fromHash( $hash )
    {
        if ( $hash === null || $hash === "" )
        {
            return $this->getEmptyValue();
        }
        
        $isbn = $hash;
        
        $isbnTestNumber = preg_replace( "/[\s|\-]/", "", trim( $isbn ) );
        if ( strlen( $isbnTestNumber ) == 10 )
        {
            $status = $this->validateISBNChecksum( $isbnTestNumber );
            if ( $status === false )
            {
                throw new InvalidValue( $hash );
            }
            
        }
        else
        {
            $status = $this->validateISBN13Checksum( $isbnTestNumber, $error );
            if ( $status === false )
            {
                throw new InvalidValue( $hash, $error );
            }
        }
        return new Value( $hash );
    }

    /**
     * Validates the ISBN number.
     * All characters should be numeric except the last digit that may be the character X,
     * which should be calculated as 10.
     * 
     * @param string $isbnNr A string containing the number without any dashes.
     * 
     * @return boolean
     */
    private function validateISBNChecksum ( $isbnNr )
    {
        $result = 0;
        $isbnNr = strtoupper( $isbnNr );
        for ( $i = 10; $i > 0; $i-- )
        {
            if ( is_numeric( $isbnNr{$i-1} ) or ( $i == 10  and $isbnNr{$i-1} == 'X' ) )
            {
                if ( ( $i == 1 ) and ( $isbnNr{9} == 'X' ) )
                {
                    $result += 10 * $i;
                }
                else
                {
                    $result += $isbnNr{10-$i} * $i;
                }
            }
            else
            {
                return false;
            }
        }
        return ( $result % 11 == 0 );
    }
    
    /**
     *  Validates the ISBN-13 number.
     * 
     * @param string $isbnNr A string containing the number without any dashes.
     * @param string $error is used to send back an error message that will be shown to the user if the
     *                      ISBN number validated.
     * 
     * @return boolean
     */
    private function validateISBN13Checksum ( $isbnNr, &$error )
    {
        if ( !$isbnNr )
            return false;
        $isbnNr = preg_replace( "/[\s|\-]+/", "", $isbnNr );
        if ( substr( $isbnNr, 0, self::PREFIX_LENGTH ) != self::PREFIX_978 and
             substr( $isbnNr, 0, self::PREFIX_LENGTH ) != self::PREFIX_979 )
        {
            $error = '13 digit ISBN must start with 978 or 979';
            return false;
        }

        $checksum13 = 0;
        $weight13 = 1;
        if ( strlen( $isbnNr ) != self::LENGTH )
        {
            $error = 'ISBN length is invalid';
            return false;
        }

        //compute checksum
        $val = 0;
        for ( $i = 0; $i < self::LENGTH; $i++ )
        {
            $val = $isbnNr{$i};
            if ( !is_numeric( $isbnNr{$i} ) )
            {
                $error = 'All ISBN 13 characters need to be numeric';
                return false;
            }
            $checksum13 = $checksum13 + $weight13 * $val;
            $weight13 = ( $weight13 + 2 ) % 4;
        }
        if ( ( $checksum13 % 10 ) != 0 )
        {
            // Calculate the last digit from the 12 first numbers.
            $checkDigit = ( 10 - ( ( $checksum13 - ( ( $weight13 + 2 ) % 4 ) * $val ) % 10 ) ) % 10;
            //bad checksum
            $error = 'Bad checksum, last digit should be ' . $checkDigit;
            return false;
        }

        return true;
    }
    
    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\ISBN\Value $value
     *
     * @return mixed
     */
    public function toHash( SPIValue $value )
    {
        if ( $this->isEmptyValue( $value ) )
        {
            return null;
        }
        return $value->isbn;
    }

    /**
     * Returns whether the field type is searchable
     *
     * @return boolean
     */
    public function isSearchable()
    {
        return true;
    }
}
