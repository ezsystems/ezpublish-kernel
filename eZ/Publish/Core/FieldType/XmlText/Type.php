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
    eZ\Publish\SPI\FieldType\Event;

/**
 * XmlBlock field type.
 */
class Type extends FieldType
{
    /**
     * List of settings available for this FieldType
     *
     * The key is the setting name, and the value is the default value for this setting
     *
     * @var array
     */
    protected $settingsSchema = array(
        'numRows' => 10,
        'tagPreset' => null,
        'defaultText' => '',
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
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $text as value.
     *
     * @param string $text
     * @return \eZ\Publish\Core\FieldType\XmlText\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( $text )
    {
        return new Value( $this->inputHandler, $text );
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
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\XmlText\Value
     */
    public function getDefaultDefaultValue()
    {
        $value = <<< EOF
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
        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\Repository\\FieldType\\XmlText\\Value',
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
        // if ( get_class( $value ) === 'eZ\\Publish\\Core\\Repository\\FieldType\\XmlText\\Value' )
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
}
