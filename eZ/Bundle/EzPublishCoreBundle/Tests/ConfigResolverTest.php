<?php
/**
 * File containing the ConfigResolverTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;

class ConfigResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    private $siteAccess;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $containerMock;

    protected function setUp()
    {
        parent::setUp();
        $this->siteAccess = new SiteAccess( 'test' );
        $this->containerMock = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );
    }

    /**
     * @param string $defaultNS
     * @param int $undefinedStrategy
     *
     * @return \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver
     */
    private function getResolver( $defaultNS = 'ezsettings', $undefinedStrategy = ConfigResolver::UNDEFINED_STRATEGY_EXCEPTION, array $groupsBySiteAccess = array() )
    {
        return new ConfigResolver(
            $this->siteAccess,
            $groupsBySiteAccess,
            $this->containerMock,
            $defaultNS,
            $undefinedStrategy
        );
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::getUndefinedStrategy
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::setUndefinedStrategy
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::getDefaultNamespace
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::setDefaultNamespace
     */
    public function testGetSetUndefinedStrategy()
    {
        $strategy = ConfigResolver::UNDEFINED_STRATEGY_NULL;
        $defaultNS = 'ezsettings';
        $resolver = $this->getResolver( $defaultNS, $strategy );

        $this->assertSame( $strategy, $resolver->getUndefinedStrategy() );
        $resolver->setUndefinedStrategy( ConfigResolver::UNDEFINED_STRATEGY_EXCEPTION );
        $this->assertSame( ConfigResolver::UNDEFINED_STRATEGY_EXCEPTION, $resolver->getUndefinedStrategy() );

        $this->assertSame( $defaultNS, $resolver->getDefaultNamespace() );
        $resolver->setDefaultNamespace( 'anotherNamespace' );
        $this->assertSame( 'anotherNamespace', $resolver->getDefaultNamespace() );
    }

    /**
     * @expectedException \eZ\Publish\Core\MVC\Exception\ParameterNotFoundException
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::getParameter
     */
    public function testGetParameterFailedWithException()
    {
        $resolver = $this->getResolver( 'ezsettings', ConfigResolver::UNDEFINED_STRATEGY_EXCEPTION );
        $resolver->getParameter( 'foo' );
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::getParameter
     */
    public function testGetParameterFailedNull()
    {
        $resolver = $this->getResolver( 'ezsettings', ConfigResolver::UNDEFINED_STRATEGY_NULL );
        $this->assertNull( $resolver->getParameter( 'foo' ) );
    }

    public function parameterProvider()
    {
        return array(
            array( 'foo', 'bar' ),
            array( 'some.parameter', true ),
            array( 'some.other.parameter', array( 'foo', 'bar', 'baz' ) ),
            array( 'a.hash.parameter', array( 'foo' => 'bar', 'tata' => 'toto' ) ),
            array(
                'a.deep.hash', array(
                    'foo' => 'bar',
                    'tata' => 'toto',
                    'deeper_hash' => array(
                        'likeStarWars'   => true,
                        'jedi'     => array( 'Obi-Wan Kenobi', 'Mace Windu', 'Luke Skywalker', 'Leïa Skywalker (yes! Read episodes 7-8-9!)' ),
                        'sith'     => array( 'Darth Vader', 'Darth Maul', 'Palpatine' ),
                        'roles'    => array(
                            'Amidala'   => array( 'Queen' ),
                            'Palpatine' => array( 'Senator', 'Emperor', 'Villain' ),
                            'C3PO'      => array( 'Droid', 'Annoying guy' ),
                            'Jar-Jar'   => array( 'Still wondering his role', 'Annoying guy' )
                        )
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider parameterProvider
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::getParameter
     */
    public function testGetParameterGlobalScope( $paramName, $expectedValue )
    {
        $globalScopeParameter = "ezsettings.global.$paramName";
        $this->containerMock
            ->expects( $this->once() )
            ->method( 'hasParameter' )
            ->with( $globalScopeParameter )
            ->will( $this->returnValue( true ) );
        $this->containerMock
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( $globalScopeParameter )
            ->will( $this->returnValue( $expectedValue ) );

        $this->assertSame( $expectedValue, $this->getResolver()->getParameter( $paramName ) );
    }

    /**
     * @dataProvider parameterProvider
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::getParameter
     */
    public function testGetParameterRelativeScope( $paramName, $expectedValue )
    {
        $relativeScopeParameter = "ezsettings.{$this->siteAccess->name}.$paramName";
        $this->containerMock
            ->expects( $this->exactly( 2 ) )
            ->method( 'hasParameter' )
            ->with(
                $this->logicalOr(
                    "ezsettings.global.$paramName",
                    $relativeScopeParameter
                )
            )
            // First call is for "global" scope, second is the right one
            ->will( $this->onConsecutiveCalls( false, true ) );
        $this->containerMock
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( $relativeScopeParameter )
            ->will( $this->returnValue( $expectedValue ) );

        $this->assertSame( $expectedValue, $this->getResolver()->getParameter( $paramName ) );
    }

    /**
     * @dataProvider parameterProvider
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::getParameter
     */
    public function testGetParameterSpecificScope( $paramName, $expectedValue )
    {
        $scope = 'some_siteaccess';
        $relativeScopeParameter = "ezsettings.$scope.$paramName";
        $this->containerMock
            ->expects( $this->exactly( 2 ) )
            ->method( 'hasParameter' )
            ->with(
                $this->logicalOr(
                    "ezsettings.global.$paramName",
                    $relativeScopeParameter
                )
            )
        // First call is for "global" scope, second is the right one
            ->will( $this->onConsecutiveCalls( false, true ) );
        $this->containerMock
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( $relativeScopeParameter )
            ->will( $this->returnValue( $expectedValue ) );

        $this->assertSame(
            $expectedValue,
            $this->getResolver()->getParameter( $paramName, 'ezsettings', $scope )
        );
    }

    /**
     * @dataProvider parameterProvider
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::getParameter
     */
    public function testGetParameterDefaultScope( $paramName, $expectedValue )
    {
        $defaultScopeParameter = "ezsettings.default.$paramName";
        $relativeScopeParameter = "ezsettings.{$this->siteAccess->name}.$paramName";
        $this->containerMock
            ->expects( $this->exactly( 3 ) )
            ->method( 'hasParameter' )
            ->with(
                $this->logicalOr(
                    "ezsettings.global.$paramName",
                    $relativeScopeParameter,
                    $defaultScopeParameter
                )
            )
            // First call is for "global" scope, second is the right one
            ->will( $this->onConsecutiveCalls( false, false, true ) );
        $this->containerMock
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( $defaultScopeParameter )
            ->will( $this->returnValue( $expectedValue ) );

        $this->assertSame( $expectedValue, $this->getResolver()->getParameter( $paramName ) );
    }

    public function hasParameterProvider()
    {
        return array(
            array( true, true, true, true ),
            array( true, true, false, true ),
            array( true, false, false, true ),
            array( false, false, false, false ),
            array( false, true, false, true ),
            array( false, false, true, true ),
            array( false, true, true, true ),
        );
    }

    /**
     * @dataProvider hasParameterProvider
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::hasParameter
     */
    public function testHasParameterNoNamespace( $defaultMatch, $scopeMatch, $globalMatch, $expectedResult )
    {
        $paramName = 'foo.bar';
        $this->containerMock->expects( $this->atLeastOnce() )
            ->method( 'hasParameter' )
            ->with(
                $this->logicalOr(
                    "ezsettings.global.$paramName",
                    "ezsettings.{$this->siteAccess->name}.$paramName",
                    "ezsettings.default.$paramName"
                )
            )
            ->will( $this->onConsecutiveCalls( $defaultMatch, $scopeMatch, $globalMatch ) );

        $this->assertSame( $expectedResult, $this->getResolver()->hasParameter( $paramName ) );
    }

    /**
     * @dataProvider hasParameterProvider
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver::hasParameter
     */
    public function testHasParameterWithNamespaceAndScope( $defaultMatch, $scopeMatch, $globalMatch, $expectedResult )
    {
        $paramName = 'foo.bar';
        $namespace = 'my.namespace';
        $scope = "another_siteaccess";
        $this->containerMock->expects( $this->atLeastOnce() )
            ->method( 'hasParameter' )
            ->with(
                $this->logicalOr(
                    "$namespace.global.$paramName",
                    "$namespace.$scope.$paramName",
                    "$namespace.default.$paramName"
                )
            )
            ->will( $this->onConsecutiveCalls( $defaultMatch, $scopeMatch, $globalMatch ) );

        $this->assertSame( $expectedResult, $this->getResolver()->hasParameter( $paramName, $namespace, $scope ) );
    }
}
