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
    private const INDEXABLE_FIELD_TYPE_TAG = 'ezpublish.fieldType.indexable';

    /** @var \eZ\Publish\SPI\FieldType\Indexable[] */
    protected $types = [];

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
            throw new OutOfBoundsException(
                sprintf(
                    'Field type "%s" is not indexable. Please provide %s implementation and register it with "%s" tag.',
                    $name,
                    Indexable::class,
                    self::INDEXABLE_FIELD_TYPE_TAG
                )
            );
        }

        return $this->types[$name];
    }
}
