<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\Tests\InMemory;

use eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache;
use PHPUnit\Framework\TestCase;

/**
 * Test case for internal in-memory cache.
 */
class InMemoryCacheTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache */
    protected $cache;

    /**
     * Setup Test.
     */
    final protected function setUp()
    {
        parent::setUp();

        $this->cache = new InMemoryCache(
            3000,
            8
        );
    }

    /**
     * Tear down test (properties).
     */
    final protected function tearDown()
    {
        $this->cache->clear();

        unset($this->cache);
        unset($GLOBALS['override_time']);
        parent::tearDown();
    }

    public function testGetByKey(): void
    {
        $this->assertNull($this->cache->get('first'));

        $obj = new \stdClass();
        $this->cache->setMulti([$obj], static function ($o) { return ['first']; });

        $this->assertSame($obj, $this->cache->get('first'));

        // Test TTL
        $GLOBALS['override_time'] = \microtime(true) + 4;
        $this->assertNull($this->cache->get('first'));
    }

    public function testGetBySecondaryIndex(): void
    {
        $this->assertNull($this->cache->get('first'));
        $this->assertNull($this->cache->get('secondary'));

        $obj = new \stdClass();
        $this->cache->setMulti([$obj], static function ($o) { return ['first', 'secondary']; });

        $this->assertSame($obj, $this->cache->get('first'));
        $this->assertSame($obj, $this->cache->get('secondary'));

        // Test TTL
        $GLOBALS['override_time'] = \microtime(true) + 4;
        $this->assertNull($this->cache->get('first'));
        $this->assertNull($this->cache->get('secondary'));
    }

    public function testGetByList(): void
    {
        $this->assertNull($this->cache->get('first'));
        $this->assertNull($this->cache->get('list'));

        $obj = new \stdClass();
        $this->cache->setMulti([$obj], static function ($o) { return ['first']; }, 'list');

        $this->assertSame($obj, $this->cache->get('first'));
        $this->assertSame([$obj], $this->cache->get('list'));

        // Test TTL
        $GLOBALS['override_time'] = \microtime(true) + 4;
        $this->assertNull($this->cache->get('first'));
        $this->assertNull($this->cache->get('list'));
    }

    public function testDeleted(): void
    {
        $obj = new \stdClass();
        $this->cache->setMulti([$obj], static function ($o) { return ['first', 'second']; }, 'list');

        $this->assertSame($obj, $this->cache->get('first'));
        $this->assertSame($obj, $this->cache->get('second'));
        $this->assertSame([$obj], $this->cache->get('list'));

        // Delete primary, her we expect secondary index to also start returning null
        $this->cache->deleteMulti(['first']);

        $this->assertNull($this->cache->get('first'));
        $this->assertNull($this->cache->get('second'));
        $this->assertSame([$obj], $this->cache->get('list'));

        // Delete list
        $this->cache->deleteMulti(['list']);

        $this->assertNull($this->cache->get('list'));
    }

    public function testClear(): void
    {
        $obj = new \stdClass();
        $this->cache->setMulti([$obj], static function ($o) { return ['first', 'second']; }, 'list');

        $this->assertSame($obj, $this->cache->get('first'));
        $this->assertSame($obj, $this->cache->get('second'));
        $this->assertSame([$obj], $this->cache->get('list'));

        // Clear all cache
        $this->cache->clear();

        $this->assertNull($this->cache->get('first'));
        $this->assertNull($this->cache->get('second'));
        $this->assertNull($this->cache->get('list'));
    }

    public function testSetWhenReachingSetLimit(): void
    {
        $obj = new \stdClass();
        $this->cache->setMulti([$obj, $obj], static function ($o) { return ['first', 'second']; }, 'list');

        $this->assertNull($this->cache->get('first'));
        $this->assertNull($this->cache->get('second'));
        $this->assertNull($this->cache->get('list'));
    }

    public function testSetWhenReachingTotalLimit(): void
    {
        $obj = new \stdClass();
        $this->cache->setMulti([$obj], static function ($o) { return ['first']; });
        $this->cache->setMulti([$obj], static function ($o) { return ['second']; });
        $this->cache->setMulti([$obj], static function ($o) { return ['third']; });
        $this->cache->setMulti([$obj], static function ($o) { return ['fourth']; });
        $this->cache->setMulti([$obj], static function ($o) { return ['fifth']; });
        $this->cache->setMulti([$obj], static function ($o) { return ['sixth']; });
        $this->cache->setMulti([$obj], static function ($o) { return ['seventh']; });
        $this->cache->setMulti([$obj], static function ($o) { return ['eight']; });

        $this->assertNull($this->cache->get('first'));
        $this->assertNull($this->cache->get('second'));
        $this->assertSame($obj, $this->cache->get('third'));
        $this->assertSame($obj, $this->cache->get('fourth'));
        $this->assertSame($obj, $this->cache->get('fifth'));
        $this->assertSame($obj, $this->cache->get('sixth'));
        $this->assertSame($obj, $this->cache->get('seventh'));
        $this->assertSame($obj, $this->cache->get('eight'));
    }

    /**
     * Tests logic behind access counts, making sure least frequently used items are deleted first.
     */
    public function testAccessCountsWhenReachingTotalLimit(): void
    {
        $obj = new \stdClass();
        $this->cache->setMulti([$obj], static function ($o) { return ['first']; });
        $this->cache->setMulti([$obj], static function ($o) { return ['second']; });
        $this->cache->setMulti([$obj], static function ($o) { return ['third']; });
        $this->cache->setMulti([$obj], static function ($o) { return ['fourth']; });

        // Make sure these are read before we set further objects.
        $this->cache->get('first');
        $this->cache->get('third');

        $this->cache->setMulti([$obj], static function ($o) { return ['fifth']; });
        $this->cache->setMulti([$obj], static function ($o) { return ['sixth']; });
        $this->cache->setMulti([$obj], static function ($o) { return ['seventh']; });
        $this->cache->setMulti([$obj], static function ($o) { return ['eight']; });

        $this->assertSame($obj, $this->cache->get('first'));
        $this->assertNull($this->cache->get('second'));
        $this->assertSame($obj, $this->cache->get('third'));
        $this->assertNull($this->cache->get('fourth'));
        $this->assertSame($obj, $this->cache->get('fifth'));
        $this->assertSame($obj, $this->cache->get('sixth'));
        $this->assertSame($obj, $this->cache->get('seventh'));
        $this->assertSame($obj, $this->cache->get('eight'));
    }
}

namespace eZ\Publish\Core\Persistence\Cache\InMemory;

/**
 * Overloads microtime(true) calls in InMemoryCache in order to be able to test expiry.
 *
 * @return float|string
 */
function microtime($asFloat = false)
{
    if ($asFloat & isset($GLOBALS['override_time'])) {
        return $GLOBALS['override_time'];
    }

    return \microtime($asFloat);
}
