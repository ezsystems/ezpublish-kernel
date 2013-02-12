<?php
/**
 * File containing the Language CreateStruct class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Language;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * Struct containing accessible properties when creating Language entities.
 */
class CreateStruct extends ValueObject
{
    /**
     * Language Code (eg: eng-GB)
     *
     * @var string
     */
    public $languageCode;

    /**
     * Human readable language name
     *
     * @var string
     */
    public $name;

    /**
     * Indicates if language is enabled or not
     *
     * @var boolean
     */
    public $isEnabled = true;
}
