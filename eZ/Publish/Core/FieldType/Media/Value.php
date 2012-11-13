<?php
/**
 * File containing the Media Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
     * @var bool
     */
    public $hasController = false;

    /**
     * if the media should be automatically played
     *
     * @var bool
     */
    public $autoplay = false;

    /**
     * if the media should be played in a loop
     *
     * @var bool
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
