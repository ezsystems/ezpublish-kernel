<?php
/**
 * File containing the Country class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Country;
use eZ\Publish\Core\FieldType\FieldType,
    eZ\Publish\Core\FieldType\Country\Exception\InvalidValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    eZ\Publish\Core\Repository\ValidatorService,
    eZ\Publish\API\Repository\FieldTypeTools,
    eZ\Publish\Core\FieldType\ValidationError;

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
     * @param \eZ\Publish\Core\Repository\ValidatorService $validatorService
     * @param \eZ\Publish\API\Repository\FieldTypeTools $fieldTypeTools
     * @param array $countriesInfo Array of countries data
     */
    public function __construct( ValidatorService $validatorService, FieldTypeTools $fieldTypeTools, array $countriesInfo )
    {
        parent::__construct( $validatorService, $fieldTypeTools );
        $this->countriesInfo = $countriesInfo;
    }

    /**
     * @param array $countries
     * @return Value
     * @throws Exception\InvalidValue
     */
    public function buildValue( $countries )
    {
        $countryValue = new Value( (array)$countries );
        foreach ( $countryValue->values as $country )
        {
            foreach ( $this->countriesInfo as $countryInfo )
            {
                switch ( $country )
                {
                    case $countryInfo["Name"]:
                    case $countryInfo["Alpha2"]:
                    case $countryInfo["Alpha3"]:
                        $countryValue->data[$countryInfo["Alpha2"]] = $countryInfo;
                        continue 3;
                }
            }

            throw new InvalidValue( $country );
        }

        return $countryValue;
    }

    /**
     * Return the field type identifier for this field type
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
        $value = $this->acceptValue( $value );

        return implode( ",", $value->values );
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @todo Is a default value really possible with this type?
     *       Shouldn't an exception be used?
     * @return \eZ\Publish\Core\FieldType\Country\Value
     */
    public function getDefaultDefaultValue()
    {
        return new Value();
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\FieldType\Country\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Country\Value
     */
    public function acceptValue( $inputValue )
    {
        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\Country\\Value',
                $inputValue
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
        $countries = array();
        foreach ( $value->data as $countryInfo )
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
        return new Value( $hash );
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
        return $value->values;
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
     * Get index data for field data for search backend
     *
     * @param mixed $value
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Field[]
     */
    public function getIndexData( $value )
    {
        throw new \RuntimeException( '@TODO: Implement' );
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
