<?php
/**
 * File containing the ContextualizerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\SiteAccessAware;

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

    private $namespace;

    private $saNodeName;

    private $availableSAs;

    private $groupsBySA;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Contextualizer
     */
    private $contextualizer;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' );
        $this->namespace = 'ez_test';
        $this->saNodeName = 'heyho';
        $this->availableSAs = array( 'sa1', 'sa2', 'sa3' );
        $this->groupsBySA = array(
            'sa1' => array( 'sa_group1', 'sa_group2' ),
            'sa2' => array( 'sa_group1' ),
            'sa3' => array( 'sa_group1' )
        );
        $this->contextualizer = new Contextualizer( $this->container, $this->namespace, $this->saNodeName, $this->availableSAs, $this->groupsBySA );
    }

    /**
     * @dataProvider setContextualParameterProvider
     */
    public function testSetContextualParameter( $parameterName, $scope, $value )
    {
        $this->container
            ->expects( $this->once() )
            ->method( 'setParameter' )
            ->with( "$this->namespace.$scope.$parameterName", $value );

        $this->contextualizer->setContextualParameter( $parameterName, $scope, $value );
    }

    public function setContextualParameterProvider()
    {
        return array(
            array( 'my_parameter', 'sa1', 'foobar' ),
            array( 'some', 'sa1', 'thing' ),
            array( 'my_array', 'sa3', array( 'foo', 'bar' ) ),
            array( 'my_hash', 'sa2', array( 'foo' => 'bar', 'hey' => array( 'ho' ), 'enabled' => true ) ),
            array( 'my_integer', 'sa3', 123 ),
            array( 'my_bool', 'sa2', false ),
        );
    }

    public function testMapSetting()
    {
        $fooSa1 = 'bar';
        $planetsSa1 = array( 'Earth' );
        $intSa1 = 123;
        $boolSa1 = true;
        $sa1Config = array(
            'foo' => $fooSa1,
            'planets' => $planetsSa1,
            'an_integer' => $intSa1,
            'a_bool' => $boolSa1,
        );
        $fooSa2 = 'bar2';
        $planetsSa2 = array( 'Earth', 'Mars', 'Venus' );
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
            )
        );

        $container = new ContainerBuilder();
        $this->contextualizer->setContainer( $container );
        $this->contextualizer->mapSetting( 'foo', $config );
        $this->contextualizer->mapSetting( 'planets', $config );
        $this->contextualizer->mapSetting( 'an_integer', $config );
        $this->contextualizer->mapSetting( 'a_bool', $config );

        $this->assertSame( $fooSa1, $container->getParameter( "$this->namespace.sa1.foo" ) );
        $this->assertSame( $planetsSa1, $container->getParameter( "$this->namespace.sa1.planets" ) );
        $this->assertSame( $intSa1, $container->getParameter( "$this->namespace.sa1.an_integer" ) );
        $this->assertSame( $boolSa1, $container->getParameter( "$this->namespace.sa1.a_bool" ) );

        $this->assertSame( $fooSa2, $container->getParameter( "$this->namespace.sa2.foo" ) );
        $this->assertSame( $planetsSa2, $container->getParameter( "$this->namespace.sa2.planets" ) );
        $this->assertSame( $intSa2, $container->getParameter( "$this->namespace.sa2.an_integer" ) );
        $this->assertSame( $boolSa2, $container->getParameter( "$this->namespace.sa2.a_bool" ) );
    }

    public function testMapConfigArray()
    {
        $containerBuilder = new ContainerBuilder();
        $this->contextualizer->setContainer( $containerBuilder );
        $defaultConfig = array(
            'foo' => null,
            'some' => null,
            'planets' => array( 'Earth' ),
            'an_integer' => 0,
            'enabled' => false,
            'j_adore' => 'les_sushis'
        );
        $containerBuilder->setParameter( "$this->namespace.default.foo_setting", $defaultConfig );

        $config = array(
            $this->saNodeName => array(
                'sa_group1' => array(
                    'foo_setting' => array(
                        'foo' => 'bar',
                        'some' => 'thing',
                        'an_integer' => 123,
                    )
                ),
                'sa1' => array(
                    'foo_setting' => array(
                        'an_integer' => 456,
                        'enabled' => true,
                        'j_adore' => 'le_saucisson'
                    )
                ),
                'sa2' => array(
                    'foo_setting' => array(
                        'foo' => 'baz',
                        'planets' => array( 'Mars', 'Venus' ),
                        'an_integer' => 789
                    )
                ),
                'global' => array(
                    'foo_setting' => array(
                        'j_adore' => 'la_truite_a_la_vapeur'
                    )
                )
            )
        );

        $expectedMergedSettings = array(
            'sa1' => array(
                'foo' => 'bar',
                'some' => 'thing',
                'planets' => array( 'Earth' ),
                'an_integer' => 456,
                'enabled' => true,
                'j_adore' => 'la_truite_a_la_vapeur'
            ),
            'sa2' => array(
                'foo' => 'baz',
                'some' => 'thing',
                'planets' => array( 'Mars', 'Venus' ),
                'an_integer' => 789,
                'enabled' => false,
                'j_adore' => 'la_truite_a_la_vapeur'
            ),
            'sa3' => array(
                'foo' => 'bar',
                'some' => 'thing',
                'planets' => array( 'Earth' ),
                'an_integer' => 123,
                'enabled' => false,
                'j_adore' => 'la_truite_a_la_vapeur'
            )
        );

        $this->contextualizer->mapConfigArray( 'foo_setting', $config );

        $this->assertSame(
            $expectedMergedSettings['sa1'],
            $containerBuilder->getParameter( "$this->namespace.sa1.foo_setting" )
        );
        $this->assertSame(
            $expectedMergedSettings['sa2'],
            $containerBuilder->getParameter( "$this->namespace.sa2.foo_setting" )
        );
        $this->assertSame(
            $expectedMergedSettings['sa3'],
            $containerBuilder->getParameter( "$this->namespace.sa3.foo_setting" )
        );
    }

    public function testMapConfigArraySecondLevel()
    {
        $containerBuilder = new ContainerBuilder();
        $this->contextualizer->setContainer( $containerBuilder );
        $defaultConfig = array(
            'foo' => null,
            'some' => null,
            'planets' => array( 'Earth' ),
            'an_integer' => 0,
            'enabled' => false,
            'j_adore' => array( 'les_sushis' )
        );
        $containerBuilder->setParameter( "$this->namespace.default.foo_setting", $defaultConfig );

        $config = array(
            $this->saNodeName => array(
                'sa_group1' => array(
                    'foo_setting' => array(
                        'foo' => 'bar',
                        'some' => 'thing',
                        'an_integer' => 123,
                    )
                ),
                'sa1' => array(
                    'foo_setting' => array(
                        'an_integer' => 456,
                        'enabled' => true,
                        'j_adore' => array( 'le_saucisson' )
                    )
                ),
                'sa2' => array(
                    'foo_setting' => array(
                        'foo' => 'baz',
                        'planets' => array( 'Mars', 'Venus' ),
                        'an_integer' => 789
                    )
                ),
                'sa3' => array(
                    'foo_setting' => array(
                        'planets' => array( 'Earth', 'Jupiter' )
                    )
                ),
                'global' => array(
                    'foo_setting' => array(
                        'j_adore' => array( 'la_truite_a_la_vapeur' )
                    )
                )
            )
        );

        $expectedMergedSettings = array(
            'sa1' => array(
                'foo' => 'bar',
                'some' => 'thing',
                'planets' => array( 'Earth' ),
                'an_integer' => 456,
                'enabled' => true,
                'j_adore' => array( 'les_sushis', 'le_saucisson', 'la_truite_a_la_vapeur' )
            ),
            'sa2' => array(
                'foo' => 'baz',
                'some' => 'thing',
                'planets' => array( 'Earth', 'Mars', 'Venus' ),
                'an_integer' => 789,
                'enabled' => false,
                'j_adore' => array( 'les_sushis', 'la_truite_a_la_vapeur' )
            ),
            'sa3' => array(
                'foo' => 'bar',
                'some' => 'thing',
                'planets' => array( 'Earth', 'Earth', 'Jupiter' ),
                'an_integer' => 123,
                'enabled' => false,
                'j_adore' => array( 'les_sushis', 'la_truite_a_la_vapeur' )
            )
        );

        $this->contextualizer->mapConfigArray( 'foo_setting', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL );

        $this->assertSame(
            $expectedMergedSettings['sa1'],
            $containerBuilder->getParameter( "$this->namespace.sa1.foo_setting" )
        );
        $this->assertSame(
            $expectedMergedSettings['sa2'],
            $containerBuilder->getParameter( "$this->namespace.sa2.foo_setting" )
        );
        $this->assertSame(
            $expectedMergedSettings['sa3'],
            $containerBuilder->getParameter( "$this->namespace.sa3.foo_setting" )
        );
    }

    public function testMapConfigArrayUnique()
    {
        $containerBuilder = new ContainerBuilder();
        $this->contextualizer->setContainer( $containerBuilder );
        $defaultConfig = array( 'Earth' );
        $containerBuilder->setParameter( "$this->namespace.default.foo_setting", $defaultConfig );

        $config = array(
            $this->saNodeName => array(
                'sa_group1' => array(
                    'foo_setting' => array( 'Mars' )
                ),
                'sa1' => array(
                    'foo_setting' => array( 'Earth' )
                ),
                'sa2' => array(
                    'foo_setting' => array( 'Mars', 'Venus' ),
                ),
                'sa3' => array(
                    'foo_setting' => array( 'Earth', 'Jupiter' )
                )
            )
        );

        $expectedMergedSettings = array(
            'sa1' => array( 'Earth', 'Mars' ),
            'sa2' => array( 'Earth', 'Mars', 'Venus' ),
            'sa3' => array( 'Earth', 'Mars', 'Jupiter' ),
        );

        $this->contextualizer->mapConfigArray( 'foo_setting', $config, ContextualizerInterface::UNIQUE );

        $this->assertSame(
            $expectedMergedSettings['sa1'],
            $containerBuilder->getParameter( "$this->namespace.sa1.foo_setting" )
        );
        $this->assertSame(
            $expectedMergedSettings['sa2'],
            $containerBuilder->getParameter( "$this->namespace.sa2.foo_setting" )
        );
        $this->assertSame(
            $expectedMergedSettings['sa3'],
            $containerBuilder->getParameter( "$this->namespace.sa3.foo_setting" )
        );
    }

    public function testGetSetContainer()
    {
        $this->assertSame( $this->container, $this->contextualizer->getContainer() );
        $containerBuilder = new ContainerBuilder();
        $this->contextualizer->setContainer( $containerBuilder );
        $this->assertSame( $containerBuilder, $this->contextualizer->getContainer() );
    }

    public function testGetSetSANodeName()
    {
        $nodeName = 'foobarbaz';
        $this->assertSame( $this->saNodeName, $this->contextualizer->getSiteAccessNodeName() );
        $this->contextualizer->setSiteAccessNodeName( $nodeName );
        $this->assertSame( $nodeName, $this->contextualizer->getSiteAccessNodeName() );
    }

    public function testGetSetNamespace()
    {
        $ns = 'ezpublish';
        $this->assertSame( $this->namespace, $this->contextualizer->getNamespace() );
        $this->contextualizer->setNamespace( $ns );
        $this->assertSame( $ns, $this->contextualizer->getNamespace() );
    }

    public function testGetSetAvailableSiteAccesses()
    {
        $this->assertSame( $this->availableSAs, $this->contextualizer->getAvailableSiteAccesses() );
        $sa = array( 'foo', 'bar', 'baz' );
        $this->contextualizer->setAvailableSiteAccesses( $sa );
        $this->assertSame( $sa, $this->contextualizer->getAvailableSiteAccesses() );
    }

    public function testGetSetGroupsBySA()
    {
        $this->assertSame( $this->groupsBySA, $this->contextualizer->getGroupsBySiteAccess() );
        $groups = array( 'foo' => array( 'bar', 'baz' ), 'group2' => array( 'some', 'thing' ) );
        $this->contextualizer->setGroupsBySiteAccess( $groups );
        $this->assertSame( $groups, $this->contextualizer->getGroupsBySiteAccess() );
    }
}
