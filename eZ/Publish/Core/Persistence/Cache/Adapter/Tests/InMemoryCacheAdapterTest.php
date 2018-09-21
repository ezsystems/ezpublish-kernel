<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\Adapter\Tests;

use eZ\Publish\Core\Persistence\Cache\Adapter\InMemoryCacheAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\CacheItem;
use PHPUnit\Framework\TestCase;

/**
 * Abstract test case for spi cache impl.
 */
class InMemoryCacheAdapterTest extends TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Cache\Adapter\InMemoryCacheAdapter
     */
    protected $cache;

    /**
     * @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $innerCacheMock;

    /**
     * @var \Closure
     */
    private $cacheItemsClosure;

    /**
     * Setup the HandlerTest.
     */
    final protected function setUp()
    {
        parent::setUp();

        $this->innerCacheMock = $this->createMock(TagAwareAdapterInterface::class);

        $this->cache = new InMemoryCacheAdapter(
            $this->innerCacheMock,
            12,
            3
        );

        $this->cacheItemsClosure = \Closure::bind(
            function ($key, $value, $isHit, $defaultLifetime = 0, $tags = []) {
                $item = new CacheItem();
                $item->key = $key;
                $item->value = $value;
                $item->isHit = $isHit;
                $item->prevTags = $tags;
                $item->defaultLifetime = $defaultLifetime;

                return $item;
            },
            null,
            CacheItem::class
        );
    }

    /**
     * Tear down test (properties).
     */
    final protected function tearDown()
    {
        $this->cache->clear();

        unset($this->cache);
        unset($this->innerCacheMock);
        unset($this->cacheItemsClosure);
        unset($GLOBALS['override_time']);
        parent::tearDown();
    }

    public function testGetItemOnlyCalledOnce()
    {
        $item = $this->getCacheItem('first');

        $this->innerCacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('first')
            ->willReturn($item);

        $returnedItem = $this->cache->getItem('first');
        $this->assertSame($item, $returnedItem);

        $returnedItem = $this->cache->getItem('first');
        $this->assertSame($item, $returnedItem);
    }

    /**
     * @depends testGetItemOnlyCalledOnce
     */
    public function testGetItemTTL()
    {
        $item = $this->getCacheItem('first');

        $this->innerCacheMock
            ->expects($this->exactly(2))
            ->method('getItem')
            ->with('first')
            ->willReturn($item);

        $this->cache->getItem('first');

        $GLOBALS['override_time'] = time() + 4;

        $this->cache->getItem('first');
    }

    /**
     * @depends testGetItemOnlyCalledOnce
     */
    public function testGetItemLastRemovedFromMemoryWhenReachingLimit()
    {
        $this->cache = new InMemoryCacheAdapter(
            $this->innerCacheMock,
            6,
            3
        );

        $this->innerCacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('first')
            ->willReturn($this->getCacheItem('first'));

        $this->innerCacheMock
            ->expects($this->at(1))
            ->method('getItem')
            ->with('second')
            ->willReturn($this->getCacheItem('second'));

        $this->innerCacheMock
            ->expects($this->at(2))
            ->method('getItem')
            ->with('third')
            ->willReturn($this->getCacheItem('third'));

        $this->innerCacheMock
            ->expects($this->at(3))
            ->method('getItem')
            ->with('fourth')
            ->willReturn($this->getCacheItem('fourth'));

        $this->innerCacheMock
            ->expects($this->at(4))
            ->method('getItem')
            ->with('fifth')
            ->willReturn($this->getCacheItem('fifth'));

        // At this point cache should start clearing cache from the end of the list
        $this->innerCacheMock
            ->expects($this->at(5))
            ->method('getItem')
            ->with('sixth')
            ->willReturn($this->getCacheItem('sixth'));

        $this->innerCacheMock
            ->expects($this->once())
            ->method('getItems')
            ->with(['fifth'])
            ->willReturn([
                'fifth' => $this->getCacheItem('fifth'),
            ]);

        // On purpose these are called twice, they should not result in cache extra calls
        $this->cache->getItem('first');
        $this->cache->getItem('first');

        $this->cache->getItem('second');
        $this->cache->getItem('second');

        $this->cache->getItem('third');
        $this->cache->getItem('third');

        // Should not result in extra lookups at this point
        iterator_to_array($this->cache->getItems(['first', 'second', 'third']));

        $this->cache->getItem('fourth');
        $this->cache->getItem('fourth');

        $this->cache->getItem('fifth');
        $this->cache->getItem('fifth');

        $this->cache->getItem('sixth');
        $this->cache->getItem('sixth');

        // Should results in lookup for fifth
        iterator_to_array($this->cache->getItems(['first', 'second', 'third', 'fourth', 'fifth', 'sixth']));
    }

    public function testHasItem()
    {
        $this->innerCacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('first')
            ->willReturn($this->getCacheItem('first'));

        $this->innerCacheMock
            ->expects($this->once())
            ->method('hasItem')
            ->with('first')
            ->willReturn(true);

        $this->cache->hasItem('first');

        // populate cache
        $this->cache->getItem('first');

        $this->cache->hasItem('first');
    }

    public function testGetItemsOnlyCalledOnce()
    {
        $items = [
            'first' => $this->getCacheItem('first'),
            'second' => $this->getCacheItem('second'),
        ];

        $this->innerCacheMock
            ->expects($this->once())
            ->method('getItems')
            ->with(['first', 'second'])
            ->willReturn($items);

        $returnedItems = iterator_to_array($this->cache->getItems(['first', 'second']));
        $this->assertSame($items, $returnedItems);

        $returnedItems = iterator_to_array($this->cache->getItems(['first', 'second']));
        $this->assertSame($items, $returnedItems);
    }

    /**
     * Symfony uses generators with getItems() so we need to make sure we handle that.
     */
    public function testGetItemsWithGenerator()
    {
        $items = [
            'first' => $this->getCacheItem('first'),
            'second' => $this->getCacheItem('second'),
        ];

        $this->innerCacheMock
            ->expects($this->once())
            ->method('getItems')
            ->with(['first', 'second'])
            ->willReturn($this->arrayAsGenerator($items));

        $returnedItems = iterator_to_array($this->cache->getItems(['first', 'second']));
        $this->assertSame($items, $returnedItems);

        $returnedItems = iterator_to_array($this->cache->getItems(['first', 'second']));
        $this->assertSame($items, $returnedItems);
    }

    /**
     * @depends testGetItemsOnlyCalledOnce
     */
    public function testGetItemsTTL()
    {
        $items = [
            'first' => $this->getCacheItem('first'),
            'second' => $this->getCacheItem('second'),
        ];

        $this->innerCacheMock
            ->expects($this->exactly(2))
            ->method('getItems')
            ->with(['first', 'second'])
            ->willReturn($items);

        iterator_to_array($this->cache->getItems(['first', 'second']));

        $GLOBALS['override_time'] = time() + 4;

        iterator_to_array($item = $this->cache->getItems(['first', 'second']));
    }

    /**
     * @depends testGetItemsOnlyCalledOnce
     */
    public function testGetItemsNotPlacedInMemoryIfEmpty()
    {
        $items = [];

        $this->innerCacheMock
            ->expects($this->exactly(2))
            ->method('getItems')
            ->with(['first', 'second', 'third'])
            ->willReturn($items);

        iterator_to_array($this->cache->getItems(['first', 'second', 'third']));
        iterator_to_array($this->cache->getItems(['first', 'second', 'third']));
    }

    /**
     * @depends testGetItemsOnlyCalledOnce
     */
    public function testGetItemsNotPlacedInMemoryIfLargerList()
    {
        $items = [
            'first' => $this->getCacheItem('first'),
            'second' => $this->getCacheItem('second'),
            'third' => $this->getCacheItem('third'),
        ];

        $this->innerCacheMock
            ->expects($this->exactly(2))
            ->method('getItems')
            ->with(['first', 'second', 'third'])
            ->willReturn($items);

        iterator_to_array($this->cache->getItems(['first', 'second', 'third']));
        iterator_to_array($this->cache->getItems(['first', 'second', 'third']));
    }

    /**
     * @dataProvider providerForInvalidation
     */
    public function testCacheClearing(string $method, $argument, int $expectedCount)
    {
        $this->innerCacheMock
            ->expects($this->exactly($expectedCount))
            ->method('getItem')
            ->with('first')
            ->willReturn($this->getCacheItem('first', ['my_tag']));

        $this->innerCacheMock
            ->expects($this->never())
            ->method('getItems')
            ->with(['first']);

        // should only lookup once
        $this->cache->getItem('first');
        $this->cache->getItem('first');
        iterator_to_array($this->cache->getItems(['first']));

        // invalidate it
        $this->cache->$method($argument);

        // again, should only lookup once
        $this->cache->getItem('first');
        $this->cache->getItem('first');
        iterator_to_array($this->cache->getItems(['first']));
    }

    public function providerForInvalidation(): array
    {
        return [
            ['deleteItem', 'first', 2],
            ['deleteItems', ['first'], 2],
            ['invalidateTags', ['my_tag'], 2],
            ['clear', null, 2],
            // negative cases
            ['deleteItem', 'second', 1],
            ['deleteItems', ['second'], 1],
            ['invalidateTags', ['some_other_tag'], 1],
        ];
    }

    /**
     * @param $key
     * @param null $value If null the cache item will be assumed to be a cache miss here.
     * @param int $defaultLifetime
     *
     * @return CacheItem
     */
    private function getCacheItem($key, $tags = [], $value = true)
    {
        $cacheItemsClosure = $this->cacheItemsClosure;

        return $cacheItemsClosure($key, $value, (bool) $value, 0, $tags);
    }

    private function arrayAsGenerator(array $array)
    {
        foreach ($array as $key => $item) {
            yield $key => $item;
        }
    }
}

namespace eZ\Publish\Core\Persistence\Cache\Adapter;

/**
 * Overload time call used in InMemoryCacheAdapter in order to be able to test expiry.
 *
 * @return mixed
 */
function time()
{
    if (isset($GLOBALS['override_time'])) {
        return $GLOBALS['override_time'];
    }

    return \time();
}
