<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Tests;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\FieldTypeRegistry;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;
use eZ\Publish\SPI\Persistence\FieldType as SPIPersistenceFieldType;

/**
 * Test case for FieldTypeRegistry.
 */
class FieldTypeRegistryTest extends TestCase
{
    private const FIELD_TYPE_IDENTIFIER = 'some-type';

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::__construct
     */
    public function testConstructor(): void
    {
        $fieldType = $this->getFieldTypeMock();
        $registry = new FieldTypeRegistry([self::FIELD_TYPE_IDENTIFIER => $fieldType]);

        $this->assertInstanceOf(
            SPIPersistenceFieldType::class,
            $registry->getFieldType(self::FIELD_TYPE_IDENTIFIER)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getFieldType
     */
    public function testGetFieldTypeInstance()
    {
        $instance = $this->getFieldTypeMock();
        $registry = new FieldTypeRegistry([self::FIELD_TYPE_IDENTIFIER => $instance]);

        $result = $registry->getFieldType(self::FIELD_TYPE_IDENTIFIER);

        $this->assertInstanceOf(SPIPersistenceFieldType::class, $result);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getFieldType
     *
     * @since 5.3.2
     */
    public function testGetNotFound()
    {
        $this->expectException(\eZ\Publish\Core\Base\Exceptions\NotFound\FieldTypeNotFoundException::class);

        $registry = new FieldTypeRegistry([]);
        $registry->getFieldType('not-found');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getFieldType
     *
     * BC with 5.0-5.3.2
     */
    public function testGetNotFoundBCException()
    {
        $this->expectException(\RuntimeException::class);

        $registry = new FieldTypeRegistry([]);
        $registry->getFieldType('not-found');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getFieldType
     */
    public function testGetNotInstance()
    {
        $this->expectException(\TypeError::class);

        $registry = new FieldTypeRegistry([self::FIELD_TYPE_IDENTIFIER => new \DateTime()]);
        $registry->getFieldType(self::FIELD_TYPE_IDENTIFIER);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::registerFieldType
     */
    public function testRegister()
    {
        $fieldType = $this->getFieldTypeMock();
        $registry = new FieldTypeRegistry([]);
        $registry->register(self::FIELD_TYPE_IDENTIFIER, $fieldType);

        $this->assertInstanceOf(
            SPIPersistenceFieldType::class,
            $registry->getFieldType(self::FIELD_TYPE_IDENTIFIER)
        );
    }

    /**
     * Returns a mock for persistence field type.
     *
     * @return \eZ\Publish\SPI\Persistence\FieldType
     */
    protected function getFieldTypeMock()
    {
        return $this->createMock(SPIFieldType::class);
    }
}
