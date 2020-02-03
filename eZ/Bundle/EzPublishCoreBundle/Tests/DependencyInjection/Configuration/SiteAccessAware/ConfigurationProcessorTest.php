<?php

/**
 * File containing the ConfigurationProcessorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\SiteAccessAware;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationMapperInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\HookableConfigurationMapperInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use stdClass;

class ConfigurationProcessorTest extends TestCase
{
    public function testConstruct()
    {
        $namespace = 'ez_test';
        $siteAccessNodeName = 'foo';
        $container = $this->getContainerMock();
        $siteAccessList = ['test', 'bar'];
        $groupsBySa = ['test' => ['group1', 'group2'], 'bar' => ['group1', 'group3']];
        $siteAccessGroups = [
            'group1' => ['test', 'bar'],
            'group2' => ['test'],
            'group3' => ['bar'],
        ];
        ConfigurationProcessor::setAvailableSiteAccesses($siteAccessList);
        ConfigurationProcessor::setGroupsBySiteAccess($groupsBySa);
        ConfigurationProcessor::setAvailableSiteAccessGroups($siteAccessGroups);
        $processor = new ConfigurationProcessor($container, $namespace, $siteAccessNodeName);

        $contextualizer = $processor->getContextualizer();
        $this->assertInstanceOf(ContextualizerInterface::class, $contextualizer);
        $this->assertSame($container, $contextualizer->getContainer());
        $this->assertSame($namespace, $contextualizer->getNamespace());
        $this->assertSame($siteAccessNodeName, $contextualizer->getSiteAccessNodeName());
        $this->assertSame($siteAccessList, $contextualizer->getAvailableSiteAccesses());
        $this->assertSame($groupsBySa, $contextualizer->getGroupsBySiteAccess());
    }

    public function testGetSetContextualizer()
    {
        $namespace = 'ez_test';
        $siteAccessNodeName = 'foo';
        $container = $this->getContainerMock();
        $processor = new ConfigurationProcessor($container, $namespace, $siteAccessNodeName);

        $this->assertInstanceOf(
            ContextualizerInterface::class,
            $processor->getContextualizer()
        );

        $newContextualizer = $this->getContextualizerMock();
        $processor->setContextualizer($newContextualizer);
        $this->assertSame($newContextualizer, $processor->getContextualizer());
    }

    public function testMapConfigWrongMapper()
    {
        $this->expectException(\InvalidArgumentException::class);

        $namespace = 'ez_test';
        $siteAccessNodeName = 'foo';
        $container = $this->getContainerMock();
        $processor = new ConfigurationProcessor($container, $namespace, $siteAccessNodeName);

        $processor->mapConfig([], new stdClass());
    }

    public function testMapConfigClosure()
    {
        $namespace = 'ez_test';
        $saNodeName = 'foo';
        $container = $this->getContainerMock();
        $processor = new ConfigurationProcessor($container, $namespace, $saNodeName);
        $expectedContextualizer = $processor->getContextualizer();

        $sa1Name = 'sa1';
        $sa2Name = 'sa2';
        $availableSAs = [$sa1Name => true, $sa2Name => true];
        $sa1Config = [
            'foo' => 'bar',
            'hello' => 'world',
            'an_integer' => 123,
            'a_bool' => true,
        ];
        $sa2Config = [
            'foo' => 'bar2',
            'hello' => 'universe',
            'an_integer' => 456,
            'a_bool' => false,
        ];
        $config = [
            'not_sa_aware' => 'blabla',
            $saNodeName => [
                'sa1' => $sa1Config,
                'sa2' => $sa2Config,
            ],
        ];

        $mapperClosure = function (array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer) use ($config, $availableSAs, $saNodeName, $expectedContextualizer) {
            self::assertTrue(isset($availableSAs[$currentScope]));
            self::assertTrue(isset($config[$saNodeName][$currentScope]));
            self::assertSame($config[$saNodeName][$currentScope], $scopeSettings);
            self::assertSame($expectedContextualizer, $contextualizer);
        };
        $processor->mapConfig($config, $mapperClosure);
    }

    public function testMapConfigMapperObject()
    {
        $namespace = 'ez_test';
        $saNodeName = 'foo';
        $container = $this->getContainerMock();
        $processor = new ConfigurationProcessor($container, $namespace, $saNodeName);
        $contextualizer = $processor->getContextualizer();

        $sa1Name = 'sa1';
        $sa2Name = 'sa2';
        $sa1Config = [
            'foo' => 'bar',
            'hello' => 'world',
            'an_integer' => 123,
            'a_bool' => true,
        ];
        $sa2Config = [
            'foo' => 'bar2',
            'hello' => 'universe',
            'an_integer' => 456,
            'a_bool' => false,
        ];
        $config = [
            'not_sa_aware' => 'blabla',
            $saNodeName => [
                'sa1' => $sa1Config,
                'sa2' => $sa2Config,
            ],
        ];

        $mapper = $this->createMock(ConfigurationMapperInterface::class);
        $mapper
            ->expects($this->exactly(count($config[$saNodeName])))
            ->method('mapConfig')
            ->will(
                $this->returnValueMap(
                    [
                        [$sa1Config, $sa1Name, $contextualizer, null],
                        [$sa2Config, $sa2Name, $contextualizer, null],
                    ]
                )
            );

        $processor->mapConfig($config, $mapper);
    }

    public function testMapConfigHookableMapperObject()
    {
        $namespace = 'ez_test';
        $saNodeName = 'foo';
        $container = $this->getContainerMock();
        $processor = new ConfigurationProcessor($container, $namespace, $saNodeName);
        $contextualizer = $processor->getContextualizer();

        $sa1Name = 'sa1';
        $sa2Name = 'sa2';
        $sa1Config = [
            'foo' => 'bar',
            'hello' => 'world',
            'an_integer' => 123,
            'a_bool' => true,
        ];
        $sa2Config = [
            'foo' => 'bar2',
            'hello' => 'universe',
            'an_integer' => 456,
            'a_bool' => false,
        ];
        $config = [
            'not_sa_aware' => 'blabla',
            $saNodeName => [
                'sa1' => $sa1Config,
                'sa2' => $sa2Config,
            ],
        ];

        $mapper = $this->createMock(HookableConfigurationMapperInterface::class);
        $mapper
            ->expects($this->once())
            ->method('preMap')
            ->with($config, $contextualizer);
        $mapper
            ->expects($this->once())
            ->method('postMap')
            ->with($config, $contextualizer);
        $mapper
            ->expects($this->exactly(count($config[$saNodeName])))
            ->method('mapConfig')
            ->will(
                $this->returnValueMap(
                    [
                        [$sa1Config, $sa1Name, $contextualizer, null],
                        [$sa2Config, $sa2Name, $contextualizer, null],
                    ]
                )
            );

        $processor->mapConfig($config, $mapper);
    }

    public function testMapSetting()
    {
        $namespace = 'ez_test';
        $saNodeName = 'foo';
        $container = $this->getContainerMock();
        $processor = new ConfigurationProcessor($container, $namespace, $saNodeName);
        $contextualizer = $this->getContextualizerMock();
        $processor->setContextualizer($contextualizer);

        $sa1Config = [
            'foo' => 'bar',
            'hello' => 'world',
            'an_integer' => 123,
            'a_bool' => true,
        ];
        $sa2Config = [
            'foo' => 'bar2',
            'hello' => 'universe',
            'an_integer' => 456,
            'a_bool' => false,
        ];
        $config = [
            'not_sa_aware' => 'blabla',
            $saNodeName => [
                'sa1' => $sa1Config,
                'sa2' => $sa2Config,
            ],
        ];

        $contextualizer
            ->expects($this->once())
            ->method('mapSetting')
            ->with('foo', $config);
        $processor->mapSetting('foo', $config);
    }

    public function testMapConfigArray()
    {
        $namespace = 'ez_test';
        $saNodeName = 'foo';
        $container = $this->getContainerMock();
        $processor = new ConfigurationProcessor($container, $namespace, $saNodeName);
        $contextualizer = $this->getContextualizerMock();
        $processor->setContextualizer($contextualizer);

        $sa1Config = [
            'foo' => 'bar',
            'hello' => ['world'],
            'an_integer' => 123,
            'a_bool' => true,
        ];
        $sa2Config = [
            'foo' => 'bar2',
            'hello' => ['universe'],
            'an_integer' => 456,
            'a_bool' => false,
        ];
        $config = [
            'not_sa_aware' => 'blabla',
            $saNodeName => [
                'sa1' => $sa1Config,
                'sa2' => $sa2Config,
            ],
        ];

        $contextualizer
            ->expects($this->once())
            ->method('mapConfigArray')
            ->with('hello', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
        $processor->mapConfigArray('hello', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
    }

    protected function getContainerMock()
    {
        return $this->createMock(ContainerInterface::class);
    }

    protected function getContextualizerMock()
    {
        return $this->createMock(ContextualizerInterface::class);
    }
}
