<?php

/**
 * File containing the ConfigResolverCleanupTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Cache\Warmer;

use eZ\Bundle\EzPublishCoreBundle\Cache\Warmer\ConfigResolverCleanup;
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
        $container->set('ezpublish.config.resolver.core', new stdClass());
        $container->set('ezpublish.config.resolver.chain', new stdClass());
        self::assertTrue($container->initialized('ezpublish.config.resolver.core'));
        self::assertTrue($container->initialized('ezpublish.config.resolver.chain'));

        $warmer = new ConfigResolverCleanup();
        $warmer->setContainer($container);
        $warmer->warmUp('my_cache_dir');

        self::assertFalse($container->initialized('ezpublish.config.resolver.core'));
        self::assertFalse($container->initialized('ezpublish.config.resolver.chain'));
    }
}
