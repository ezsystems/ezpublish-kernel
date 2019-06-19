<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\StorageRegistryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry;
use eZ\Publish\SPI\FieldType\FieldStorage;
use eZ\Publish\Core\FieldType\NullStorage;

/**
 * Test case for StorageRegistry.
 */
class StorageRegistryTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry::register
     */
    public function testRegister()
    {
        $storage = $this->getStorageMock();
        $registry = new StorageRegistry(['some-type' => $storage]);

        $this->assertAttributeSame(
            [
                'some-type' => $storage,
            ],
            'storageMap',
            $registry
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry::getStorage
     */
    public function testGetStorage()
    {
        $storage = $this->getStorageMock();
        $registry = new StorageRegistry(['some-type' => $storage]);

        $res = $registry->getStorage('some-type');

        $this->assertSame(
            $storage,
            $res
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry::getStorage
     * @covers \eZ\Publish\Core\Persistence\Legacy\Exception\StorageNotFound
     */
    public function testGetNotFound()
    {
        $registry = new StorageRegistry([]);
        self::assertInstanceOf(
            NullStorage::class,
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
        return $this->createMock(FieldStorage::class);
    }
}
