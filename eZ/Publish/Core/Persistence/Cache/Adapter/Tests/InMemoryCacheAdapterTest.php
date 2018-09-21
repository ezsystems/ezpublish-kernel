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
    protected $cacheMock;

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

        $this->cacheMock = $this->createMock(TagAwareAdapterInterface::class);

        $this->cache = new InMemoryCacheAdapter(
            $this->cacheMock,
            20,
            3
        );

        $this->cacheItemsClosure = \Closure::bind(
            function ($key, $value, $isHit, $defaultLifetime = 0) {
                $item = new CacheItem();
                $item->key = $key;
                $item->value = $value;
                $item->isHit = $isHit;
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
        unset($this->cacheMock);
        unset($this->cacheItemsClosure);
        parent::tearDown();
    }

    public function testGetItemOnlyCalledOnce()
    {
        $item = $this->getCacheItem('some_key', true);

        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('some_key')
            ->willReturn($item);

        $returnedItem = $this->cache->getItem('some_key');
        $this->assertSame($item, $returnedItem);

        $returnedItem = $this->cache->getItem('some_key');
        $this->assertSame($item, $returnedItem);
    }

    public function testGetItemsOnlyCalledOnce()
    {
        $items = [
            'first_key' => $this->getCacheItem('first_key', true),
            'second_key' => $this->getCacheItem('second_key', true),
        ];

        $this->cacheMock
            ->expects($this->once())
            ->method('getItems')
            ->with(['first_key', 'second_key'])
            ->willReturn($items);

        $returnedItems = $this->cache->getItems(['first_key', 'second_key']);
        $this->assertSame($items, $returnedItems);

        $returnedItems = $this->cache->getItems(['first_key', 'second_key']);
        $this->assertSame($items, $returnedItems);
    }

    /**
     * @param $key
     * @param null $value If null the cache item will be assumed to be a cache miss here.
     * @param int $defaultLifetime
     *
     * @return CacheItem
     */
    final protected function getCacheItem($key, $value = null, $defaultLifetime = 0)
    {
        $cacheItemsClosure = $this->cacheItemsClosure;

        return $cacheItemsClosure($key, $value, (bool)$value, $defaultLifetime);
    }
}
