<?php
/**
 * File containing the BinaryBase Value class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\BinaryBase;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * Base value for binary field types
 * @property string $path Used for BC with 5.0 (EZP-20948). Equivalent to $id.
 */
abstract class Value extends BaseValue
{
    /**
     * @todo This doesn't really make sense here...
     * What is the point of exposing this ? It makes no sense as seen from outside (no storage dir nor prefix)
     * but a path *also* doesn't make sense when we move to a cloud/remote storage
     * On the other hand, this property IS required when INPUTING files, as they need to be read from
     * somewhere. It makes no harm, but is still confusing.
     *
     * Unique file ID
     *
     * @var string
     * @required
     */
    public $id;

    /**
     * Display file name
     *
     * @var string
     */
    public $fileName;

    /**
     * Size of the image file
     *
     * @var int
     */
    public $fileSize;

    /**
     * Mime type of the file
     *
     * @var string
     */
    public $mimeType;

    /**
     * HTTP URI
     * @var string
     */
    public $uri;

    /**
     * Construct a new Value object.
     *
     * @param array $fileData
     */
    public function __construct( array $fileData = array() )
    {
        // BC with 5.0 (EZP-20948)
        if ( isset( $fileData['path'] ) )
        {
            $fileData['id'] = $fileData['path'];
            unset( $fileData['path'] );
        }

        foreach ( $fileData as $key => $value )
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
     * Returns a string representation of the field value.
     * This string representation must be compatible with format accepted via
     * {@link \eZ\Publish\SPI\FieldType\FieldType::buildValue}
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->uri;
    }

    public function __get( $propertyName )
    {
        if ( $propertyName == 'path' )
            return $this->id;

        parent::__get( $propertyName );
    }

    public function __set( $propertyName, $propertyValue )
    {
        if ( $propertyName == 'path' )
            $this->id = $propertyValue;

        parent::__set( $propertyName, $propertyValue );
    }

    public function __isset( $propertyName )
    {
        if ( $propertyName == 'path' )
            return true;

        parent::__isset( $propertyName );
    }
}
