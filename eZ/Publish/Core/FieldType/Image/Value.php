<?php
/**
 * File containing the Image Value class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;

/**
 * Value for Image field type
 *
 * @property string $fileName Display file name of the image.
 * @property string $path Path where the image can be found
 * @property string $alternativeText Alternative text for the image
 *
 * @todo Mime type?
 * @todo Dimensions?
 */
class Value extends BaseValue
{
    /**
     * The alternative image text (for example "Picture of an apple.").
     *
     * @var string
     */
    public $alternativeText;

    /**
     * Display file name of the image
     *
     * @var string
     * @required
     */
    public $fileName;

    /**
     * Size of the image file
     *
     * @var int
     * @required
     */
    public $fileSize;

    /**
     * Path string, where the image is located
     *
     * @var string
     * @required
     */
    public $path;

    /**
     * Construct a new Value object.
     *
     * @param array $fileData
     */
    public function __construct( array $imageData = array() )
    {
        foreach ( $imageData as $key => $value )
        {
            try
            {
                $this->$key = $value;
            }
            catch ( PropertyNotFoundException $e )
            {
                throw new InvalidArgumentType(
                    sprintf( '$imageData->%s', $key ),
                    'Property not found',
                    $value
                );
            }
        }
    }

    /**
     * Creates a value only from a file path
     *
     * @param string $path
     *
     * @return Value
     */
    public static function fromString( $path )
    {
        if ( !file_exists( $path ) )
        {
            throw new InvalidArgumentType(
                '$path',
                'existing file',
                $path
            );
        }
        return new static(
            array(
                'path' => $path,
                'fileName' => basename( $path ),
                'fileSize' => filesize( $path ),
                'alternativeText' => '',
            )
        );
    }

    /**
     * Returns the image file size in byte
     *
     * @return integer
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->fileName;
    }
}
