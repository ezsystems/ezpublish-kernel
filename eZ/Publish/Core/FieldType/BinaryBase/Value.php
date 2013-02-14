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
 */
abstract class Value extends BaseValue
{
    /**
     * Path string, where the binary file is located
     *
     * @var string
     * @required
     */
    public $path;

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
     * Construct a new Value object.
     *
     * @param array $fileData
     */
    public function __construct( array $fileData = array() )
    {
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
        return (string)$this->path;
    }
}
