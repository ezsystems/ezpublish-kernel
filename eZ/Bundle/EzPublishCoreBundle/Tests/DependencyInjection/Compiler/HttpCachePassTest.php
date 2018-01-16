<?php

/**
 * File containing the HttpCachePassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\HttpCachePass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class HttpCachePassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new HttpCachePass());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProcessVarnishProxyNotRegistered()
    {
        $this->setDefinition('ezpublish.http_cache.cache_manager', new Definition());
        $this->compile();
    }

    public function testProcessCacheManager()
    {
        $this->setDefinition('ezpublish.http_cache.cache_manager', new Definition('foo', array(true)));
        $varnishProxyClient = new Definition();
        $this->setDefinition('fos_http_cache.proxy_client.varnish', $varnishProxyClient);
        $this->compile();

        $factoryArray = $varnishProxyClient->getFactory();
        $this->assertInstanceOf(Reference::class, $factoryArray[0]);
        $this->assertEquals('buildProxyClient', $factoryArray[1]);
        $this->assertEquals('ezpublish.http_cache.proxy_client.varnish.factory', $factoryArray[0]);
        $this->assertTrue($varnishProxyClient->isLazy());

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.http_cache.cache_manager',
            0,
            new Reference('fos_http_cache.proxy_client.varnish')
        );
    }

    public function processPurgeClientProvider()
    {
        return [
            ['local', 'ezpublish.http_cache.purge_client.local'],
            ['http', 'ezpublish.http_cache.purge_client.fos'],
        ];
    }

    /**
     * @dataProvider processPurgeClientProvider
     *
     * @param string $paramValue
     * @param string $expectedServiceAlias
     * @param \Symfony\Component\DependencyInjection\Definition|null $customService
     */
    public function testProcessPurgeClient($paramValue, $expectedServiceId, Definition $customService = null)
    {
        $this->setDefinition('ezpublish.http_cache.purge_client', new Definition());
        $this->setParameter('ezpublish.http_cache.purge_type', $paramValue);
        if ($customService) {
            $this->setDefinition($paramValue, $customService);
        }

        $this->compile();

        $this->assertContainerBuilderHasAlias('ezpublish.http_cache.purge_client', $expectedServiceId);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProcessPurgeClientOnInvalidService()
    {
        $this->setDefinition('ezpublish.http_cache.purge_client', new Definition());
        $this->setParameter('ezpublish.http_cache.purge_type', 'foo');

        $this->compile();
    }
}
