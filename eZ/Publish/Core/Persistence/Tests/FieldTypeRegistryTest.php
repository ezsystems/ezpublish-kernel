<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Tests\FieldTypeRegistryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Tests;

use eZ\Publish\Core\Base\Exceptions\NotFound\FieldTypeNotFoundException;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\FieldTypeRegistry;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;
use eZ\Publish\SPI\Persistence\FieldType as SPIPersistenceFieldType;
use RuntimeException;

/**
 * Test case for FieldTypeRegistry.
 */
class FieldTypeRegistryTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::__construct
     */
    public function testConstructor()
    {
        $fieldType = $this->getFieldTypeMock();
        $registry = new FieldTypeRegistry(array('some-type' => $fieldType));

        $this->assertAttributeSame(
            array(
                'some-type' => $fieldType,
            ),
            'coreFieldTypeMap',
            $registry
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getFieldType
     */
    public function testGetFieldTypeInstance()
    {
        $instance = $this->getFieldTypeMock();
        $registry = new FieldTypeRegistry(array('some-type' => $instance));

        $result = $registry->getFieldType('some-type');

        $this->assertInstanceOf(SPIPersistenceFieldType::class, $result);
        $this->assertAttributeSame(
            $instance,
            'internalFieldType',
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getFieldType
     */
    public function testGetFieldTypeCallable()
    {
        $instance = $this->getFieldTypeMock();
        $closure = function () use ($instance) {
            return $instance;
        };
        $registry = new FieldTypeRegistry(array('some-type' => $closure));

        $result = $registry->getFieldType('some-type');

        $this->assertInstanceOf(SPIPersistenceFieldType::class, $result);
        $this->assertAttributeSame(
            $instance,
            'internalFieldType',
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getFieldType
     *
     * @since 5.3.2
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFound\FieldTypeNotFoundException
     */
    public function testGetNotFound()
    {
        $registry = new FieldTypeRegistry(array());
        $registry->getFieldType('not-found');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getFieldType
     *
     * BC with 5.0-5.3.2
     * @expectedException \RuntimeException
     */
    public function testGetNotFoundBCException()
    {
        $registry = new FieldTypeRegistry(array());
        $registry->getFieldType('not-found');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getFieldType
     *
     * @expectedException \RuntimeException
     */
    public function testGetNotCallableOrInstance()
    {
        $registry = new FieldTypeRegistry(array('some-type' => new \DateTime()));
        $registry->getFieldType('some-type');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::register
     */
    public function testRegister()
    {
        $fieldType = $this->getFieldTypeMock();
        $registry = new FieldTypeRegistry(array());
        $registry->register('some-type', $fieldType);

        $this->assertAttributeSame(
            array(
                'some-type' => $fieldType,
            ),
            'coreFieldTypeMap',
            $registry
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getCoreFieldType
     */
    public function testGetCoreFieldTypeInstance(): void
    {
        $instance = $this->getFieldTypeMock();
        $registry = new FieldTypeRegistry(array('some-type' => $instance));

        $result = $registry->getCoreFieldType('some-type');

        $this->assertInstanceOf(SPIFieldType::class, $result);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getCoreFieldType
     */
    public function testGetCoreFieldTypeCallable(): void
    {
        $instance = $this->getFieldTypeMock();
        $closure = function () use ($instance) {
            return $instance;
        };
        $registry = new FieldTypeRegistry(array('some-type' => $closure));

        $result = $registry->getCoreFieldType('some-type');

        $this->assertInstanceOf(SPIFieldType::class, $result);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getCoreFieldType
     */
    public function testGetCoreFieldTypeNotFound(): void
    {
        $registry = new FieldTypeRegistry([]);

        $this->expectException(FieldTypeNotFoundException::class);
        $registry->getCoreFieldType('not-found');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getCoreFieldType
     */
    public function testGetCoreFieldTypeNotCallableOrInstance(): void
    {
        $registry = new FieldTypeRegistry(array('some-type' => new \DateTime()));
        $this->expectException(RuntimeException::class);
        $registry->getCoreFieldType('some-type');
    }

    /**
     * Returns a mock for persistence field type.
     *
     * @return \eZ\Publish\SPI\FieldType\FieldType|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFieldTypeMock()
    {
        return $this->createMock(SPIFieldType::class);
    }
}
