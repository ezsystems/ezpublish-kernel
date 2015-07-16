<?php
/**
 * File containing the VersionStateUpdateStruct class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\VersionState;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a value for updating version states
 */
class VersionStateUpdateStruct extends ValueObject
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
