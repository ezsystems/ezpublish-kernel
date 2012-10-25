<?php
/**
 * File containing the ImageVariation class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Variation\Values;

class ImageVariation extends Variation
{
    /**
     * The width as number of pixels (for example "320").
     *
     * @var int
     */
    protected $width;

    /**
     * The height as number of pixels (for example "256").
     *
     * @var int
     */
    protected $height;

    /**
     * The name of the image alias (for example "original").
     *
     * @var string
     */
    protected $name;

    /**
     * Contains extra information about the image, depending on the image type.
     * It will typically contain EXIF information from digital cameras or information about animated GIFs.
     * If there is no information, the info will be a boolean FALSE.
     *
     * @var mixed
     */
    protected $info;
}
