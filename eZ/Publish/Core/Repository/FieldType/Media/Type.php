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
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Exception\InvalidArgumentValue,
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
    public function getDefaultValue()
    {
        return new Value;
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
            throw new InvalidArgumentType( 'value', 'eZ\\Publish\\Core\\Repository\\FieldType\\Media\\Value' );
        }

        if ( isset( $inputValue->file ) && !$inputValue->file instanceof BinaryFile )
        {
            throw new InvalidArgumentValue( $inputValue, get_class( $this ) );
        }

        return $inputValue;
    }

    /**
     * BinaryFile does not support sorting
     *
     * @return bool
     */
    protected function getSortInfo( BaseValue $value )
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

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Value $value
     */
    public function fromHash( $hash )
    {
        throw new \Exception( "Not implemented yet" );
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
        throw new \Exception( "Not implemented yet" );
        return $value->value;
    }
}
