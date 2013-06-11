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
 * @property string $path Used for BC with 5.0 (EZP-20948). Equivalent to $id.
 *
 * @todo Mime type?
 * @todo Dimensions?
 */
class Value extends BaseValue
{
    /**
     * Image id
     *
     * @var mixed
     * @required
     */
    public $id;

    /**
     * The alternative image text (for example "Picture of an apple.").
     *
     * @var string|null
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
     * The image's HTTP URI
     * @var string
     */
    public $uri;

    /**
     * External image ID (required by REST for now, see https://jira.ez.no/browse/EZP-20831)
     * @var mixed
     */
    public $imageId;

    /**
     * Construct a new Value object.
     *
     * @param array $imageData
     */
    public function __construct( array $imageData = array() )
    {
        // BC with 5.0 (EZP-20948)
        if ( isset( $imageData['path'] ) )
        {
            $imageData['id'] = $imageData['path'];
            unset( $imageData['path'] );
        }

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
                'id' => $path,
                'fileName' => basename( $path ),
                'fileSize' => filesize( $path ),
                'alternativeText' => '',
                'uri' => '',
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

    public function __get( $propertyName )
    {
        if ( $propertyName == 'path' )
            return $this->id;

        throw new PropertyNotFoundException( $propertyName, get_class( $this ) );
    }

    public function __set( $propertyName, $propertyValue )
    {
        if ( $propertyName == 'path' )
            $this->id = $propertyValue;

        throw new PropertyNotFoundException( $propertyName, get_class( $this ) );
    }
}
