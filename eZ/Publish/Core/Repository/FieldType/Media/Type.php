<?php
/**
 * File containing the Media Type class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Media;
use eZ\Publish\Core\Repository\FieldType\FieldType,
    eZ\Publish\API\Repository\Repository,
    ezp\Content\Field,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    ezp\Base\Observable,
    eZ\Publish\API\Repository\Values\IO\BinaryFile;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
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
     * @var \eZ\Publish\API\Repository\IOService
     */
    protected $IOService;

    /**
     * Constructs field type object, initializing internal data structures.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct( Repository $repository )
    {
        $this->IOService = $repository->getIOService();
    }

    /**
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $file as value.
     *
     * @param string $file
     * @return \eZ\Publish\Core\Repository\FieldType\Media\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( $file )
    {
        return new Value( $this->IOService, $file );
    }

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezmedia';
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Media\Value
     */
    public function getDefaultDefaultValue()
    {
        return new Value( $this->IOService );
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\Repository\FieldType\Media\Value $inputValue
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Media\Value
     */
    public function acceptValue( $inputValue )
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
    protected function getSortInfo( $value )
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Media\Value $value
     */
    public function fromHash( $hash )
    {
        throw new \Exception( "Not implemented yet" );
        return new Value( $this->IOService, $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\Repository\FieldType\Media\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        throw new \Exception( "Not implemented yet" );
        return $value->value;
    }
}
