<?php
/**
 * File containing the Country class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Country;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\FieldType\Country\Exception\InvalidValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\ValidationError;

/**
 * The Country field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    protected $settingsSchema = array(
        "isMultiple" => array(
            "type" => "boolean",
            "default" => false
        )
    );

    /**
     * @var array
     */
    protected $countriesInfo;

    /**
     * @param array $countriesInfo Array of countries data
     */
    public function __construct( array $countriesInfo )
    {
        $this->countriesInfo = $countriesInfo;
    }

    /**
     * Returns the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezcountry";
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
        return implode(
            ", ",
            array_map(
                function ( $countryInfo )
                {
                    return $countryInfo["Name"];
                },
                $this->countriesInfo
            )
        );
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Country\Value
     */
    public function getEmptyValue()
    {
        return new Value;
    }

    /**
     * Implements the core of {@see acceptValue()}.
     *
     * @param mixed $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Country\Value The potentially converted and structurally plausible value.
     */
    protected function internalAcceptValue( $inputValue )
    {
        if ( is_array( $inputValue ) )
        {
            if ( empty( $inputValue ) )
                return $this->getEmptyValue();

            $inputValue = $this->fromHash( $inputValue );
        }

        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\Country\\Value',
                $inputValue
            );
        }

        if ( !is_array( $inputValue->countries ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->countries',
                'array',
                $inputValue->countries
            );
        }

        return $inputValue;
    }

    /**
     * Validates field value against 'isMultiple' setting.
     *
     * Does not use validators.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\Value $fieldValue The field value for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate( FieldDefinition $fieldDefinition, $fieldValue )
    {
        $validationErrors = array();
        $fieldSettings = $fieldDefinition->fieldSettings;

        if ( ( !isset( $fieldSettings["isMultiple"] ) || $fieldSettings["isMultiple"] === false )
            && count( $fieldValue->countries ) > 1 )
        {
            $validationErrors[] = new ValidationError(
                "Field definition does not allow multiple countries to be selected.",
                null,
                array()
            );
        }

        foreach ( $fieldValue->countries as $alpha2 => $countryInfo )
        {
            if ( !isset( $this->countriesInfo[$alpha2] ) )
            {
                $validationErrors[] = new ValidationError(
                    "Country with Alpha2 code '%alpha2%' is not defined in FieldType settings.",
                    null,
                    array(
                        "alpha2" => $alpha2
                    )
                );
            }
        }

        return $validationErrors;
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
            return "";
        }

        $countries = array();
        foreach ( $value->countries as $countryInfo )
        {
            $countries[] = strtolower( $countryInfo["Name"] );
        }

        sort( $countries );

        return implode( ",", $countries );
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Country\Value $value
     */
    public function fromHash( $hash )
    {
        if ( $hash === null )
        {
            return null;
        }

        $countries = array();
        foreach ( $hash as $country )
        {
            foreach ( $this->countriesInfo as $countryInfo )
            {
                switch ( $country )
                {
                    case $countryInfo["Name"]:
                    case $countryInfo["Alpha2"]:
                    case $countryInfo["Alpha3"]:
                    $countries[$countryInfo["Alpha2"]] = $countryInfo;
                        continue 3;
                }
            }

            throw new InvalidValue( $country );
        }

        return new Value( $countries );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\Country\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        if ( $this->isEmptyValue( $value ) )
        {
            return null;
        }

        return array_keys( $value->countries );
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

    /**
     * Get index data for field data for search backend
     *
     * @param mixed $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Field[]
     */
    public function getIndexData( $value )
    {
        throw new \RuntimeException( '@todo: Implement' );
    }

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings( $fieldSettings )
    {
        $validationErrors = array();

        foreach ( (array)$fieldSettings as $name => $value )
        {
            if ( isset( $this->settingsSchema[$name] ) )
            {
                switch ( $name )
                {
                    case "isMultiple":
                        if ( !is_bool( $value ) )
                        {
                            $validationErrors[] = new ValidationError(
                                "Setting '%setting%' value must be of boolean type",
                                null,
                                array(
                                    "setting" => $name
                                )
                            );
                        }
                        break;
                }
            }
            else
            {
                $validationErrors[] = new ValidationError(
                    "Setting '%setting%' is unknown",
                    null,
                    array(
                        "setting" => $name
                    )
                );
            }
        }

        return $validationErrors;
    }
}
