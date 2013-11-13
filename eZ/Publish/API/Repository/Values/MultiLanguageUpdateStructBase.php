<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\MultiLanguageUpdateStructBase class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\API\Repository\Values;

/**
 * This is the base class for update structs of domain objects providing
 * an identifier and multi language names and descriptions
 *
 * @package eZ\Publish\API\Repository\Values
 */
class MultiLanguageUpdateStructBase extends ValueObject
{
    /**
     * If set the unique identifier of the domain object is updated
     *
     * @required
     *
     * @var string
     */
    public $identifier;

    /**
     * If set the array of names with languageCode keys is updated
     *
     * @required - at least one name in the main language is required
     *
     * @var array an array of string
     */
    public $names;

    /**
     * If set the array of descriptions with languageCode keys is updated
     *
     * @var array an array of string
     */
    public $descriptions;

    /**
     * if set the main language code is changed
     * @var string
     */
    public $mainLanguageCode;

}
