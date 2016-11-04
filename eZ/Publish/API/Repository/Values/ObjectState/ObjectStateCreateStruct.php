<?php

/**
 * File containing the ObjectStateCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\ObjectState;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a value for creating object states.
 */
class ObjectStateCreateStruct extends ValueObject
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
     * Priority for ordering. If not set the object state is created as the last one.
     *
     * @var int
     */
    public $priority = false;

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
