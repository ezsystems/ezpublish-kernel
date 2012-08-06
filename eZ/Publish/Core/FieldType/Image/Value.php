<?php
/**
 * File containing the Image Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image;
use eZ\Publish\Core\FieldType\Value as BaseValue,
    eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;

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
     */
    public $fileName;

    /**
     * Path string, where the image is located
     *
     * @var string
     * @todo This could better be an URI? E.g. file://… or http://… or …
     */
    public $path;

    /**
     * Construct a new Value object.
     *
     * @param \eZ\Publish\API\Repository\IOService $IOService
     * @param string|null $file
     * @param string $alternativeText
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
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return $this->fileName;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value::getTitle()
     */
    public function getTitle()
    {
        return !empty( $this->alternativeText ) ? $this->alternativeText : $this->fileName;
    }
}
