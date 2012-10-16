<?php
/**
 * File containing the ObjectStateUpdateStruct class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ObjectState;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a value for updating object states
 */
class ObjectStateUpdateStruct extends ValueObject
{
    /**
     * Readable unique string identifier of a group
     *
     * @var string
     */
    public $identifier;

    /**
     * The default language code
     *
     * @var string
     */
    public $defaultLanguageCode;

     /**
     * An array of names with languageCode keys
     *
     * @var string[]
     */
    public $names;

    /**
     * An array of descriptions with languageCode keys
     *
     * @var string[]
     */
    public $descriptions;

}
