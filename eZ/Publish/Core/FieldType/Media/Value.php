<?php
/**
 * File containing the Media Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Media;
use eZ\Publish\Core\FieldType\BinaryBase\Value as BaseValue,
    eZ\Publish\API\Repository\IOService,
    eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;

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
    public $hasController;

    /**
     * if the media should be automatically played
     *
     * @var bool
     */
    public $autoplay;

    /**
     * if the media should be played in a loop
     *
     * @var bool
     */
    public $loop;

    /**
     * Height of the media
     *
     * @var int
     */
    public $height;

    /**
     * Width of the media
     *
     * @var int
     */
    public $width;
}
