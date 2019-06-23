<?php

/**
 * File containing the Base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Page\Parts;

use eZ\Publish\API\Repository\Values\ValueObject;

abstract class Base extends ValueObject
{
    const ACTION_ADD = 'add';

    const ACTION_MODIFY = 'modify';

    const ACTION_REMOVE = 'remove';

    /**
     * Hash of arbitrary attributes.
     *
     * @var array
     */
    public $attributes;

    /**
     * Constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        $this->attributes = [];
        parent::__construct($properties);
    }

    /**
     * Returns available properties with their values as a simple hash.
     *
     * @return array
     */
    public function getState()
    {
        $hash = [];

        foreach ($this->getProperties() as $property) {
            $hash[$property] = $this->$property;
        }

        return $hash;
    }
}
