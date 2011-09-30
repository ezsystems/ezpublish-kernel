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
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Content\FieldType\XmlText\Value,
    ezp\Content\Type\FieldDefinition,
    ezp\Content\FieldType\XmlText\Input\Parser\Simplified as SimplifiedInputParser,
    ezp\Content\FieldType\XmlText\Input\Handler\Simplified as SimplifiedInputHandler,
    \ezp\Base\Exception\BadFieldTypeInput;

/**
 * XmlBlock field type.
 *
 * This field
 * @package
 */
class Type extends FieldType
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
     * @param \ezp\Content\FieldType\TextLine\Value $inputValue
     * @return \ezp\Content\FieldType\TextLine\Value
     */
    protected function canParseValue( BaseValue $inputValue )
    {
        if ( !$inputValue instanceof Value || !is_string( $inputValue->text ) )
        {
            throw new BadFieldTypeInput( $inputValue, get_class() );
        }

        $xmlTextHandler = $this->getInputHandler( $inputValue->text );
        if ( !$xmlTextHandler->isXmlValid() )
        {
            throw new BadFieldTypeInput( $inputValue, get_class() );
        }
        else
        {
            return $inputValue;
        }
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

    protected function onContentPublish( Repository $repository, Version $version, Field $field )
    {
        // needs to pass more data to the handler
        // - repository (to publish new items)
        // - validate or process modes... that's harder.
        $handler = $this->getInputHandler( $field->getValue()->text );
        $handler->process();

        // From here, we can get the list of elements that need further processing:
        // - links: replace the URL with the ID of the eZURL object; create the object if it doesn't exist yet
        // - embeds: replace the embed contents with the internal version; create the relation accordingly
    }

    /**
     * Returns the XML Input Handler for $xmlString
     * @param string $xmlString
     * @return \ezp\Content\FieldType\XmlText\Input\Handler
     */
    protected function getInputHandler( $xmlString )
    {
        return new SimplifiedInputHandler( $xmlString );
    }
}
