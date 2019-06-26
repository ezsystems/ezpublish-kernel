<?php

/**
 * File containing the ImageVariation class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Variation\Values;

/**
 * @property-read int $width The width as number of pixels (for example "320")
 * @property-read int $height The height as number of pixels (for example "256")
 * @property-read string $name The name of the image alias (for example "original")
 * @property-read mixed $info Extra information about the image, depending on the image type
 * @property-read mixed $imageId
 */
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
     * Beware: This information may contain e.g. HTML, JavaScript, or PHP code, and should be treated like any
     * other user-submitted data. Make sure it is properly escaped before use.
     *
     * @var mixed
     */
    protected $info;

    /** @var mixed */
    protected $imageId;
}
