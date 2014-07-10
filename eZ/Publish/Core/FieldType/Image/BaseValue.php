<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Image;

use eZ\Publish\Core\FieldType\Value as FieldTypeValue;

/**
 * Base Value for image. Contains user-definable attributes.
 */
abstract class BaseValue extends FieldTypeValue
{
    /**
     * The alternative image text ("Picture of an apple")
     *
     * @var string|null
     */
    public $alternativeText;

    /**
     * Display file name of the image ("Picture-of-an-Apple.png")
     *
     * @var string
     * @required
     */
    public $fileName;

    /**
     * Size of the image file
     *
     * @var int
     */
    public $fileSize;

    public function __toString()
    {
        return (string)$this->fileName;
    }
}
