<?php

/**
 * File containing the EnvTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case to verify Integration tests are setup with the right instances.
 */
class EnvTest extends BaseTest
{
    /**
     * Verify Redis is setup if asked for.
     */
    public function testVerifyStashDriver()
    {
        /** @var \Stash\Pool $pool */
        $pool = $this->getSetupFactory()->getServiceContainer()->get('ezpublish.cache_pool');

        if (getenv('CUSTOM_CACHE_POOL') === 'singleredis') {
            $this->assertInstanceOf('\Stash\Driver\Redis', $pool->getDriver());
        } else {
            $this->assertInstanceOf('\Stash\Driver\Ephemeral', $pool->getDriver());
        }
    }
}
