<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\MultiLanguageCreateStructBase class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\API\Repository\Values;

/**
 * This is the base class for create structs of domain objects providing
 * an identifier and multi language names and descriptions
 *
 * @package eZ\Publish\API\Repository\Values
 */
abstract class MultiLanguageCreateStructBase extends ValueObject
{
    /**
     * String unique identifier of the domain object
     *
     * @required
     *
     * @var string
     */
    public $identifier;

    /**
     * An array of names with languageCode keys
     *
     * @required - at least one name in the main language is required
     *
     * @var array an array of string
     */
    public $names;

    /**
     * An array of descriptions with languageCode keys
     *
     * @var array an array of string
     */
    public $descriptions;

    /**
     * the main language code used as fallback
     *
     * @var string
     */
    public $mainLanguageCode;
}
