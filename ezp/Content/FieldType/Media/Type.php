<?php
/**
 * File containing the Media Type class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Media;
use ezp\Content\FieldType,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Content\FieldType\Media\Value as MediaValue,
    ezp\Content\Field,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Io\BinaryFile;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = 'ezmedia';
    const IS_SEARCHABLE = false;

    protected $allowedValidators = array(
        'ezp\\Content\\FieldType\\BinaryFile\\FileSizeValidator'
    );

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * Default value for ezmedia is a media of HTML5 video type
     *
     * @return \ezp\Content\FieldType\Media\Value
     */
    protected function getDefaultValue()
    {
        $value = new MediaValue;
        $value->type = MediaValue::TYPE_HTML5_VIDEO;
        return $value;
    }

    /**
     * Checks if $inputValue can be parsed.
     * If the $inputValue actually can be parsed, the value is returned.
     * Otherwise, an \ezp\Base\Exception\BadFieldTypeInput exception is thrown
     *
     * @throws \ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @param \ezp\Content\FieldType\Media\Value $inputValue
     * @return \ezp\Content\FieldType\Media\Value
     */
    protected function canParseValue( BaseValue $inputValue )
    {
        if ( $inputValue instanceof MediaValue )
        {
            $allowedTypes = array(
                MediaValue::TYPE_HTML5_VIDEO,
                MediaValue::TYPE_HTML5_AUDIO,
                MediaValue::TYPE_FLASH,
                MediaValue::TYPE_QUICKTIME,
                MediaValue::TYPE_REALPLAYER,
                MediaValue::TYPE_SILVERLIGHT,
                MediaValue::TYPE_WINDOWSMEDIA
            );
            if ( !empty( $inputValue->type ) && !in_array( $inputValue->type, $allowedTypes ) )
                throw new InvalidArgumentValue ( 'media type', $inputValue->type, get_class( $inputValue ) );

            if ( isset( $inputValue->file ) && !$inputValue->file instanceof BinaryFile )
                throw new BadFieldTypeInput( $inputValue, get_class() );

            return $inputValue;
        }

        throw new InvalidArgumentType( 'value', 'ezp\\Content\\FieldType\\Media\\Value' );
    }

    /**
     * BinaryFile does not support sorting
     *
     * @return bool
     */
    protected function getSortInfo()
    {
        return false;
    }

    /**
     * Fills in $value->type and $value->pluginspage according to default value in FieldDefinition
     *
     * @see \ezp\Content\FieldType::onFieldSetValue
     * @param \ezp\Base\Observable $subject
     * @param \ezp\Content\FieldType\Media\Value $value
     */
    protected function onFieldSetValue( Observable $subject, Value $value )
    {
        parent::onFieldSetValue( $subject, $value );
        if ( $subject instanceof Field )
        {
            $defaultValue = $subject->getFieldDefinition()->getDefaultValue();
            $value->type = $defaultValue->type;
            if ( !isset( $value->pluginspage ) )
            {
                $value->pluginspage = $value->getHandler()->getPluginspageByType( $value->type );
            }
        }
    }
}
