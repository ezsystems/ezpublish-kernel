<?php

/**
 * File containing FieldTypeService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\Helper;

use eZ\Publish\SPI\FieldType\Nameable as SPIFieldTypeNameable;
use eZ\Publish\Core\Base\Exceptions\NotFound\FieldTypeNotFoundException;
use RuntimeException;

/**
 * Registry for SPI FieldTypes.
 */
class NameableFieldTypeRegistry
{
    /**
     * @var \eZ\Publish\SPI\FieldType\Nameable[] Hash of SPI FieldTypes where key is identifier
     */
    protected $fieldTypes;

    /**
     * @param \eZ\Publish\SPI\FieldType\Nameable[]|\Closure $fieldTypes Hash of SPI FieldTypes where key is identifier
     */
    public function __construct(array $fieldTypes = array())
    {
        $this->fieldTypes = $fieldTypes;
    }

    /**
     * Returns a list of all SPI FieldTypes.
     *
     * @return \eZ\Publish\SPI\FieldType\Nameable[]
     */
    public function getFieldTypes()
    {
        // First make sure all items are correct type (call closures)
        foreach ($this->fieldTypes as $identifier => $value) {
            if (!$value instanceof SPIFieldTypeNameable) {
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
     * @return \eZ\Publish\SPI\FieldType\Nameable
     */
    public function getFieldType($identifier)
    {
        if (!isset($this->fieldTypes[$identifier])) {
            throw new FieldTypeNotFoundException($identifier);
        }

        if ($this->fieldTypes[$identifier] instanceof SPIFieldTypeNameable) {
            return $this->fieldTypes[$identifier];
        } elseif (is_callable($this->fieldTypes[$identifier])) {
            /** @var $closure \Closure */
            $closure = $this->fieldTypes[$identifier];
            $this->fieldTypes[$identifier] = $closure();
        }

        if (!$this->fieldTypes[$identifier] instanceof SPIFieldTypeNameable) {
            throw new RuntimeException("\$fieldTypes[$identifier] must be instance of SPI\\FieldType\\Nameable or callable");
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
