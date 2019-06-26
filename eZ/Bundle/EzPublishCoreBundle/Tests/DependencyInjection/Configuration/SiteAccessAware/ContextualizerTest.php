<?php

/**
 * File containing the ContextualizerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\SiteAccessAware;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Contextualizer;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContextualizerTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $container;

    private $namespace = 'ez_test';

    private $saNodeName = 'heyho';

    private $availableSAs = ['sa1', 'sa2', 'sa3'];

    private $groupsBySA = [
        'sa1' => ['sa_group1', 'sa_group2'],
        'sa2' => ['sa_group1'],
        'sa3' => ['sa_group1'],
    ];

    /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Contextualizer */
    private $contextualizer;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->createMock(ContainerInterface::class);
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
        return [
            ['my_parameter', 'sa1', 'foobar'],
            ['some', 'sa1', 'thing'],
            ['my_array', 'sa3', ['foo', 'bar']],
            ['my_hash', 'sa2', ['foo' => 'bar', 'hey' => ['ho'], 'enabled' => true]],
            ['my_integer', 'sa3', 123],
            ['my_bool', 'sa2', false],
        ];
    }

    public function testMapSetting()
    {
        $fooSa1 = 'bar';
        $planetsSa1 = ['Earth'];
        $intSa1 = 123;
        $boolSa1 = true;
        $sa1Config = [
            'foo' => $fooSa1,
            'planets' => $planetsSa1,
            'an_integer' => $intSa1,
            'a_bool' => $boolSa1,
        ];
        $fooSa2 = 'bar2';
        $planetsSa2 = ['Earth', 'Mars', 'Venus'];
        $intSa2 = 456;
        $boolSa2 = false;
        $sa2Config = [
            'foo' => $fooSa2,
            'planets' => $planetsSa2,
            'an_integer' => $intSa2,
            'a_bool' => $boolSa2,
        ];
        $config = [
            'not_sa_aware' => 'blabla',
            $this->saNodeName => [
                'sa1' => $sa1Config,
                'sa2' => $sa2Config,
            ],
        ];

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
        $defaultConfig = [
            'foo' => null,
            'some' => null,
            'planets' => ['Earth'],
            'an_integer' => 0,
            'enabled' => false,
            'j_adore' => 'les_sushis',
        ];

        $config = [
            $this->saNodeName => [
                'default' => [
                    'foo_setting' => $defaultConfig,
                ],
                'sa_group1' => [
                    'foo_setting' => [
                        'foo' => 'bar',
                        'some' => 'thing',
                        'an_integer' => 123,
                    ],
                ],
                'sa1' => [
                    'foo_setting' => [
                        'an_integer' => 456,
                        'enabled' => true,
                        'j_adore' => 'le_saucisson',
                    ],
                ],
                'sa2' => [
                    'foo_setting' => [
                        'foo' => 'baz',
                        'planets' => ['Mars', 'Venus'],
                        'an_integer' => 789,
                    ],
                ],
                'global' => [
                    'foo_setting' => [
                        'j_adore' => 'la_truite_a_la_vapeur',
                    ],
                ],
            ],
        ];

        $expectedMergedSettings = [
            'sa1' => [
                'foo' => 'bar',
                'some' => 'thing',
                'planets' => ['Earth'],
                'an_integer' => 456,
                'enabled' => true,
                'j_adore' => 'la_truite_a_la_vapeur',
            ],
            'sa2' => [
                'foo' => 'baz',
                'some' => 'thing',
                'planets' => ['Mars', 'Venus'],
                'an_integer' => 789,
                'enabled' => false,
                'j_adore' => 'la_truite_a_la_vapeur',
            ],
            'sa3' => [
                'foo' => 'bar',
                'some' => 'thing',
                'planets' => ['Earth'],
                'an_integer' => 123,
                'enabled' => false,
                'j_adore' => 'la_truite_a_la_vapeur',
            ],
        ];

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
        $defaultConfig = [
            'foo' => null,
            'some' => null,
            'planets' => ['Earth'],
            'an_integer' => 0,
            'enabled' => false,
            'j_adore' => ['les_sushis'],
        ];

        $config = [
            $this->saNodeName => [
                'default' => [
                    'foo_setting' => $defaultConfig,
                ],
                'sa_group1' => [
                    'foo_setting' => [
                        'foo' => 'bar',
                        'some' => 'thing',
                        'an_integer' => 123,
                    ],
                ],
                'sa1' => [
                    'foo_setting' => [
                        'an_integer' => 456,
                        'enabled' => true,
                        'j_adore' => ['le_saucisson'],
                    ],
                ],
                'sa2' => [
                    'foo_setting' => [
                        'foo' => 'baz',
                        'planets' => ['Mars', 'Venus'],
                        'an_integer' => 789,
                    ],
                ],
                'sa3' => [
                    'foo_setting' => [
                        'planets' => ['Earth', 'Jupiter'],
                    ],
                ],
                'global' => [
                    'foo_setting' => [
                        'j_adore' => ['la_truite_a_la_vapeur'],
                    ],
                ],
            ],
        ];

        $expectedMergedSettings = [
            'sa1' => [
                'foo' => 'bar',
                'some' => 'thing',
                'planets' => ['Earth'],
                'an_integer' => 456,
                'enabled' => true,
                'j_adore' => ['les_sushis', 'le_saucisson', 'la_truite_a_la_vapeur'],
            ],
            'sa2' => [
                'foo' => 'baz',
                'some' => 'thing',
                'planets' => ['Earth', 'Mars', 'Venus'],
                'an_integer' => 789,
                'enabled' => false,
                'j_adore' => ['les_sushis', 'la_truite_a_la_vapeur'],
            ],
            'sa3' => [
                'foo' => 'bar',
                'some' => 'thing',
                'planets' => ['Earth', 'Earth', 'Jupiter'],
                'an_integer' => 123,
                'enabled' => false,
                'j_adore' => ['les_sushis', 'la_truite_a_la_vapeur'],
            ],
        ];

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
        $defaultConfig = ['Earth'];

        $config = [
            $this->saNodeName => [
                'default' => [
                    'foo_setting' => $defaultConfig,
                ],
                'sa_group1' => [
                    'foo_setting' => ['Mars'],
                ],
                'sa1' => [
                    'foo_setting' => ['Earth'],
                ],
                'sa2' => [
                    'foo_setting' => ['Mars', 'Venus'],
                ],
                'sa3' => [
                    'foo_setting' => ['Earth', 'Jupiter'],
                ],
            ],
        ];

        $expectedMergedSettings = [
            'sa1' => ['Earth', 'Mars'],
            'sa2' => ['Earth', 'Mars', 'Venus'],
            'sa3' => ['Earth', 'Mars', 'Jupiter'],
        ];

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
        $sa = ['foo', 'bar', 'baz'];
        $this->contextualizer->setAvailableSiteAccesses($sa);
        $this->assertSame($sa, $this->contextualizer->getAvailableSiteAccesses());
    }

    public function testGetSetGroupsBySA()
    {
        $this->assertSame($this->groupsBySA, $this->contextualizer->getGroupsBySiteAccess());
        $groups = ['foo' => ['bar', 'baz'], 'group2' => ['some', 'thing']];
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
        $this->contextualizer->setGroupsBySiteAccess([$siteaccess => $groups]);

        $hasParameterMap = [
            [
                $this->namespace . '.' . ConfigResolver::SCOPE_DEFAULT . '.' . $testId,
                true,
            ],
            [
                $this->namespace . '.' . ConfigResolver::SCOPE_GLOBAL . '.' . $testId,
                true,
            ],
        ];

        $getParameterMap = [
            [
                $this->namespace . '.' . ConfigResolver::SCOPE_DEFAULT . '.' . $testId,
                $defaultValue,
            ],
            [
                $this->namespace . '.' . ConfigResolver::SCOPE_GLOBAL . '.' . $testId,
                $globalValue,
            ],
        ];

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
        $all = ['Kulgan', 'Macros the Black', 'Pug', 'Rogen', 'William'];
        $siteaccessConfig = [
            'list' => [$siteaccess],
            'groups' => [
                $group1 => [$siteaccess],
                $group2 => [$siteaccess],
            ],
        ];
        $testIdHash = 'location_view';
        $locationView1 = [
            'full' => [
                'Wizard' => [
                    'template' => 'wizard.html.twig',
                ],
                'Sorcerer' => [
                    'template' => 'sorcerer.html.twig',
                ],
            ],
        ];

        $locationView2 = [
            'full' => [
                'Dwarve' => [
                    'template' => 'dwarve.html.twig',
                ],
                'Sorcerer' => [
                    'template' => 'sorcerer2.html.twig',
                ],
            ],
        ];

        $locationView3 = [
            'full' => [
                'Moredhel' => [
                    'template' => 'moredhel.html.twig',
                ],
                'Sorcerer' => [
                    'template' => 'sorcerer3.html.twig',
                ],
            ],
        ];
        $locationView4 = [
            'full' => [
                'Moredhel' => [
                    'template' => 'moredhel2.html.twig',
                ],
                'Warrior' => [
                    'template' => 'warrior.html.twig',
                ],
            ],
        ];

        $locationView12 = [
            'full' => [
                'Wizard' => [
                    'template' => 'wizard.html.twig',
                ],
                'Sorcerer' => [
                    'template' => 'sorcerer2.html.twig',
                ],
                'Dwarve' => [
                    'template' => 'dwarve.html.twig',
                ],
            ],
        ];

        $locationView123 = [
            'full' => [
                'Wizard' => [
                    'template' => 'wizard.html.twig',
                ],
                'Sorcerer' => [
                    'template' => 'sorcerer3.html.twig',
                ],
                'Dwarve' => [
                    'template' => 'dwarve.html.twig',
                ],
                'Moredhel' => [
                    'template' => 'moredhel.html.twig',
                ],
            ],
        ];

        $locationView1234 = [
            'full' => [
                'Wizard' => [
                    'template' => 'wizard.html.twig',
                ],
                'Sorcerer' => [
                    'template' => 'sorcerer3.html.twig',
                ],
                'Dwarve' => [
                    'template' => 'dwarve.html.twig',
                ],
                'Moredhel' => [
                    'template' => 'moredhel2.html.twig',
                ],
                'Warrior' => [
                    'template' => 'warrior.html.twig',
                ],
            ],
        ];

        $locationView21 = [
            'full' => [
                'Dwarve' => [
                    'template' => 'dwarve.html.twig',
                ],
                'Sorcerer' => [
                    'template' => 'sorcerer.html.twig',
                ],
                'Wizard' => [
                    'template' => 'wizard.html.twig',
                ],
            ],
        ];

        $cases = [
            //
            // MERGING TESTS ON NORMAL ARRAY
            //
            [
                // everything in default scope
                $testId,
                $siteaccess,
                [$group1, $group2],
                $all,
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [],
                ],
                0,
                $all,
            ],
            [
                // everything in global scope
                $testId,
                $siteaccess,
                [$group1, $group2],
                [],
                $all,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [],
                ],
                0,
                $all,
            ],
            [
                // everything in a group
                $testId,
                $siteaccess,
                [$group1, $group2],
                [],
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group2 => [$testId => $all],
                    ],
                ],
                0,
                $all,
            ],
            [
                // everything in a siteaccess
                $testId,
                $siteaccess,
                [$group1, $group2],
                [],
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $siteaccess => [$testId => $all],
                    ],
                ],
                0,
                $all,
            ],
            [
                // default scope + one group
                $testId,
                $siteaccess,
                [$group1, $group2],
                ['Kulgan', 'Macros the Black'],
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testId => ['Pug', 'Rogen', 'William']],
                    ],
                ],
                0,
                $all,
            ],
            [
                // one group + global scope
                $testId,
                $siteaccess,
                [$group1, $group2],
                [],
                ['Pug', 'Rogen', 'William'],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testId => ['Kulgan', 'Macros the Black']],
                    ],
                ],
                0,
                $all,
            ],
            [
                // default scope + two groups
                $testId,
                $siteaccess,
                [$group1, $group2],
                ['Kulgan', 'Macros the Black'],
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testId => ['Pug', 'Rogen']],
                        $group2 => [$testId => ['William']],
                    ],
                ],
                0,
                $all,
            ],
            [
                // two groups + global scope
                $testId,
                $siteaccess,
                [$group1, $group2],
                [],
                ['Kulgan', 'Macros the Black'],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testId => ['Pug', 'Rogen']],
                        $group2 => [$testId => ['William']],
                    ],
                ],
                0,
                ['Pug', 'Rogen', 'William', 'Kulgan', 'Macros the Black'],
            ],
            [
                // default scope + two groups + siteaccess
                $testId,
                $siteaccess,
                [$group1, $group2],
                ['Kulgan', 'Macros the Black'],
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testId => ['Pug']],
                        $group2 => [$testId => ['Rogen']],
                        $siteaccess => [$testId => ['William']],
                    ],
                ],
                0,
                $all,
            ],
            [
                // global scope + two groups + siteaccess
                $testId,
                $siteaccess,
                [$group1, $group2],
                [],
                ['Kulgan', 'Macros the Black'],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testId => ['Pug']],
                        $group2 => [$testId => ['Rogen']],
                        $siteaccess => [$testId => ['William']],
                    ],
                ],
                0,
                ['Pug', 'Rogen', 'William', 'Kulgan', 'Macros the Black'],
            ],
            [
                // default scope + two groups +  global
                $testId,
                $siteaccess,
                [$group1, $group2],
                ['Kulgan', 'Macros the Black'],
                ['William'],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testId => ['Pug']],
                        $group2 => [$testId => ['Rogen']],
                    ],
                ],
                0,
                $all,
            ],
            [
                // default scope + two groups + siteaccess + global
                $testId,
                $siteaccess,
                [$group1, $group2],
                ['Kulgan'],
                ['William'],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testId => ['Macros the Black']],
                        $group2 => [$testId => ['Pug']],
                        $siteaccess => [$testId => ['Rogen']],
                    ],
                ],
                0,
                $all,
            ],
            //
            // UNIQUE OPTION TESTS (only suitable for normal array)
            //
            [
                $testId,
                $siteaccess,
                [$group1, $group2],
                ['Kulgan', 'Kulgan'],
                ['William'],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testId => ['Macros the Black']],
                        $group2 => [$testId => ['Pug']],
                        $siteaccess => [$testId => ['Rogen', 'Pug']],
                    ],
                ],
                ContextualizerInterface::UNIQUE,
                ['Kulgan', 'Macros the Black', 'Pug', 'Rogen', 'William'],
            ],
            [
                $testId,
                $siteaccess,
                [$group1, $group2],
                ['Kulgan', 'Kulgan'],
                ['William', 'Kulgan', 'Pug'],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testId => ['Macros the Black']],
                        $group2 => [$testId => ['Pug', 'William', 'Kulgan']],
                        $siteaccess => [$testId => ['Rogen', 'Pug', 'Rogen', 'Macros the Black']],
                    ],
                ],
                ContextualizerInterface::UNIQUE,
                ['Kulgan', 'Macros the Black', 'Pug', 'William', 'Rogen'],
            ],
            //
            // MERGING HASH TESTS with MERGE_FROM_SECOND_LEVEL
            //
            [
                // everything in default scope
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                $locationView1,
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView1,
            ],
            [
                // everything in global scope
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                $locationView1,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView1,
            ],
            [
                // everything in a group
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView1],
                    ],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView1,
            ],
            [
                // everything in a siteaccess
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $siteaccess => [$testIdHash => $locationView1],
                    ],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView1,
            ],

            [
                // default scope + one group
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                $locationView1,
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView2],
                    ],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView12,
            ],
            [
                // one group + global scope
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                $locationView1,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView2],
                    ],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView21,
            ],
            [
                // default scope + two groups
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                $locationView1,
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView2],
                        $group2 => [$testIdHash => $locationView3],
                    ],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ],
            [
                // default scope + two groups + siteaccess
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                $locationView1,
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView2],
                        $group2 => [$testIdHash => $locationView3],
                        $siteaccess => [$testIdHash => $locationView4],
                    ],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView1234,
            ],
            [
                // two groups
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView1],
                        $group2 => [$testIdHash => $locationView2],
                    ],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView12,
            ],
            [
                // two groups + global scope
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                $locationView3,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView1],
                        $group2 => [$testIdHash => $locationView2],
                    ],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ],
            [
                // two groups + siteaccess + global scope
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                $locationView4,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView1],
                        $group2 => [$testIdHash => $locationView2],
                        $siteaccess => [$testIdHash => $locationView3],
                    ],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView1234,
            ],

            [
                // two groups + siteaccess
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $siteaccess => [$testIdHash => $locationView3],
                        $group1 => [$testIdHash => $locationView1],
                        $group2 => [$testIdHash => $locationView2],
                    ],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ],
            [
                // default scope + group + siteaccess
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                $locationView1,
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView2],
                        $siteaccess => [$testIdHash => $locationView3],
                    ],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ],
            [
                // default scope + group + siteaccess + global scope
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                $locationView1,
                $locationView4,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView2],
                        $siteaccess => [$testIdHash => $locationView3],
                    ],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView1234,
            ],
            [
                // global scope + group + siteaccess
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                $locationView3,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group2 => [$testIdHash => $locationView1],
                        $siteaccess => [$testIdHash => $locationView2],
                    ],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ],
            [
                // default scope + group +  global
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                $locationView1,
                $locationView3,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group2 => [$testIdHash => $locationView2],
                    ],
                ],
                ContextualizerInterface::MERGE_FROM_SECOND_LEVEL,
                $locationView123,
            ],
            //
            // MERGING HASH TESTS without MERGE_FROM_SECOND_LEVEL, the result
            // is always the "last" defined one
            //
            [
                // everything in default scope
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                $locationView1,
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [],
                ],
                0,
                $locationView1,
            ],
            [
                // everything in global scope
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                $locationView1,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [],
                ],
                0,
                $locationView1,
            ],
            [
                // everything in a group
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView1],
                    ],
                ],
                0,
                $locationView1,
            ],
            [
                // everything in a siteaccess
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $siteaccess => [$testIdHash => $locationView1],
                    ],
                ],
                0,
                $locationView1,
            ],
            [
                // default scope + one group
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                $locationView1,
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView2],
                    ],
                ],
                0,
                $locationView2,
            ],
            [
                // one group + global scope
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                $locationView1,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView2],
                    ],
                ],
                0,
                $locationView1,
            ],
            [
                // default scope + two groups
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                $locationView1,
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView2],
                        $group2 => [$testIdHash => $locationView3],
                    ],
                ],
                0,
                $locationView3,
            ],
            [
                // default scope + two groups + siteaccess
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                $locationView1,
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView2],
                        $group2 => [$testIdHash => $locationView3],
                        $siteaccess => [$testIdHash => $locationView4],
                    ],
                ],
                0,
                $locationView4,
            ],
            [
                // default scope + two groups + global scope
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                $locationView1,
                $locationView4,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView2],
                        $group2 => [$testIdHash => $locationView3],
                    ],
                ],
                0,
                $locationView4,
            ],
            [
                // two groups
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView1],
                        $group2 => [$testIdHash => $locationView2],
                    ],
                ],
                0,
                $locationView2,
            ],
            [
                // two groups + global scope
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                $locationView3,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView1],
                        $group2 => [$testIdHash => $locationView2],
                    ],
                ],
                0,
                $locationView3,
            ],
            [
                // two groups + siteaccess
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $siteaccess => [$testIdHash => $locationView3],
                        $group1 => [$testIdHash => $locationView1],
                        $group2 => [$testIdHash => $locationView2],
                    ],
                ],
                0,
                $locationView3,
            ],
            [
                // two groups + siteaccess + global scope
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                $locationView4,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $siteaccess => [$testIdHash => $locationView3],
                        $group1 => [$testIdHash => $locationView1],
                        $group2 => [$testIdHash => $locationView2],
                    ],
                ],
                0,
                $locationView4,
            ],
            [
                // default scope + group + siteaccess
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                $locationView1,
                [],
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group1 => [$testIdHash => $locationView2],
                        $siteaccess => [$testIdHash => $locationView3],
                    ],
                ],
                0,
                $locationView3,
            ],
            [
                // global scope + group + siteaccess
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                [],
                $locationView3,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group2 => [$testIdHash => $locationView1],
                        $siteaccess => [$testIdHash => $locationView2],
                    ],
                ],
                0,
                $locationView3,
            ],
            [
                // default scope + group +  global
                $testIdHash,
                $siteaccess,
                [$group1, $group2],
                $locationView1,
                $locationView3,
                [
                    'siteaccess' => $siteaccessConfig,
                    $this->saNodeName => [
                        $group2 => [$testIdHash => $locationView2],
                    ],
                ],
                0,
                $locationView3,
            ],
        ];

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
