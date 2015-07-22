<?php

/**
 * File containing the ObjectStateGroupUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ObjectState;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a value for updating object state groups.
 */
class ObjectStateGroupUpdateStruct extends ValueObject
{
    /**
     * Readable unique string identifier of a group.
     *
     * @var string
     */
    public $identifier;

    /**
     * The default language code.
     *
     * @var string
     */
    public $defaultLanguageCode;

    /**
     * An array of names with languageCode keys.
     *
     * @var string[]
     */
    public $names;

    /**
     * An array of descriptions with languageCode keys.
     *
     * @var string[]
     */
    public $descriptions;
}
