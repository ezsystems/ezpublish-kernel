<?php

/**
 * File containing the ContextualizerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\SiteAccessAware;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Contextualizer;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContextualizerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    private $namespace = 'ez_test';

    private $saNodeName = 'heyho';

    private $availableSAs = array('sa1', 'sa2', 'sa3');

    private $groupsBySA = array(
        'sa1' => array('sa_group1', 'sa_group2'),
        'sa2' => array('sa_group1'),
        'sa3' => array('sa_group1'),
    );

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Contextualizer
     */
    private $contextualizer;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->contextualizer = new Contextualizer($this->container, $this->namespace, $this->saNodeName, $this->availableSAs, $this->groupsBySA);
    }

    /**
     * @dataProvider setContextualParameterProvider
     */
    public function testSetContextualParameter($parameterName, $scope, $value)
    {
        $this->container
            ->expects($this->once())
            ->method('setParameter')
            ->with("$this->namespace.$scope.$parameterName", $value);

        $this->contextualizer->setContextualParameter($parameterName, $scope, $value);
    }

    public function setContextualParameterProvider()
    {
        return array(
            array('my_parameter', 'sa1', 'foobar'),
            array('some', 'sa1', 'thing'),
            array('my_array', 'sa3', array('foo', 'bar')),
            array('my_hash', 'sa2', array('foo' => 'bar', 'hey' => array('ho'), 'enabled' => true)),
            array('my_integer', 'sa3', 123),
            array('my_bool', 'sa2', false),
        );
    }

    public function testMapSetting()
    {
        $fooSa1 = 'bar';
        $planetsSa1 = array('Earth');
        $intSa1 = 123;
        $boolSa1 = true;
        $sa1Config = array(
            'foo' => $fooSa1,
            'planets' => $planetsSa1,
            'an_integer' => $intSa1,
            'a_bool' => $boolSa1,
        );
        $fooSa2 = 'bar2';
        $planetsSa2 = array('Earth', 'Mars', 'Venus');
        $intSa2 = 456;
        $boolSa2 = false;
        $sa2Config = array(
            'foo' => $fooSa2,
            'planets' => $planetsSa2,
            'an_integer' => $intSa2,
            'a_bool' => $boolSa2,
        );
        $config = array(
            'not_sa_aware' => 'blabla',
            $this->saNodeName => array(
                'sa1' => $sa1Config,
                'sa2' => $sa2Config,
            ),
        );

        $container = new ContainerBuilder();
        $this->contextualizer->setContainer($container);
        $this->contextualizer->mapSetting('foo', $config);
        $this->contextualizer->mapSetting('planets', $config);
        $this->contextualizer->mapSetting('an_integer', $config);
        $this->contextualizer->mapSetting('a_bool', $config);

        $this->assertSame($fooSa1, $container->getParameter("$this->namespace.sa1.foo"));
        $this->assertSame($planetsSa1, $container->getParameter("$this->namespace.sa1.planets"));
        $this->assertSame($intSa1, $container->getParameter("$this->namespace.sa1.an_integer"));
        $this->assertSame($boolSa1, $container->getParameter("$this->namespace.sa1.a_bool"));

        $this->assertSame($fooSa2, $container->getParameter("$this->namespace.sa2.foo"));
        $this->assertSame($planetsSa2, $container->getParameter("$this->namespace.sa2.planets"));
        $this->assertSame($intSa2, $container->getParameter("$this->namespace.sa2.an_integer"));
        $this->assertSame($boolSa2, $container->getParameter("$this->namespace.sa2.a_bool"));
    }

    public function testMapConfigArray()
    {
        $containerBuilder = new ContainerBuilder();
        $this->contextualizer->setContainer($containerBuilder);
        $defaultConfig = array(
            'foo' => null,
            'some' => null,
            'planets' => array('Earth'),
            'an_integer' => 0,
            'enabled' => false,
            'j_adore' => 'les_sushis',
        );

        $config = array(
            $this->saNodeName => array(
                'default' => array(
                    'foo_setting' => $defaultConfig,
                ),
                'sa_group1' => array(
                    'foo_setting' => array(
                        'foo' => 'bar',
                        'some' => 'thing',
                        'an_integer' => 123,
                    ),
                ),
                'sa1' => array(
                    'foo_setting' => array(
                        'an_integer' => 456,
                        'enabled' => true,
                        'j_adore' => 'le_saucisson',
                    ),
                ),
                'sa2' => array(
                    'foo_setting' => array(
                        'foo' => 'baz',
                        'planets' => array('Mars', 'Venus'),
                        'an_integer' => 789,
                    ),
                ),
                'global' => array(
                    'foo_setting' => array(
                        'j_adore' => 'la_truite_a_la_vapeur',
                    ),
                ),
            ),
        );

        $expectedMergedSettings = array(
            'sa1' => array(
                'foo' => 'bar',
                'some' => 'thing',
                'planets' => array('Earth'),
                'an_integer' => 456,
                'enabled' => true,
                'j_adore' => 'la_truite_a_la_vapeur',
            ),
            'sa2' => array(
                'foo' => 'baz',
                'some' => 'thing',
                'planets' => array('Mars', 'Venus'),
                'an_integer' => 789,
                'enabled' => false,
                'j_adore' => 'la_truite_a_la_vapeur',
            ),
            'sa3' => array(
                'foo' => 'bar',
                'some' => 'thing',
                'planets' => array('Earth'),
                'an_integer' => 123,
                'enabled' => false,
                'j_adore' => 'la_truite_a_la_vapeur',
            ),
        );

        $this->contextualizer->mapConfigArray('foo_setting', $config);

        $this->assertSame(
            $expectedMergedSettings['sa1'],
            $containerBuilder->getParameter("$this->namespace.sa1.foo_setting")
        );
        $this->assertSame(
            $expectedMergedSettings['sa2'],
            $containerBuilder->getParameter("$this->namespace.sa2.foo_setting")
        );
        $this->assertSame(
            $expectedMergedSettings['sa3'],
            $containerBuilder->getParameter("$this->namespace.sa3.foo_setting")
        );
    }

    public function testMapConfigArraySecondLevel()
    {
        $containerBuilder = new ContainerBuilder();
        $this->contextualizer->setContainer($containerBuilder);
        $defaultConfig = array(
            'foo' => null,
            'some' => null,
            'planets' => array('Earth'),
            'an_integer' => 0,
            'enabled' => false,
            'j_adore' => array('les_sushis'),
        );

        $config = array(
            $this->saNodeName => array(
                'default' => array(
                    'foo_setting' => $defaultConfig,
                ),
                'sa_group1' => array(
                    'foo_setting' => array(
                        'foo' => 'bar',
                        'some' => 'thing',
                        'an_integer' => 123,
                    ),
                ),
                'sa1' => array(
                    'foo_setting' => array(
                        'an_integer' => 456,
                        'enabled' => true,
                        'j_adore' => array('le_saucisson'),
                    ),
                ),
                'sa2' => array(
                    'foo_setting' => array(
                        'foo' => 'baz',
                        'planets' => array('Mars', 'Venus'),
                        'an_integer' => 789,
                    ),
                ),
                'sa3' => array(
                    'foo_setting' => array(
                        'planets' => array('Earth', 'Jupiter'),
                    ),
                ),
                'global' => array(
                    'foo_setting' => array(
                        'j_adore' => array('la_truite_a_la_vapeur'),
                    ),
                ),
            ),
        );

        $expectedMergedSettings = array(
            'sa1' => array(
                'foo' => 'bar',
                'some' => 'thing',
                'planets' => array('Earth'),
                'an_integer' => 456,
                'enabled' => true,
                'j_adore' => array('les_sushis', 'le_saucisson', 'la_truite_a_la_vapeur'),
            ),
            'sa2' => array(
                'foo' => 'baz',
                'some' => 'thing',
                'planets' => array('Earth', 'Mars', 'Venus'),
                'an_integer' => 789,
                'enabled' => false,
                'j_adore' => array('les_sushis', 'la_truite_a_la_vapeur'),
            ),
            'sa3' => array(
                'foo' => 'bar',
                'some' => 'thing',
                'planets' => array('Earth', 'Earth', 'Jupiter'),
                'an_integer' => 123,
                'enabled' => false,
                'j_adore' => array('les_sushis', 'la_truite_a_la_vapeur'),
            ),
        );

        $this->contextualizer->mapConfigArray('foo_setting', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);

        $this->assertSame(
            $expectedMergedSettings['sa1'],
            $containerBuilder->getParameter("$this->namespace.sa1.foo_setting")
        );
        $this->assertSame(
            $expectedMergedSettings['sa2'],
            $containerBuilder->getParameter("$this->namespace.sa2.foo_setting")
        );
        $this->assertSame(
            $expectedMergedSettings['sa3'],
            $containerBuilder->getParameter("$this->namespace.sa3.foo_setting")
        );
    }

    public function testMapConfigArrayUnique()
    {
        $containerBuilder = new ContainerBuilder();
        $this->contextualizer->setContainer($containerBuilder);
        $defaultConfig = array('Earth');

        $config = array(
            $this->saNodeName => array(
                'default' => array(
                    'foo_setting' => $defaultConfig,
                ),
                'sa_group1' => array(
                    'foo_setting' => array('Mars'),
                ),
                'sa1' => array(
                    'foo_setting' => array('Earth'),
                ),
                'sa2' => array(
                    'foo_setting' => array('Mars', 'Venus'),
                ),
                'sa3' => array(
                    'foo_setting' => array('Earth', 'Jupiter'),
                ),
            ),
        );

        $expectedMergedSettings = array(
            'sa1' => array('Earth', 'Mars'),
            'sa2' => array('Earth', 'Mars', 'Venus'),
            'sa3' => array('Earth', 'Mars', 'Jupiter'),
        );

        $this->contextualizer->mapConfigArray('foo_setting', $config, ContextualizerInterface::UNIQUE);

        $this->assertSame(
            $expectedMergedSettings['sa1'],
            $containerBuilder->getParameter("$this->namespace.sa1.foo_setting")
        );
        $this->assertSame(
            $expectedMergedSettings['sa2'],
            $containerBuilder->getParameter("$this->namespace.sa2.foo_setting")
        );
        $this->assertSame(
            $expectedMergedSettings['sa3'],
            $containerBuilder->getParameter("$this->namespace.sa3.foo_setting")
        );
    }

    public function testGetSetContainer()
    {
        $this->assertSame($this->container, $this->contextualizer->getContainer());
        $containerBuilder = new ContainerBuilder();
        $this->contextualizer->setContainer($containerBuilder);
        $this->assertSame($containerBuilder, $this->contextualizer->getContainer());
    }

    public function testGetSetSANodeName()
    {
        $nodeName = 'foobarbaz';
        $this->assertSame($this->saNodeName, $this->contextualizer->getSiteAccessNodeName());
        $this->contextualizer->setSiteAccessNodeName($nodeName);
        $this->assertSame($nodeName, $this->contextualizer->getSiteAccessNodeName());
    }

    public function testGetSetNamespace()
    {
        $ns = 'ezpublish';
        $this->assertSame($this->namespace, $this->contextualizer->getNamespace());
        $this->contextualizer->setNamespace($ns);
        $this->assertSame($ns, $this->contextualizer->getNamespace());
    }

    public function testGetSetAvailableSiteAccesses()
    {
        $this->assertSame($this->availableSAs, $this->contextualizer->getAvailableSiteAccesses());
        $sa = array('foo', 'bar', 'baz');
        $this->contextualizer->setAvailableSiteAccesses($sa);
        $this->assertSame($sa, $this->contextualizer->getAvailableSiteAccesses());
    }

    public function testGetSetGroupsBySA()
    {
        $this->assertSame($this->groupsBySA, $this->contextualizer->getGroupsBySiteAccess());
        $groups = array('foo' => array('bar', 'baz'), 'group2' => array('some', 'thing'));
        $this->contextualizer->setGroupsBySiteAccess($groups);
        $this->assertSame($groups, $this->contextualizer->getGroupsBySiteAccess());
    }

    /**
     * Test that settings array a properly merged when defined in several
     * scopes.
     *
     * @dataProvider fullMapConfigArrayProvider
     */
    public function testFullMapConfigArray(
        $testId,
        $siteaccess,
        array $groups,
        array $defaultValue,
        array $globalValue,
        array $config,
        $options,
        array $expected,
        $customSANodeKey = null
    ) {
        $this->contextualizer->setAvailableSiteAccesses($config['siteaccess']['list']);
        $this->contextualizer->setGroupsBySiteAccess(array($siteaccess => $groups));

        $hasParameterMap = array(
            array(
                $this->namespace . '.' . ConfigResolver::SCOPE_DEFAULT . '.' . $testId,
                true,
            ),
            array(
                $this->namespace . '.' . ConfigResolver::SCOPE_GLOBAL . '.' . $testId,
                true,
            ),
        );

        $getParameterMap = array(
            array(
                $this->namespace . '.' . ConfigResolver::SCOPE_DEFAULT . '.' . $testId,
                $defaultValue,
            ),
            array(
                $this->namespace . '.' . ConfigResolver::SCOPE_GLOBAL . '.' . $testId,
                $globalValue,
            ),
        );

        $this->container
            ->expects($this->any())
            ->method('hasParameter')
            ->will($this->returnValueMap($hasParameterMap));

        $this->container
            ->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValueMap($getParameterMap));

        $this->container
            ->expects($this->any())
            ->method('setParameter')
            ->with(
                $this->equalTo("$this->namespace.$siteaccess.$testId"),
                $this->equalTo($expected)
            );

        if ($customSANodeKey !== null) {
            $this->contextualizer->setSiteAccessNodeName($customSANodeKey);
        }
        $this->contextualizer->mapConfigArray($testId, $config, $options);
    }

    public function fullMapConfigArrayProvider()
    {
        $testId = 'wizards';
        $siteaccess = 'krondor';
        $group1 = 'midkemia';
        $group2 = 'triagia';
        $all = array('Kulgan', 'Macros the Black', 'Pug', 'Rogen', 'William');
        $siteaccessConfig = array(
            'list' => array($siteaccess),
            'groups' => array(
                $group1 => array($siteaccess),
                $group2 => array($siteaccess),
            ),
        );
        $testIdHash = 'location_view';
        $locationView1 = array(
            'full' => array(
                'Wizard' => array(
                    'template' => 'wizard.html.twig',
                ),
                'Sorcerer' => array(
                    'template' => 'sorcerer.html.twig',
                ),
            ),
        );

        $locationView2 = array(
            'full' => array(
                'Dwarve' => array(
                    'template' => 'dwarve.html.twig',
                ),
                'Sorcerer' => array(
                    'template' => 'sorcerer2.html.twig',
                ),
            ),
        );

        $locationView3 = array(
            'full' => array(
                'Moredhel' => array(
                    'template' => 'moredhel.html.twig',
                ),
                'Sorcerer' => array(
                    'template' => 'sorcerer3.html.twig',
                ),
            ),
        );
        $locationView4 = array(
            'full' => array(
                'Moredhel' => array(
                    'template' => 'moredhel2.html.twig',
                ),
                'Warrior' => array(
                    'template' => 'warrior.html.twig',
                ),
            ),
        );

        $locationView12 = array(
            'full' => array(
                'Wizard' => array(
                    'template' => 'wizard.html.twig',
                ),
                'Sorcerer' => array(
                    'template' => 'sorcerer2.html.twig',
                ),
                'Dwarve' => array(
                    'template' => 'dwarve.html.twig',
                ),
            ),
        );

        $locationView123 = array(
            'full' => array(
                'Wizard' => array(
                    'template' => 'wizard.html.twig',
                ),
                'Sorcerer' => array(
                    'template' => 'sorcerer3.html.twig',
                ),
                'Dwarve' => array(
                    'template' => 'dwarve.html.twig',
                ),
                'Moredhel' => array(
                    'template' => 'moredhel.html.twig',
                ),
            ),
        );

        $locationView1234 = array(
            'full' => array(
                'Wizard' => array(
                    'template' => 'wizard.html.twig',
                ),
                'Sorcerer' => array(
                    'template' => 'sorcerer3.html.twig',
                ),
                'Dwarve' => array(
                    'template' => 'dwarve.html.twig',
                ),
                'Moredhel' => array(
                    'template' => 'moredhel2.html.twig',
                ),
                'Warrior' => array(
                    'template' => 'warrior.html.twig',
                ),
            ),
        );

        $locationView21 = array(
            'full' => array(
                'Dwarve' => array(
                    'template' => 'dwarve.html.twig',
                ),
                'Sorcerer' => array(
                    'template' => 'sorcerer.html.twig',
                ),
                'Wizard' => array(
                    'template' => 'wizard.html.twig',
                ),
            ),
        );

        $cases = array(
            //
            // MERGING TESTS ON NORMAL ARRAY
            //
            array(
                // everything in default scope
                $testId,
                $siteaccess,
                array($group1, $group2),
                $all,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(),
                ),
                0,
                $all,
            ),
            array(
                // everything in global scope
                $testId,
                $siteaccess,
                array($group1, $group2),
                array(),
                $all,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(),
                ),
                0,
                $all,
            ),
            array(
                // everything in a group
                $testId,
                $siteaccess,
                array($group1, $group2),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group2 => array($testId => $all),
                    ),
                ),
                0,
                $all,
            ),
            array(
                // everything in a siteaccess
                $testId,
                $siteaccess,
                array($group1, $group2),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $siteaccess => array($testId => $all),
                    ),
                ),
                0,
                $all,
            ),
            array(
                // default scope + one group
                $testId,
                $siteaccess,
                array($group1, $group2),
                array('Kulgan', 'Macros the Black'),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testId => array('Pug', 'Rogen', 'William')),
                    ),
                ),
                0,
                $all,
            ),
            array(
                // one group + global scope
                $testId,
                $siteaccess,
                array($group1, $group2),
                array(),
                array('Pug', 'Rogen', 'William'),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testId => array('Kulgan', 'Macros the Black')),
                    ),
                ),
                0,
                $all,
            ),
            array(
                // default scope + two groups
                $testId,
                $siteaccess,
                array($group1, $group2),
                array('Kulgan', 'Macros the Black'),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testId => array('Pug', 'Rogen')),
                        $group2 => array($testId => array('William')),
                    ),
                ),
                0,
                $all,
            ),
            array(
                // two groups + global scope
                $testId,
                $siteaccess,
                array($group1, $group2),
                array(),
                array('Kulgan', 'Macros the Black'),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testId => array('Pug', 'Rogen')),
                        $group2 => array($testId => array('William')),
                    ),
                ),
                0,
                array('Pug', 'Rogen', 'William', 'Kulgan', 'Macros the Black'),
            ),
            array(
                // default scope + two groups + siteaccess
                $testId,
                $siteaccess,
                array($group1, $group2),
                array('Kulgan', 'Macros the Black'),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testId => array('Pug')),
                        $group2 => array($testId => array('Rogen')),
                        $siteaccess => array($testId => array('William')),
                    ),
                ),
                0,
                $all,
            ),
            array(
                // global scope + two groups + siteaccess
                $testId,
                $siteaccess,
                array($group1, $group2),
                array(),
                array('Kulgan', 'Macros the Black'),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testId => array('Pug')),
                        $group2 => array($testId => array('Rogen')),
                        $siteaccess => array($testId => array('William')),
                    ),
                ),
                0,
                array('Pug', 'Rogen', 'William', 'Kulgan', 'Macros the Black'),
            ),
            array(
                // default scope + two groups +  global
                $testId,
                $siteaccess,
                array($group1, $group2),
                array('Kulgan', 'Macros the Black'),
                array('William'),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testId => array('Pug')),
                        $group2 => array($testId => array('Rogen')),
                    ),
                ),
                0,
                $all,
            ),
            array(
                // default scope + two groups + siteaccess + global
                $testId,
                $siteaccess,
                array($group1, $group2),
                array('Kulgan'),
                array('William'),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testId => array('Macros the Black')),
                        $group2 => array($testId => array('Pug')),
                        $siteaccess => array($testId => array('Rogen')),
                    ),
                ),
                0,
                $all,
            ),
            //
            // UNIQUE OPTION TESTS (only suitable for normal array)
            //
            array(
                $testId,
                $siteaccess,
                array($group1, $group2),
                array('Kulgan', 'Kulgan'),
                array('William'),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testId => array('Macros the Black')),
                        $group2 => array($testId => array('Pug')),
                        $siteaccess => array($testId => array('Rogen', 'Pug')),
                    ),
                ),
                ContextualizerInterface::UNIQUE,
                array('Kulgan', 'Macros the Black', 'Pug', 'Rogen', 'William'),
            ),
            array(
                $testId,
                $siteaccess,
                array($group1, $group2),
                array('Kulgan', 'Kulgan'),
                array('William', 'Kulgan', 'Pug'),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testId => array('Macros the Black')),
                        $group2 => array($testId => array('Pug', 'William', 'Kulgan')),
                        $siteaccess => array($testId => array('Rogen', 'Pug', 'Rogen', 'Macros the Black')),
                    ),
                ),
                ContextualizerInterface::UNIQUE,
                array('Kulgan', 'Macros the Black', 'Pug', 'William', 'Rogen'),
            ),
            //
            // MERGING HASH TESTS with MERGE_FROM_SECOND_LEVEL
            //
            array(
                // everything in default scope
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView1,
            ),
            array(
                // everything in global scope
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                $locationView1,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView1,
            ),
            array(
                // everything in a group
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView1),
                    ),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView1,
            ),
            array(
                // everything in a siteaccess
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $siteaccess => array($testIdHash => $locationView1),
                    ),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView1,
            ),

            array(
                // default scope + one group
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView2),
                    ),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView12,
            ),
            array(
                // one group + global scope
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                $locationView1,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView2),
                    ),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView21,
            ),
            array(
                // default scope + two groups
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView2),
                        $group2 => array($testIdHash => $locationView3),
                    ),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ),
            array(
                // default scope + two groups + siteaccess
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView2),
                        $group2 => array($testIdHash => $locationView3),
                        $siteaccess => array($testIdHash => $locationView4),
                    ),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView1234,
            ),
            array(
                // two groups
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView1),
                        $group2 => array($testIdHash => $locationView2),
                    ),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView12,
            ),
            array(
                // two groups + global scope
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                $locationView3,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView1),
                        $group2 => array($testIdHash => $locationView2),
                    ),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ),
            array(
                // two groups + siteaccess + global scope
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                $locationView4,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView1),
                        $group2 => array($testIdHash => $locationView2),
                        $siteaccess => array($testIdHash => $locationView3),
                    ),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView1234,
            ),

            array(
                // two groups + siteaccess
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $siteaccess => array($testIdHash => $locationView3),
                        $group1 => array($testIdHash => $locationView1),
                        $group2 => array($testIdHash => $locationView2),
                    ),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ),
            array(
                // default scope + group + siteaccess
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView2),
                        $siteaccess => array($testIdHash => $locationView3),
                    ),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ),
            array(
                // default scope + group + siteaccess + global scope
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                $locationView1,
                $locationView4,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView2),
                        $siteaccess => array($testIdHash => $locationView3),
                    ),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView1234,
            ),
            array(
                // global scope + group + siteaccess
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                $locationView3,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group2 => array($testIdHash => $locationView1),
                        $siteaccess => array($testIdHash => $locationView2),
                    ),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ),
            array(
                // default scope + group +  global
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                $locationView1,
                $locationView3,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group2 => array($testIdHash => $locationView2),
                    ),
                ),
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ),
            //
            // MERGING HASH TESTS without MERGE_FROM_SECOND_LEVEL, the result
            // is always the "last" defined one
            //
            array(
                // everything in default scope
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(),
                ),
                0,
                $locationView1,
            ),
            array(
                // everything in global scope
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                $locationView1,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(),
                ),
                0,
                $locationView1,
            ),
            array(
                // everything in a group
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView1),
                    ),
                ),
                0,
                $locationView1,
            ),
            array(
                // everything in a siteaccess
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $siteaccess => array($testIdHash => $locationView1),
                    ),
                ),
                0,
                $locationView1,
            ),
            array(
                // default scope + one group
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView2),
                    ),
                ),
                0,
                $locationView2,
            ),
            array(
                // one group + global scope
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                $locationView1,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView2),
                    ),
                ),
                0,
                $locationView1,
            ),
            array(
                // default scope + two groups
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView2),
                        $group2 => array($testIdHash => $locationView3),
                    ),
                ),
                0,
                $locationView3,
            ),
            array(
                // default scope + two groups + siteaccess
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView2),
                        $group2 => array($testIdHash => $locationView3),
                        $siteaccess => array($testIdHash => $locationView4),
                    ),
                ),
                0,
                $locationView4,
            ),
            array(
                // default scope + two groups + global scope
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                $locationView1,
                $locationView4,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView2),
                        $group2 => array($testIdHash => $locationView3),
                    ),
                ),
                0,
                $locationView4,
            ),
            array(
                // two groups
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView1),
                        $group2 => array($testIdHash => $locationView2),
                    ),
                ),
                0,
                $locationView2,
            ),
            array(
                // two groups + global scope
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                $locationView3,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView1),
                        $group2 => array($testIdHash => $locationView2),
                    ),
                ),
                0,
                $locationView3,
            ),
            array(
                // two groups + siteaccess
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $siteaccess => array($testIdHash => $locationView3),
                        $group1 => array($testIdHash => $locationView1),
                        $group2 => array($testIdHash => $locationView2),
                    ),
                ),
                0,
                $locationView3,
            ),
            array(
                // two groups + siteaccess + global scope
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                $locationView4,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $siteaccess => array($testIdHash => $locationView3),
                        $group1 => array($testIdHash => $locationView1),
                        $group2 => array($testIdHash => $locationView2),
                    ),
                ),
                0,
                $locationView4,
            ),
            array(
                // default scope + group + siteaccess
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                $locationView1,
                array(),
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group1 => array($testIdHash => $locationView2),
                        $siteaccess => array($testIdHash => $locationView3),
                    ),
                ),
                0,
                $locationView3,
            ),
            array(
                // global scope + group + siteaccess
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                array(),
                $locationView3,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group2 => array($testIdHash => $locationView1),
                        $siteaccess => array($testIdHash => $locationView2),
                    ),
                ),
                0,
                $locationView3,
            ),
            array(
                // default scope + group +  global
                $testIdHash,
                $siteaccess,
                array($group1, $group2),
                $locationView1,
                $locationView3,
                array(
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => array(
                        $group2 => array($testIdHash => $locationView2),
                    ),
                ),
                0,
                $locationView3,
            ),
        );

        foreach ($cases as $k => $newcase) {
            // run the same tests with another baseKey than the default one
            if (isset($newcase[5][$this->saNodeName])) {
                $newcase[5]['customBaseKey'] = $newcase[5][$this->saNodeName];
                unset($newcase[5][$this->saNodeName]);
                $newcase[] = 'customBaseKey';
                $cases[] = $newcase;
            }
        }

        return $cases;
    }
}
