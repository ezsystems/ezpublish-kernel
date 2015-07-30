<?php

/**
 * File containing the HttpCachePassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
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

    public function testProcess()
    {
        $this->setDefinition('ezpublish.http_cache.cache_manager', new Definition('foo', array(true)));
        $varnishProxyClient = new Definition();
        $this->setDefinition('fos_http_cache.proxy_client.varnish', $varnishProxyClient);
        $this->compile();

        $factoryArray = $varnishProxyClient->getFactory();
        $this->assertInstanceOf('Symfony\\Component\\DependencyInjection\\Reference', $factoryArray[0]);
        $this->assertEquals('buildProxyClient', $factoryArray[1]);
        $this->assertEquals('ezpublish.http_cache.proxy_client.varnish.factory', $factoryArray[0]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.http_cache.cache_manager',
            0,
            new Reference('fos_http_cache.proxy_client.varnish')
        );
    }
}
