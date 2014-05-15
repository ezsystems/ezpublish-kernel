<?php
/**
 * File containing the Media Value class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Media;

use eZ\Publish\Core\FieldType\BinaryBase\Value as BaseValue;

/**
 * Value for Media field type
 */
class Value extends BaseValue
{
    /**
     * If the media has a controller when being displayed
     *
     * @var boolean
     */
    public $hasController = false;

    /**
     * If the media should be automatically played
     *
     * @var boolean
     */
    public $autoplay = false;

    /**
     * If the media should be played in a loop
     *
     * @var boolean
     */
    public $loop = false;

    /**
     * Height of the media
     *
     * @var int
     */
    public $height = 0;

    /**
     * Width of the media
     *
     * @var int
     */
    public $width = 0;
}
