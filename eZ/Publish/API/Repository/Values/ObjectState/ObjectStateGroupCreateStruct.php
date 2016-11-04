<?php

/**
 * File containing the ObjectStateGroupCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\ObjectState;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a value for creating object state groups.
 */
class ObjectStateGroupCreateStruct extends ValueObject
{
    /**
     * Readable unique string identifier of a group.
     *
     * Required.
     *
     * @var string
     */
    public $identifier;

    /**
     * The default language code.
     *
     * Required.
     *
     * @var string
     */
    public $defaultLanguageCode;

    /**
     * An array of names with languageCode keys.
     *
     * Required. - at least one name in the main language is required
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
