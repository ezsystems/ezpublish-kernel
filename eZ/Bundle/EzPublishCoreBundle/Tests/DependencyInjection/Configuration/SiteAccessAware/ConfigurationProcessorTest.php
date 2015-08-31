<?php

/**
 * File containing the ConfigurationProcessorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\SiteAccessAware;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use PHPUnit_Framework_TestCase;
use stdClass;

class ConfigurationProcessorTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $namespace = 'ez_test';
        $siteAccessNodeName = 'foo';
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $siteAccessList = array('test', 'bar');
        $groupsBySa = array('test' => array('group1', 'group2'), 'bar' => array('group1', 'group3'));
        ConfigurationProcessor::setAvailableSiteAccesses($siteAccessList);
        ConfigurationProcessor::setGroupsBySiteAccess($groupsBySa);
        $processor = new ConfigurationProcessor($container, $namespace, $siteAccessNodeName);

        $contextualizer = $processor->getContextualizer();
        $this->assertInstanceOf(
            'eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface',
            $contextualizer
        );
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
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $processor = new ConfigurationProcessor($container, $namespace, $siteAccessNodeName);

        $this->assertInstanceOf(
            'eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface',
            $processor->getContextualizer()
        );

        $newContextualizer = $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface');
        $processor->setContextualizer($newContextualizer);
        $this->assertSame($newContextualizer, $processor->getContextualizer());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMapConfigWrongMapper()
    {
        $namespace = 'ez_test';
        $siteAccessNodeName = 'foo';
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $processor = new ConfigurationProcessor($container, $namespace, $siteAccessNodeName);

        $processor->mapConfig(array(), new stdClass());
    }

    public function testMapConfigClosure()
    {
        $namespace = 'ez_test';
        $saNodeName = 'foo';
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $processor = new ConfigurationProcessor($container, $namespace, $saNodeName);
        $expectedContextualizer = $processor->getContextualizer();

        $sa1Name = 'sa1';
        $sa2Name = 'sa2';
        $availableSAs = array($sa1Name => true, $sa2Name => true);
        $sa1Config = array(
            'foo' => 'bar',
            'hello' => 'world',
            'an_integer' => 123,
            'a_bool' => true,
        );
        $sa2Config = array(
            'foo' => 'bar2',
            'hello' => 'universe',
            'an_integer' => 456,
            'a_bool' => false,
        );
        $config = array(
            'not_sa_aware' => 'blabla',
            $saNodeName => array(
                'sa1' => $sa1Config,
                'sa2' => $sa2Config,
            ),
        );

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
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $processor = new ConfigurationProcessor($container, $namespace, $saNodeName);
        $contextualizer = $processor->getContextualizer();

        $sa1Name = 'sa1';
        $sa2Name = 'sa2';
        $sa1Config = array(
            'foo' => 'bar',
            'hello' => 'world',
            'an_integer' => 123,
            'a_bool' => true,
        );
        $sa2Config = array(
            'foo' => 'bar2',
            'hello' => 'universe',
            'an_integer' => 456,
            'a_bool' => false,
        );
        $config = array(
            'not_sa_aware' => 'blabla',
            $saNodeName => array(
                'sa1' => $sa1Config,
                'sa2' => $sa2Config,
            ),
        );

        $mapper = $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationMapperInterface');
        $mapper
            ->expects($this->exactly(count($config[$saNodeName])))
            ->method('mapConfig')
            ->will(
                $this->returnValueMap(
                    array(
                        array($sa1Config, $sa1Name, $contextualizer, null),
                        array($sa2Config, $sa2Name, $contextualizer, null),
                    )
                )
            );

        $processor->mapConfig($config, $mapper);
    }

    public function testMapConfigHookableMapperObject()
    {
        $namespace = 'ez_test';
        $saNodeName = 'foo';
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $processor = new ConfigurationProcessor($container, $namespace, $saNodeName);
        $contextualizer = $processor->getContextualizer();

        $sa1Name = 'sa1';
        $sa2Name = 'sa2';
        $sa1Config = array(
            'foo' => 'bar',
            'hello' => 'world',
            'an_integer' => 123,
            'a_bool' => true,
        );
        $sa2Config = array(
            'foo' => 'bar2',
            'hello' => 'universe',
            'an_integer' => 456,
            'a_bool' => false,
        );
        $config = array(
            'not_sa_aware' => 'blabla',
            $saNodeName => array(
                'sa1' => $sa1Config,
                'sa2' => $sa2Config,
            ),
        );

        $mapper = $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\HookableConfigurationMapperInterface');
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
                    array(
                        array($sa1Config, $sa1Name, $contextualizer, null),
                        array($sa2Config, $sa2Name, $contextualizer, null),
                    )
                )
            );

        $processor->mapConfig($config, $mapper);
    }

    public function testMapSetting()
    {
        $namespace = 'ez_test';
        $saNodeName = 'foo';
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $processor = new ConfigurationProcessor($container, $namespace, $saNodeName);
        $contextualizer = $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface');
        $processor->setContextualizer($contextualizer);

        $sa1Config = array(
            'foo' => 'bar',
            'hello' => 'world',
            'an_integer' => 123,
            'a_bool' => true,
        );
        $sa2Config = array(
            'foo' => 'bar2',
            'hello' => 'universe',
            'an_integer' => 456,
            'a_bool' => false,
        );
        $config = array(
            'not_sa_aware' => 'blabla',
            $saNodeName => array(
                'sa1' => $sa1Config,
                'sa2' => $sa2Config,
            ),
        );

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
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $processor = new ConfigurationProcessor($container, $namespace, $saNodeName);
        $contextualizer = $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface');
        $processor->setContextualizer($contextualizer);

        $sa1Config = array(
            'foo' => 'bar',
            'hello' => array('world'),
            'an_integer' => 123,
            'a_bool' => true,
        );
        $sa2Config = array(
            'foo' => 'bar2',
            'hello' => array('universe'),
            'an_integer' => 456,
            'a_bool' => false,
        );
        $config = array(
            'not_sa_aware' => 'blabla',
            $saNodeName => array(
                'sa1' => $sa1Config,
                'sa2' => $sa2Config,
            ),
        );

        $contextualizer
            ->expects($this->once())
            ->method('mapConfigArray')
            ->with('hello', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
        $processor->mapConfigArray('hello', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
    }
}
