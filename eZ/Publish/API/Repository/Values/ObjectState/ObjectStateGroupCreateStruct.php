<?php
/**
 * File containing the ObjectStateGroupCreateStruct class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ObjectState;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a value for creating object state groups
 */
class ObjectStateGroupCreateStruct extends ValueObject
{
    /**
     * Readable unique string identifier of a group
     *
     * @required
     *
     * @var string
     */
    public $identifier;

    /**
     * The default language code
     *
     * @required
     *
     * @var string
     */
    public $defaultLanguageCode;

    /**
     * An array of names with languageCode keys
     *
     * @required - at least one name in the main language is required
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
