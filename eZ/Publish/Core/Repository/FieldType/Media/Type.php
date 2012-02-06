<?php
/**
 * File containing the Media Type class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Media;
use eZ\Publish\Core\Repository\FieldType,
    eZ\Publish\Core\Repository\FieldType\Value as BaseValue,
    ezp\Content\Field,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Observable,
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

    const TYPE_FLASH = 'flash',
          TYPE_QUICKTIME = 'quick_time',
          TYPE_REALPLAYER = 'real_player',
          TYPE_SILVERLIGHT = 'silverlight',
          TYPE_WINDOWSMEDIA = 'windows_media_player',
          TYPE_HTML5_VIDEO = 'html5_video',
          TYPE_HTML5_AUDIO = 'html5_audio';

    protected $allowedValidators = array(
        'eZ\\Publish\\Core\\Repository\\FieldType\\BinaryFile\\FileSizeValidator'
    );

    /*
     * mediaType can be one of those values:
     *   - flash
     *   - quick_time
     *   - real_player
     *   - siverlight
     *   - windows_media_player
     *   - html5_video
     *   - html5_audio
     *
     * Default value for ezmedia is a media of HTML5 video type
     */
    protected $allowedSettings = array(
        'mediaType' => self::TYPE_HTML5_VIDEO
    );

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Media\Value
     */
    protected function getDefaultValue()
    {
        return new Value;
    }

    /**
     * Checks if $inputValue can be parsed.
     * If the $inputValue actually can be parsed, the value is returned.
     * Otherwise, an \ezp\Base\Exception\BadFieldTypeInput exception is thrown
     *
     * @throws \ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @throws \ezp\Base\Exception\InvalidArgumentType
     * @param \eZ\Publish\Core\Repository\FieldType\Media\Value $inputValue
     * @return \eZ\Publish\Core\Repository\FieldType\Media\Value
     */
    protected function canParseValue( BaseValue $inputValue )
    {
        if ( $inputValue instanceof Value )
        {
            if ( isset( $inputValue->file ) && !$inputValue->file instanceof BinaryFile )
                throw new BadFieldTypeInput( $inputValue, get_class( $this ) );

            return $inputValue;
        }

        throw new InvalidArgumentType( 'value', 'eZ\\Publish\\Core\\Repository\\FieldType\\Media\\Value' );
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
     * Fills in $value->type and $value->pluginspage according to field settings set in FieldDefinition
     *
     * @see \eZ\Publish\Core\Repository\FieldType::onFieldSetValue()
     * @param \ezp\Base\Observable $subject
     * @param \eZ\Publish\Core\Repository\FieldType\Media\Value $value
     */
    protected function onFieldSetValue( Observable $subject, BaseValue $value )
    {
        parent::onFieldSetValue( $subject, $value );
        if ( $subject instanceof Field )
        {
            if ( !isset( $value->pluginspage ) )
            {
                $value->pluginspage = $value->getHandler()->getPluginspageByType( $this->fieldSettings['mediaType'] );
            }
        }
    }
}
