<?php

/**
 * File containing FieldTypeService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Helper;

use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;
use eZ\Publish\Core\Base\Exceptions\NotFound\FieldTypeNotFoundException;
use RuntimeException;

/**
 * Registry for SPI FieldTypes.
 *
 * @internal Meant for internal use by Repository.
 */
class FieldTypeRegistry
{
    /** @var \eZ\Publish\SPI\FieldType\FieldType[] Hash of SPI FieldTypes where key is identifier */
    protected $fieldTypes;

    /**
     * @param \eZ\Publish\SPI\FieldType\FieldType[]|\Closure $fieldTypes Hash of SPI FieldTypes where key is identifier
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
    public function getFieldTypes()
    {
        // First make sure all items are correct type (call closures)
        foreach ($this->fieldTypes as $identifier => $value) {
            if (!$value instanceof SPIFieldType) {
                $this->getFieldType($identifier);
            }
        }

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
    public function getFieldType($identifier)
    {
        if (!isset($this->fieldTypes[$identifier])) {
            throw new FieldTypeNotFoundException($identifier);
        }

        if ($this->fieldTypes[$identifier] instanceof SPIFieldType) {
            return $this->fieldTypes[$identifier];
        } elseif (is_callable($this->fieldTypes[$identifier])) {
            /** @var $closure \Closure */
            $closure = $this->fieldTypes[$identifier];
            $this->fieldTypes[$identifier] = $closure();
        }

        if (!$this->fieldTypes[$identifier] instanceof SPIFieldType) {
            throw new RuntimeException("\$fieldTypes[$identifier] must be instance of SPI\\FieldType\\FieldType or callable");
        }

        return $this->fieldTypes[$identifier];
    }

    /**
     * Returns if there is a SPI FieldType registered under $identifier.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function hasFieldType($identifier)
    {
        return isset($this->fieldTypes[$identifier]);
    }
}
