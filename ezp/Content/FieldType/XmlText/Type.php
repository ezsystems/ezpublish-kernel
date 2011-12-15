<?php
/**
 * File containing the ezp\Content\FieldType\XmlBlock class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\XmlText;
use ezp\Base\Repository,
    ezp\Content\Field,
    ezp\Content\Version,
    ezp\Content\FieldType,
    ezp\Content\FieldType\OnContentPublish,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Content\FieldType\XmlText\Value as Value,
    ezp\Content\Type\FieldDefinition,
    ezp\Content\FieldType\XmlText\Input\Handler as InputHandler,
    ezp\Content\FieldType\XmlText\Input\Parser\Simplified as SimplifiedInputParser,
    ezp\Content\FieldType\XmlText\Input\Parser\OnlineEditor as OnlineEditorParser,
    ezp\Content\FieldType\XmlText\Input\Parser\Raw as RawInputParser,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Base\Exception\InvalidArgumentType;

/**
 * XmlBlock field type.
 *
 * This field
 * @package
 */
class Type extends FieldType implements OnContentPublish
{
    const FIELD_TYPE_IDENTIFIER = "ezxmltext";
    const IS_SEARCHABLE = true;

    /**
     * List of settings available for this FieldType
     *
     * The key is the setting name, and the value is the default value for this setting
     *
     * @var array
     */
    protected $allowedSettings = array(
        'numRows' => 10,
        'tagPreset' => null,
    );

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \ezp\Content\FieldType\TextLine\Value
     */
    protected function getDefaultValue()
    {
        $value = <<< EOF
<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" />
EOF;
        return new Value( $value );
    }

    /**
     * Checks if $inputValue can be parsed.
     * If the $inputValue actually can be parsed, the value is returned.
     * Otherwise, an \ezp\Base\Exception\BadFieldTypeInput exception is thrown
     *
     * @throws \ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @param \ezp\Content\FieldType\XmlText\Value $inputValue
     * @return \ezp\Content\FieldType\TextLine\Value
     */
    protected function canParseValue( BaseValue $inputValue )
    {
        if ( $inputValue instanceof Value )
        {

            if ( !is_string( $inputValue->text ) )
            {
                throw new BadFieldTypeInput( $inputValue, get_class( $this ) );
            }

            $handler = new InputHandler( $this->getInputParser( $inputValue ) );
            if ( !$handler->isXmlValid( $inputValue->text, false ) )
            {
                // @todo Pass on the parser error messages (if any: $handler->getParsingMessages())
                throw new BadFieldTypeInput( $inputValue, get_class() );
            }
            else
            {
                return $inputValue;
            }
        }

        throw new InvalidArgumentType( 'value', 'ezp\\Content\\FieldType\\XmlText\\Value' );
    }

    /**
     * Returns sortKey information
     *
     * @see ezp\Content\FieldType
     *
     * @return array|bool
     */
    protected function getSortInfo()
    {
        return false;
    }

    /**
     * Event handler for content/publish
     * @param \ezp\Base\Repository $repository
     * @param \ezp\Content\Version $version
     * @param \ezp\Content\Field $field
     */
    public function onContentPublish( Repository $repository, Field $field )
    {
        $this->value = $this->convertValueToRawValue( $field->value, $repository, $field );
    }

    /**
     * Converts complex values to a Value\Raw object
     * @param \ezp\Content\FieldType\XmlText\Value $value
     * @param \ezp\Base\Repository $repository
     * @param \ezp\Content\Field $field
     * @return \ezp\Content\FieldType\XmlText\Value
     */
    protected function convertValueToRawValue( Value $value, Repository $repository, Field $field )
    {
        // we don't convert Raw to Raw, right ?
        if ( get_class( $value ) === 'ezp\\Content\\FieldType\\XmlText\\Value' )
            return $value;

        $handler = $this->getInputHandler( $value );
        $handler->process( $value->text, $repository, $field->version );

        return new RawValue( $handler->getDocumentAsXml() );
    }

    /**
     * Returns the InputHandler object
     * @return \ezp\Content\FieldType\XmlText\Input\Handler
     */
    protected function getInputHandler( Value $value )
    {
        return new InputHandler( $this->getInputParser( $value ) );
    }

    /**
     * Returns the XML Input Parser for an XmlText Value
     * @param \ezp\Content\FieldType\XmlText\Value $value
     * @return \ezp\Content\FieldType\XmlText\Input\Parser
     */
    protected function getInputParser( BaseValue $value )
    {
        // @todo Load from configuration
        $valueClass = get_class( $value );
        if ( !isset( $this->parserClasses[$valueClass] ) )
        {
            // @todo Use dedicated exception
            throw new Exception( "No parser found for " . get_class( $value ) );
        }

        return new $this->parserClasses[$valueClass];
    }
}
