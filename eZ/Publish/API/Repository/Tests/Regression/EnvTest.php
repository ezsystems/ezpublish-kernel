<?php

/**
 * File containing the EnvTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\Core\Persistence\Cache\Adapter\TransactionalCacheAdapterDecorator;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Test case to verify Integration tests are setup with the right instances.
 */
class EnvTest extends BaseTest
{
    /**
     * Verify Redis cache is setup if asked for, if not file system.
     */
    public function testVerifyCacheDriver()
    {
        $pool = $this->getSetupFactory()->getServiceContainer()->get('ezpublish.cache_pool');

        $this->assertInstanceOf(TransactionalCacheAdapterDecorator::class, $pool);

        $reflectionDecoratedPool = new \ReflectionProperty($pool, 'innerPool');
        $reflectionDecoratedPool->setAccessible(true);
        $decoratedPool = $reflectionDecoratedPool->getValue($pool);

        $reflectionPool = new \ReflectionProperty($decoratedPool, 'pool');
        $reflectionPool->setAccessible(true);
        $pool = $reflectionPool->getValue($decoratedPool);

        $this->assertInstanceOf(TagAwareAdapter::class, $pool);

        $reflectionPool = new \ReflectionProperty($pool, 'pool');
        $reflectionPool->setAccessible(true);
        $innerPool = $reflectionPool->getValue($pool);

        if (getenv('CUSTOM_CACHE_POOL') === 'singleredis') {
            $this->assertInstanceOf(RedisAdapter::class, $innerPool);
        } else {
            $this->assertInstanceOf(ArrayAdapter::class, $innerPool);
        }
    }
}
