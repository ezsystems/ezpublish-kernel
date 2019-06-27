<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\Tests\Adapter;

use eZ\Publish\Core\Persistence\Cache\Adapter\InMemoryClearingProxyAdapter;
use eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\CacheItem;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Adapter decorator.
 */
class InMemoryClearingProxyAdapterTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Cache\Adapter\InMemoryClearingProxyAdapter */
    protected $cache;

    /** @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $innerPool;

    /** @var \eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache|\PHPUnit\Framework\MockObject\MockObject */
    protected $inMemory;

    /** @var \Closure */
    private $cacheItemsClosure;

    /**
     * Setup the HandlerTest.
     */
    final protected function setUp()
    {
        parent::setUp();

        $this->innerPool = $this->createMock(TagAwareAdapterInterface::class);
        $this->inMemory = $this->createMock(InMemoryCache::class);

        $this->cache = new InMemoryClearingProxyAdapter(
            $this->innerPool,
            [$this->inMemory]
        );

        $this->cacheItemsClosure = \Closure::bind(
            static function ($key, $value, $isHit, $defaultLifetime = 0, $tags = []) {
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
        unset($this->cache);
        unset($this->innerPool);
        unset($this->inMemory);

        parent::tearDown();
    }

    public function testGetItem()
    {
        $item = $this->createCacheItem('first');

        $this->innerPool
            ->expects($this->once())
            ->method('getItem')
            ->with('first')
            ->willReturn($item);

        $this->inMemory->expects($this->never())->method($this->anything());

        $returnedItem = $this->cache->getItem('first');
        $this->assertSame($item, $returnedItem);
    }

    public function testGetItems()
    {
        $items = [
            'first' => $this->createCacheItem('first'),
            'second' => $this->createCacheItem('second'),
        ];

        $this->innerPool
            ->expects($this->once())
            ->method('getItems')
            ->with(['first', 'second'])
            ->willReturn($items);

        $this->inMemory->expects($this->never())->method($this->anything());

        $returnedItems = $this->cache->getItems(['first', 'second']);
        $this->assertSame($items, $returnedItems);
    }

    /**
     * Symfony uses generators with getItems() so we need to make sure we handle that.
     */
    public function testGetItemsWithGenerator()
    {
        $items = [
            'first' => $this->createCacheItem('first'),
            'second' => $this->createCacheItem('second'),
        ];

        $this->innerPool
            ->expects($this->once())
            ->method('getItems')
            ->with(['first', 'second'])
            ->willReturn($this->arrayAsGenerator($items));

        $this->inMemory->expects($this->never())->method($this->anything());

        $returnedItems = iterator_to_array($this->cache->getItems(['first', 'second']));
        $this->assertSame($items, $returnedItems);
    }

    public function testHasItem()
    {
        $this->innerPool
            ->expects($this->once())
            ->method('hasItem')
            ->with('first')
            ->willReturn(true);

        $this->inMemory->expects($this->never())->method($this->anything());

        $this->assertTrue($this->cache->hasItem('first'));
    }

    /**
     * @dataProvider providerForDelete
     */
    public function testDelete(string $method, $argument)
    {
        $this->innerPool
            ->expects($this->once())
            ->method($method)
            ->with($argument)
            ->willReturn(true);

        $this->inMemory
            ->expects($this->once())
            ->method('deleteMulti')
            ->with(is_array($argument) ? $argument : [$argument]);

        // invalidate it
        $this->assertTrue($this->cache->$method($argument));
    }

    public function providerForDelete(): array
    {
        return [
            ['deleteItem', 'first'],
            ['deleteItems', ['first']],
            ['deleteItems', ['first', 'second']],
        ];
    }

    /**
     * Test for clear and invalidateTags as both expects a clear to in-memory as it on purpose does not track tags.
     *
     * @dataProvider providerForClearAndInvalidation
     */
    public function testClearAndInvalidation(string $method, $argument)
    {
        if ($argument) {
            $this->innerPool
                ->expects($this->once())
                ->method($method)
                ->with($argument)
                ->willReturn(true);
        } else {
            $this->innerPool
                ->expects($this->once())
                ->method($method)
                ->willReturn(true);
        }

        $this->inMemory
            ->expects($this->once())
            ->method('clear');

        // invalidate it
        $this->assertTrue($this->cache->$method($argument));
    }

    public function providerForClearAndInvalidation(): array
    {
        return [
            ['invalidateTags', ['my_tag']],
            ['invalidateTags', ['my_tag', 'another_tag']],
            ['clear', null],
        ];
    }

    /**
     * @param string $key
     * @param array $tags Optional.
     * @param mixed $value Optional, if value evaluates to false the cache item will be assumed to be a cache miss here.
     *
     * @return \Symfony\Component\Cache\CacheItem
     */
    private function createCacheItem($key, $tags = [], $value = true)
    {
        $cacheItemsClosure = $this->cacheItemsClosure;

        return $cacheItemsClosure($key, $value, (bool) $value, 0, $tags);
    }

    /**
     * @param array $array
     *
     * @return \Generator
     */
    private function arrayAsGenerator(array $array)
    {
        foreach ($array as $key => $item) {
            yield $key => $item;
        }
    }
}
