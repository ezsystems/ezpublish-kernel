<?php

/**
 * File containing the ConfigResolverCleanupTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Cache\Warmer;

use eZ\Bundle\EzPublishCoreBundle\Cache\Warmer\ConfigResolverCleanup;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ChainConfigResolver;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\Container;

class ConfigResolverCleanupTest extends TestCase
{
    public function testIsOptional()
    {
        self::assertFalse((new ConfigResolverCleanup())->isOptional());
    }

    public function testWarmup()
    {
        $container = new Container();
        $container->set(ChainConfigResolver::class, new stdClass());
        self::assertTrue($container->initialized(ChainConfigResolver::class));

        $warmer = new ConfigResolverCleanup();
        $warmer->setContainer($container);
        $warmer->warmUp('my_cache_dir');

        self::assertFalse($container->initialized(ChainConfigResolver::class));
    }
}
