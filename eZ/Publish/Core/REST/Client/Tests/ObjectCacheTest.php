<?php

/**
 * File containing the ObjectCacheTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests;

use eZ\Publish\Core\REST\Client\ObjectCache;
use PHPUnit\Framework\TestCase;

class ObjectCacheTest extends TestCase
{
    public function testStoreRestore()
    {
        $cache = $this->getCache();

        $object = new TestValueObject();

        $cache->store('some-key', $object);

        $this->assertSame(
            $object,
            $cache->restore('some-key')
        );
    }

    public function testStoreOverwrite()
    {
        $cache = $this->getCache();

        $firstObject = new TestValueObject();
        $secondObject = new TestValueObject();

        $cache->store('some-key', $firstObject);
        $cache->store('some-key', $secondObject);

        $this->assertSame(
            $secondObject,
            $cache->restore('some-key')
        );
        $this->assertNotSame(
            $firstObject,
            $cache->restore('some-key')
        );
    }

    public function testRestoreNotAvailable()
    {
        $cache = $this->getCache();

        $this->assertNull($cache->restore('non-existent'));
    }

    public function testClear()
    {
        $cache = $this->getCache();

        $object = new TestValueObject();

        $cache->store('some-key', $object);
        $cache->clear('some-key');

        $this->assertNull($cache->restore('some-key'));
    }

    public function testClearAll()
    {
        $cache = $this->getCache();

        $object = new TestValueObject();

        $cache->store('some-key', $object);
        $cache->store('other-key', $object);

        $cache->clearAll();

        $this->assertNull($cache->restore('some-key'));
        $this->assertNull($cache->restore('other-key'));
    }

    protected function getCache()
    {
        return new ObjectCache();
    }
}
