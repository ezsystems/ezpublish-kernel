<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Type class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText;

use eZ\Publish\Core\FieldType\FieldType,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    eZ\Publish\Core\FieldType\ValidationError,
    eZ\Publish\Core\FieldType\XmlText\Input,
    eZ\Publish\Core\FieldType\XmlText\Input\EzXml,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    DOMDocument;

/**
 * XmlText field type.
 */
class Type extends FieldType
{
    /**
     * Default preset of tags available in online editor
     */
    const TAG_PRESET_DEFAULT = 0;

    /**
     * Preset of tags for online editor intended for simple formatting options
     */
    const TAG_PRESET_SIMPLE_FORMATTING = 1;

    /**
     * List of settings available for this FieldType
     *
     * The key is the setting name, and the value is the default value for this setting
     *
     * @var array
     */
    protected $settingsSchema = array(
        "numRows" => array(
            "type" => "int",
            "default" => 10
        ),
        "tagPreset" => array(
            "type" => "choice",
            "default" => self::TAG_PRESET_DEFAULT
        )
    );

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezxmltext";
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

        $result = null;
        if ( $section = $value->xml->documentElement->firstChild )
        {
            $textDom = $section->firstChild;

            if ( $textDom && $textDom->hasChildNodes() )
            {
                $result = $textDom->firstChild->textContent;
            }
            elseif ( $textDom )
            {
                $result = $textDom->textContent;
            }
        }

        if ( $result === null )
            $result = $value->xml->documentElement->textContent;

        return trim( $result );
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\XmlText\Value
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
        if ( $value === null || $value->xml === null )
            return true;

        return !$value->xml->documentElement->hasChildNodes();
    }

    /**
     * Implements the core of {@see acceptValue()}.
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Value|\eZ\Publish\Core\FieldType\XmlText\Input|string $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\XmlText\Value The potentially converted and structurally plausible value.
     */
    protected function internalAcceptValue( $inputValue )
    {
        if ( is_string( $inputValue ) )
        {
            if ( empty( $inputValue ) )
                $inputValue = Value::EMPTY_VALUE;
            $inputValue = new EzXml( $inputValue );
        }

        if ( $inputValue instanceof Input )
        {
            $doc = new DOMDocument;
            $doc->loadXML( $inputValue->getInternalRepresentation() );
            $inputValue = new Value( $doc );
        }
        else if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\XmlText\\Value',
                $inputValue
            );
        }

        return $inputValue;
    }

    /**
     * Returns sortKey information
     *
     * @see \eZ\Publish\Core\FieldType
     *
     * @param mixed $value
     *
     * @return array|bool
     */
    protected function getSortInfo( $value )
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     * $hash accepts the following keys:
     *  - xml (XML string which complies internal format)
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\XmlText\Value $value
     */
    public function fromHash( $hash )
    {
        $doc = new DOMDocument;
        $doc->loadXML( $hash['xml'] );
        return new Value( $doc );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return array( 'xml' => (string)$value );
    }

    /**
     * Creates a new Value object from persistence data.
     * $fieldValue->data is supposed to be a DOMDocument object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     * @return Value
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        return new Value( $fieldValue->data );
    }

    /**
     * @param Value $value
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue( $value )
    {
        return new FieldValue(
            array(
                 'data'         => $value->xml,
                 'externalData' => null,
                 'sortKey'      => $this->getSortInfo( $value )
            )
        );
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
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings( $fieldSettings )
    {
        $validationErrors = array();

        foreach ( $fieldSettings as $name => $value )
        {
            if ( isset( $this->settingsSchema[$name] ) )
            {
                switch ( $name )
                {
                    case "numRows":
                        if ( !is_integer( $value ) )
                        {
                            $validationErrors[] = new ValidationError(
                                "Setting '%setting%' value must be of integer type",
                                null,
                                array(
                                    "setting" => $name
                                )
                            );
                        }
                        break;
                    case "tagPreset":
                        $definedTagPresets = array(
                            self::TAG_PRESET_DEFAULT,
                            self::TAG_PRESET_SIMPLE_FORMATTING
                        );
                        if ( !in_array( $value, $definedTagPresets ) )
                        {
                            $validationErrors[] = new ValidationError(
                                "Setting '%setting%' is of unknown tag preset",
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
