<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Security\PolicyProvider;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider\PoliciesConfigBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PoliciesConfigBuilderTest extends TestCase
{
    public function testAddConfig()
    {
        $containerBuilder = new ContainerBuilder();
        $configBuilder = new PoliciesConfigBuilder($containerBuilder);
        $config1 = ['foo' => ['bar' => null]];
        $config2 = ['some' => ['thing' => ['limitation']]];
        $expected = [
            'foo' => ['bar' => []],
            'some' => ['thing' => ['limitation' => true]],
        ];
        $configBuilder->addConfig($config1);
        $configBuilder->addConfig($config2);

        self::assertSame($expected, $containerBuilder->getParameter('ezpublish.api.role.policy_map'));
    }

    public function testAddResource()
    {
        $containerBuilder = new ContainerBuilder();
        $configBuilder = new PoliciesConfigBuilder($containerBuilder);
        $resource1 = new FileResource(__FILE__);
        $resource2 = new DirectoryResource(__DIR__);
        $configBuilder->addResource($resource1);
        $configBuilder->addResource($resource2);

        self::assertSame([$resource1, $resource2], $containerBuilder->getResources());
    }
}
