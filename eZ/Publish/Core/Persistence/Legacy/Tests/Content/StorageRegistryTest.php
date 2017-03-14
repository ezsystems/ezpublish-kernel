<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\StorageRegistryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Content\StorageRegistry;

/**
 * Test case for StorageRegistry.
 */
class StorageRegistryTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\Persistence\Content\StorageRegistry::register
     */
    public function testRegister()
    {
        $storage = $this->getStorageMock();
        $registry = new StorageRegistry(array('some-type' => $storage));

        $this->assertAttributeSame(
            array(
                'some-type' => $storage,
            ),
            'storageMap',
            $registry
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Content\StorageRegistry::getStorage
     */
    public function testGetStorage()
    {
        $storage = $this->getStorageMock();
        $registry = new StorageRegistry(array('some-type' => $storage));

        $res = $registry->getStorage('some-type');

        $this->assertSame(
            $storage,
            $res
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Content\StorageRegistry::getStorage
     * @covers \eZ\Publish\Core\Persistence\Legacy\Exception\StorageNotFound
     */
    public function testGetNotFound()
    {
        $registry = new StorageRegistry(array());
        self::assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\NullStorage',
            $registry->getStorage('not-found')
        );
    }

    /**
     * Returns a mock for Storage.
     *
     * @return Storage
     */
    protected function getStorageMock()
    {
        return $this->getMock(
            'eZ\\Publish\\SPI\\FieldType\\FieldStorage'
        );
    }
}
