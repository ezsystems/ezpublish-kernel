<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType;

use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;
use eZ\Publish\Core\Base\Exceptions\NotFound\FieldTypeNotFoundException;

/**
 * Registry for SPI FieldTypes.
 *
 * @internal Meant for internal use by Repository.
 */
class FieldTypeRegistry
{
    /** @var \eZ\Publish\SPI\FieldType\FieldType[] Hash of SPI FieldTypes where key is identifier */
    protected $fieldTypes;

    /** @var string[] */
    private $concreteFieldTypesIdentifiers;

    /**
     * @param \eZ\Publish\SPI\FieldType\FieldType[] $fieldTypes Hash of SPI FieldTypes where key is identifier
     */
    public function __construct(array $fieldTypes = [])
    {
        $this->fieldTypes = $fieldTypes;
    }

    /**
     * Returns a list of all SPI FieldTypes.
     *
     * @return \eZ\Publish\SPI\FieldType\FieldType[]
     */
    public function getFieldTypes(): array
    {
        return $this->fieldTypes;
    }

    /**
     * Return a SPI FieldType object.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFound\FieldTypeNotFoundException If $identifier was not found
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\FieldType\FieldType
     */
    public function getFieldType($identifier): SPIFieldType
    {
        if (!isset($this->fieldTypes[$identifier])) {
            throw new FieldTypeNotFoundException($identifier);
        }

        return $this->fieldTypes[$identifier];
    }

    public function registerFieldType(string $identifier, SPIFieldType $fieldType): void
    {
        $this->fieldTypes[$identifier] = $fieldType;
    }

    /**
     * Returns if there is a SPI FieldType registered under $identifier.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function hasFieldType($identifier): bool
    {
        return isset($this->fieldTypes[$identifier]);
    }

    /**
     * Registers $fieldTypeIdentifier as "concrete" FieldType (i.e. not using NullFieldType).
     */
    public function registerConcreteFieldTypeIdentifier(string $fieldTypeIdentifier): void
    {
        $this->concreteFieldTypesIdentifiers[] = $fieldTypeIdentifier;
    }

    /**
     * @return string[]
     */
    public function getConcreteFieldTypesIdentifiers(): array
    {
        return $this->concreteFieldTypesIdentifiers;
    }
}
