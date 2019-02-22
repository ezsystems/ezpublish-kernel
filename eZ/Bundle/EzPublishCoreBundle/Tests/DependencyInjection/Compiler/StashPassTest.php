<?php

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\Cache\Driver\Redis\RedisIgbinary;
use eZ\Bundle\EzPublishCoreBundle\Cache\Driver\Redis\RedisIgbinaryLzf;
use eZ\Bundle\EzPublishCoreBundle\Cache\Driver\Redis\RedisSerializeLzf;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\StashPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Stash\Driver\Redis;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class StashPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new StashPass());
    }

    /**
     * @dataProvider configureRedisProvider
     */
    public function testConfigureRedis($igbinary, $lzf, $expectedClass)
    {
        $this->setDefinition('stash.driver', new Definition());
        $this->setParameter('ezpublish.stash_cache.redis_driver.class', Redis::class);
        $this->setParameter('ezpublish.stash_cache.redis_driver.name', 'Redis');
        $this->setParameter('ezpublish.stash_cache.igbinary', $igbinary);
        $this->setParameter('ezpublish.stash_cache.lzf', $lzf);
        $this->container->prependExtensionConfig('stash',
            [
                'caches' => [
                    'test' => [
                        'drivers' => [
                            'Redis',
                        ],
                    ],
                ],
            ]
        );

        $this->compile();

        $this->assertContainerBuilderHasParameter('ezpublish.stash_cache.redis_driver.class', $expectedClass);
    }

    public function configureRedisProvider()
    {
        return [
            [false, false, Redis::class],
            [true, false, RedisIgbinary::class],
            [false, true, RedisSerializeLzf::class],
            [true, true, RedisIgbinaryLzf::class],
        ];
    }
}
