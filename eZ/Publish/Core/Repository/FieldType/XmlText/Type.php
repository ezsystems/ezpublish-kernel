<?php
/**
 * File containing the eZ\Publish\Core\Repository\FieldType\XmlBlock class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\XmlText;
use ezp\Base\Repository,
    ezp\Content\Field,
    ezp\Content\Version,
    eZ\Publish\Core\Repository\FieldType,
    eZ\Publish\Core\Repository\FieldType\Value as BaseValue,
    eZ\Publish\Core\Repository\FieldType\XmlText\Value as Value,
    ezp\Content\Type\FieldDefinition,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Exception\InvalidArgumentType;

/**
 * XmlBlock field type.
 *
 * This field
 * @package
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
    protected $allowedSettings = array(
        'numRows' => 10,
        'tagPreset' => null,
        'defaultText' => '',
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
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\Repository\FieldType\TextLine\Value
     */
    public function getDefaultDefaultValue()
    {
        $value = <<< EOF
<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" />
EOF;
        return new Value( $value, Value::INPUT_FORMAT_RAW );
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \ezp\Base\Exception\InvalidArgumentType if the parameter is not of the supported value sub type
     * @throws \ezp\Base\Exception\InvalidArgumentValue if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\Repository\FieldType\Value $inputValue
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Value
     */
    public function acceptValue( BaseValue $inputValue )
    {
        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType( 'value', 'eZ\\Publish\\Core\\Repository\\FieldType\\XmlText\\Value' );
        }

        if ( !is_string( $inputValue->text ) )
        {
            throw new InvalidArgumentValue( $inputValue, get_class( $this ) );
        }

        $handler = $inputValue->getInputHandler();
        if ( !$handler->isXmlValid( $inputValue->text, false ) )
        {
            // @todo Pass on the parser error messages (if any: $handler->getParsingMessages())
            throw new InvalidArgumentValue( $inputValue, get_class( $this ) );
        }

        return $inputValue;
    }

    /**
     * Returns sortKey information
     *
     * @see eZ\Publish\Core\Repository\FieldType
     *
     * @return array|bool
     */
    protected function getSortInfo( BaseValue $value )
    {
        return false;
    }

    /**
     * Converts complex values to a Value\Raw object
     * @param \eZ\Publish\Core\Repository\FieldType\XmlText\Value $value
     * @param \ezp\Base\Repository $repository
     * @param \ezp\Content\Field $field
     * @return \eZ\Publish\Core\Repository\FieldType\XmlText\Value
     */
    protected function convertValueToRawValue( Value $value, Repository $repository, Field $field )
    {
        // we don't convert Raw to Raw, right ?
        // if ( get_class( $value ) === 'eZ\\Publish\\Core\\Repository\\FieldType\\XmlText\\Value' )
        //    return $value;

        $handler = $value->getInputHandler( $value );
        $handler->process( $value->text, $repository, $field->version );

        $value->setRawText( $handler->getDocumentAsXml() );
    }

    /**
     * This method is called on occuring events. Implementations can perform corresponding actions
     *
     * @param string $event - prePublish, postPublish, preCreate, postCreate
     * @param Repository $repository
     * @param $fieldDef - the field definition of the field
     * @param $field - the field for which an action is performed
     */
    public function handleEvent( $event, Repository $repository, FieldDefinition $fieldDef, Field $field )
    {
        if ( $event === "preCreate" )
        {
            $this->convertValueToRawValue( $field->value, $repository, $field );
        }
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\Repository\FieldType\Value $value
     *
     * @return mixed
     */
    public function toHash( BaseValue $value )
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
