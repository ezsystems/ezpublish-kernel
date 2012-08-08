<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlBlock class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText;
use eZ\Publish\API\Repository\Values\Content\Field,
    eZ\Publish\API\Repository\FieldTypeTools,
    eZ\Publish\Core\FieldType\FieldType,
    eZ\Publish\Core\FieldType\XmlText\Input\Handler as XMLTextInputHandler,
    eZ\Publish\Core\FieldType\XmlText\Input\Parser as XMLTextInputParserInterface,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\SPI\FieldType\Event,
    eZ\Publish\Core\FieldType\ValidationError;

/**
 * XmlBlock field type.
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
     * @var \eZ\Publish\Core\FieldType\XmlText\Input\Handler
     */
    protected $inputHandler;

    /**
     * Constructs field type object, initializing internal data structures.
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Input\Parser $inputParser
     */
    public function __construct( XMLTextInputParserInterface $inputParser )
    {
        $this->inputHandler = new XMLTextInputHandler( $inputParser );
    }

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
        throw new \RuntimeException( 'Implement this method' );
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\XmlText\Value
     */
    public function getDefaultDefaultValue()
    {
        $value = <<<EOF
<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" />
EOF;
        return new Value( $this->inputHandler, $value, Value::INPUT_FORMAT_RAW );
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\XmlText\Value
     */
    public function acceptValue( $inputValue )
    {
        if ( is_string( $inputValue ) )
        {
            $inputValue = new Value( $this->inputHandler, $inputValue );
        }

        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\XmlText\\Value',
                $inputValue
            );
        }

        if ( !is_string( $inputValue->text ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->text',
                'string',
                $inputValue->text
            );
        }

        $handler = $inputValue->getInputHandler();
        if ( !$handler->isXmlValid( $inputValue->text, false ) )
        {
            // @todo Pass on the parser error messages (if any: $handler->getParsingMessages())
            throw new InvalidArgumentValue( '$inputValue->text', $inputValue->text, __CLASS__ );
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
     * Converts complex values to a Value\Raw object
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Value $value
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     *
     * @return \eZ\Publish\Core\FieldType\XmlText\Value
     */
    protected function convertValueToRawValue( Value $value, Field $field )
    {
        // we don't convert Raw to Raw, right ?
        // if ( get_class( $value ) === 'eZ\\Publish\\Core\\FieldType\\XmlText\\Value' )
        //    return $value;

        $handler = $value->getInputHandler( $value );
        throw new \RuntimeException( '@todo XMLText has a dependency on version id and version number, after refactoring that is not available' );
        $handler->process( $value->text, $this->fieldTypeTools, $field->version );

        $value->setRawText( $handler->getDocumentAsXml() );
    }

    /**
     * This method is called on occurring events. Implementations can perform corresponding actions
     *
     * @param string $event prePublish, postPublish, preCreate, postCreate
     * @param \eZ\Publish\SPI\FieldType\Event $event
     */
    public function handleEvent( Event $event )
    {
        if ( $event instanceof PreCreateEvent )
        {
            $this->convertValueToRawValue( $event->field->value, $field );
        }
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\XmlText\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $this->inputHandler, $hash );
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
