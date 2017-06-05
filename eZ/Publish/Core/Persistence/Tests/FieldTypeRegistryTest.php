<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Tests\FieldTypeRegistryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Tests;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\FieldTypeRegistry;
use eZ\Publish\SPI\Persistence\Content\StorageHandler;
use eZ\Publish\SPI\Persistence\Content\StorageHandlerRegistry;
use eZ\Publish\SPI\Persistence\FieldType;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;

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
        $registry = new FieldTypeRegistry(
            ['some-type' => $fieldType],
            $this->getStorageHandlerRegistryMock(),
            $this->getStorageHandlerMock()
        );

        $this->assertAttributeSame(
            [
                'some-type' => $fieldType,
            ],
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
        $registry = new FieldTypeRegistry(
            ['some-type' => $instance],
            $this->getStorageHandlerRegistryMock(),
            $this->getStorageHandlerMock()
        );

        $result = $registry->getFieldType('some-type');

        $this->assertInstanceOf(FieldType::class, $result);
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
        $registry = new FieldTypeRegistry(
            ['some-type' => $closure],
            $this->getStorageHandlerRegistryMock(),
            $this->getStorageHandlerMock()
        );

        $result = $registry->getFieldType('some-type');

        $this->assertInstanceOf(FieldType::class, $result);
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
        $registry = new FieldTypeRegistry(
            [],
            $this->getStorageHandlerRegistryMock(),
            $this->getStorageHandlerMock()
        );
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
        $registry = new FieldTypeRegistry(
            [],
            $this->getStorageHandlerRegistryMock(),
            $this->getStorageHandlerMock()
        );
        $registry->getFieldType('not-found');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getFieldType
     *
     * @expectedException \RuntimeException
     */
    public function testGetNotCallableOrInstance()
    {
        $registry = new FieldTypeRegistry(
            ['some-type' => new \DateTime()],
            $this->getStorageHandlerRegistryMock(),
            $this->getStorageHandlerMock()
        );
        $registry->getFieldType('some-type');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::register
     */
    public function testRegister()
    {
        $fieldType = $this->getFieldTypeMock();
        $registry = new FieldTypeRegistry(
            [],
            $this->getStorageHandlerRegistryMock(),
            $this->getStorageHandlerMock()
        );
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
     * Returns a mock for persistence field type.
     *
     * @return \eZ\Publish\SPI\Persistence\FieldType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFieldTypeMock()
    {
        return $this->getMock(
            SPIFieldType::class
        );
    }

    /**
     * Returns a mock for persistence storage handler registry.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\StorageHandlerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStorageHandlerRegistryMock()
    {
        return $this->getMock(StorageHandlerRegistry::class);
    }

    /**
     * Returns a mock for persistence storage handler.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\StorageHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStorageHandlerMock()
    {
        return $this->getMock(StorageHandler::class);
    }
}
