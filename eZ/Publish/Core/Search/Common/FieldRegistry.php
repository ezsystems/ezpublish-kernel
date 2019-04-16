<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common;

use eZ\Publish\SPI\FieldType\Indexable;
use OutOfBoundsException;

/**
 * Registry for field type's Indexable interface implementations available to Search Engines.
 */
class FieldRegistry
{
    /**
     * @var \eZ\Publish\SPI\FieldType\Indexable[]
     */
    protected $types;

    /**
     * @param \eZ\Publish\SPI\FieldType\Indexable[] $types
     */
    public function __construct(array $types = [])
    {
        foreach ($types as $name => $type) {
            $this->registerType($name, $type);
        }
    }

    public function registerType(string $name, Indexable $type): void
    {
        $this->types[$name] = $type;
    }

    public function getType(string $name): Indexable
    {
        if (!isset($this->types[$name])) {
            throw new OutOfBoundsException('Field type "' . $name . '" is not indexable. Please provide \eZ\Publish\SPI\FieldType\Indexable implementation and register it with "ezpublish.fieldType.indexable" tag.');
        }

        return $this->types[$name];
    }
}
