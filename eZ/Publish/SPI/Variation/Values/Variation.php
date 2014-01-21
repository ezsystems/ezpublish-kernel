<?php
/**
 * File containing the file Variation class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Variation\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Base class for file variations (i.e. image aliases)
 *
 * @property-read int $fileSize Number of bytes for current variation
 * @property-read string $mimeType The MIME type (for example "image/png")
 * @property-read string $fileName The name of the file (for example "my_image.png")
 * @property-read string $dirPath The path to the file (for example "var/storage/images/test/199-2-eng-GB")
 * @property-read string $uri Complete path + name of image file (for example "var/storage/images/test/199-2-eng-GB/apple.png")
 * @property-read \DateTime $lastModified When the variation was last modified
 */
class Variation extends ValueObject
{
    /**
     * Number of bytes for current variation
     *
     * @var int
     */
    protected $fileSize;

    /**
     * The MIME type (for example "image/png").
     *
     * @var string
     */
    protected $mimeType;

    /**
     * The name of the file (for example "my_image.png").
     *
     * @var string
     */
    protected $fileName;

    /**
     * The path to the file (for example "var/storage/images/test/199-2-eng-GB").
     *
     * @var string
     */
    protected $dirPath;

    /**
     * Complete path + name of image file (for example "var/storage/images/test/199-2-eng-GB/apple.png").
     *
     * @var string
     */
    protected $uri;

    /**
     * When the variation was last modified.
     *
     * @var \DateTime
     */
    protected $lastModified;
}
