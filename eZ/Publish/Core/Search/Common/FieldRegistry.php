<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common;

use eZ\Publish\SPI\FieldType\Indexable;

/**
 * Registry for field type's Indexable interface implementations available to Search Engines.
 */
class FieldRegistry
{
    /**
     * Registered field types.
     *
     * @var array(string => Indexable)
     */
    protected $types = [];

    /**
     * Construct from optional Indexable type array.
     *
     * @param \eZ\Publish\SPI\FieldType\Indexable[] $types
     */
    public function __construct(array $types = [])
    {
        foreach ($types as $name => $type) {
            $this->registerType($name, $type);
        }
    }

    /**
     * Register another indexable type.
     *
     * @param string $name
     * @param \eZ\Publish\SPI\FieldType\Indexable $type
     */
    public function registerType($name, Indexable $type)
    {
        $this->types[$name] = $type;
    }

    /**
     * Get Indexable type.
     *
     * @param string $name
     *
     * @return \eZ\Publish\SPI\FieldType\Indexable
     */
    public function getType($name)
    {
        if (!isset($this->types[$name])) {
            throw new \OutOfBoundsException("No type registered for $name.");
        }

        return $this->types[$name];
    }
}
