<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence;

use eZ\Publish\Core\Base\Exceptions\NotFound\FieldTypeNotFoundException;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;
use eZ\Publish\SPI\Persistence\FieldType as FieldTypeInterface;

/**
 * Registry for field types available to storage engines.
 */
class FieldTypeRegistry
{
    /**
     * Map of FieldTypes where key is field type identifier and value is FieldType object complying
     * to {@link \eZ\Publish\SPI\FieldType\FieldType} interface.
     *
     * @var \eZ\Publish\SPI\FieldType\FieldType[]
     */
    protected $coreFieldTypes;

    /**
     * Map of FieldTypes where key is field type identifier and value is FieldType object.
     *
     * @var \eZ\Publish\SPI\Persistence\FieldType[]
     */
    protected $fieldTypes;

    /**
     * Creates FieldType registry.
     *
     * In $fieldTypes a mapping of field type identifier to object is expected.
     * The FieldType object must comply to the {@link \eZ\Publish\SPI\FieldType\FieldType} interface.
     *
     * @param \eZ\Publish\Core\Persistence\FieldType[] $coreFieldTypes
     * @param SPIFieldType[] $fieldTypes A map where key is field type identifier and value is
     *                                                          a callable factory to get FieldType OR FieldType object.
     */
    public function __construct(array $coreFieldTypes = [], array $fieldTypes = [])
    {
        $this->coreFieldTypes = $coreFieldTypes;
        $this->fieldTypes = $fieldTypes;
    }

    /**
     * Returns the FieldType object for given $identifier.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\Persistence\FieldType
     *
     * @throws \RuntimeException If field type for given $identifier is not instance or callable.
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFound\FieldTypeNotFoundException If field type for given $identifier is not found.
     */
    public function getFieldType(string $identifier): FieldTypeInterface
    {
        if (!isset($this->fieldTypes[$identifier])) {
            $this->fieldTypes[$identifier] = new FieldType($this->getCoreFieldType($identifier));
        }

        return $this->fieldTypes[$identifier];
    }

    public function register(string $identifier, SPIFieldType $fieldType): void
    {
        $this->coreFieldTypes[$identifier] = $fieldType;
    }

    protected function getCoreFieldType(string $identifier): SPIFieldType
    {
        if (!isset($this->coreFieldTypes[$identifier])) {
            throw new FieldTypeNotFoundException($identifier);
        }

        return $this->coreFieldTypes[$identifier];
    }
}
