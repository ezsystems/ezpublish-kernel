<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a value for creating a language
 *
 */
class LanguageCreateStruct extends ValueObject
{

    /**
     * the languageCode code
     *
     * @var string
     */
    public $languageCode;

    /**
     * Human readable name of the language
     *
     * @var string
     */
    public $name;

    /**
     * indicates if the language is enabled or not.
     *
     * @var boolean
     */
    public $enabled = true;
}

