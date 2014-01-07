<?php
/**
 * File containing the SecurityTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper\Tests\LegacyMapper;

use eZ\Publish\Core\MVC\Legacy\Event\PostBuildKernelEvent;
use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelWebHandlerEvent;
use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use eZ\Bundle\EzPublishLegacyBundle\LegacyMapper\Security;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class SecurityTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this->getMock( 'eZ\Publish\API\Repository\Repository' );
        $this->configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array(
                LegacyEvents::POST_BUILD_LEGACY_KERNEL => 'onKernelBuilt',
                LegacyEvents::PRE_BUILD_LEGACY_KERNEL_WEB => 'onLegacyKernelWebBuild',
            ),
            Security::getSubscribedEvents()
        );
    }

    public function testOnKernelBuiltNotWebBasedHandler()
    {
        $kernelHandler = $this->getMock( 'ezpKernelHandler' );
        $legacyKernel = $this
            ->getMockBuilder( 'eZ\Publish\Core\MVC\Legacy\Kernel' )
            ->setConstructorArgs( array( $kernelHandler, 'foo', 'bar' ) )
            ->getMock();
        $event = new PostBuildKernelEvent( $legacyKernel, $kernelHandler );

        $this->repository
            ->expects( $this->never() )
            ->method( 'getCurrentUser' );
        $legacyKernel
            ->expects( $this->never() )
            ->method( 'runCallback' );

        $listener = new Security( $this->repository, $this->configResolver );
        $listener->onKernelBuilt( $event );
    }

    public function testOnKernelBuiltWithLegacyMode()
    {
        $kernelHandler = $this->getMock( 'ezpWebBasedKernelHandler' );
        $legacyKernel = $this
            ->getMockBuilder( 'eZ\Publish\Core\MVC\Legacy\Kernel' )
            ->setConstructorArgs( array( $kernelHandler, 'foo', 'bar' ) )
            ->getMock();
        $event = new PostBuildKernelEvent( $legacyKernel, $kernelHandler );

        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'legacy_mode' )
            ->will( $this->returnValue( true ) );
        $this->repository
            ->expects( $this->never() )
            ->method( 'getCurrentUser' );
        $legacyKernel
            ->expects( $this->never() )
            ->method( 'runCallback' );

        $listener = new Security( $this->repository, $this->configResolver );
        $listener->onKernelBuilt( $event );
    }

    public function testOnKernelBuiltDisabled()
    {
        $kernelHandler = $this->getMock( 'ezpWebBasedKernelHandler' );
        $legacyKernel = $this
            ->getMockBuilder( 'eZ\Publish\Core\MVC\Legacy\Kernel' )
            ->setConstructorArgs( array( $kernelHandler, 'foo', 'bar' ) )
            ->getMock();
        $event = new PostBuildKernelEvent( $legacyKernel, $kernelHandler );

        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'legacy_mode' )
            ->will( $this->returnValue( false ) );
        $this->repository
            ->expects( $this->never() )
            ->method( 'getCurrentUser' );
        $legacyKernel
            ->expects( $this->never() )
            ->method( 'runCallback' );

        $listener = new Security( $this->repository, $this->configResolver );
        $listener->setEnabled( false );
        $listener->onKernelBuilt( $event );
    }

    public function testOnKernelBuilt()
    {
        $kernelHandler = $this->getMock( 'ezpWebBasedKernelHandler' );
        $legacyKernel = $this
            ->getMockBuilder( 'eZ\Publish\Core\MVC\Legacy\Kernel' )
            ->setConstructorArgs( array( $kernelHandler, 'foo', 'bar' ) )
            ->getMock();
        $event = new PostBuildKernelEvent( $legacyKernel, $kernelHandler );

        $userId = 123;
        $user = $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\User\User' );
        $user
            ->expects( $this->any() )
            ->method( '__get' )
            ->with( 'id' )
            ->will( $this->returnValue( $userId ) );
        $this->repository
            ->expects( $this->once() )
            ->method( 'getCurrentUser' )
            ->will( $this->returnValue( $user ) );
        $legacyKernel
            ->expects( $this->once() )
            ->method( 'runCallback' )
            ->with( $this->isInstanceOf( 'Closure' ) );

        // TODO: Test legacy static expectations using Mockery

        $listener = new Security( $this->repository, $this->configResolver );
        $listener->onKernelBuilt( $event );
    }

    public function testOnLegacyKernelWebBuildLegacyMode()
    {
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'legacy_mode' )
            ->will( $this->returnValue( true ) );

        $parameters = array( 'foo' => 'bar' );
        $event = new PreBuildKernelWebHandlerEvent( new ParameterBag( $parameters ), new Request );
        $listener = new Security( $this->repository, $this->configResolver );
        $listener->onLegacyKernelWebBuild( $event );
        $this->assertSame( $parameters, $event->getParameters()->all() );
    }

    /**
     * @dataProvider onLegacyKernelWebBuildProvider
     */
    public function testOnLegacyKernelWebBuild( array $previousSettings, array $expected )
    {
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'legacy_mode' )
            ->will( $this->returnValue( false ) );

        $event = new PreBuildKernelWebHandlerEvent( new ParameterBag( $previousSettings ), new Request );
        $listener = new Security( $this->repository, $this->configResolver );
        $listener->onLegacyKernelWebBuild( $event );
        $this->assertSame( $expected, $event->getParameters()->all() );
    }

    public function onLegacyKernelWebBuildProvider()
    {
        return array(
            array(
                array(),
                array(
                    'injected-settings' => array(
                        'site.ini/SiteAccessRules/Rules' => array(
                            'access;disable',
                            'module;user/login',
                            'module;user/logout',
                        )
                    )
                )
            ),
            array(
                array(
                    'foo' => 'bar',
                    'some' => array( 'thing' ),
                ),
                array(
                    'foo' => 'bar',
                    'some' => array( 'thing' ),
                    'injected-settings' => array(
                        'site.ini/SiteAccessRules/Rules' => array(
                            'access;disable',
                            'module;user/login',
                            'module;user/logout',
                        )
                    )
                )
            ),
            array(
                array(
                    'foo' => 'bar',
                    'some' => array( 'thing' ),
                    'injected-settings' => array(
                        'Empire' => array( 'Darth Vader', 'Emperor', 'Moff Tarkin' ),
                        'Rebellion' => array( 'Luke Skywalker', 'Leïa Organa', 'Obi-Wan Kenobi', 'Han Solo' ),
                        'Chewbacca' => 'Arrrrrhhhhhh!'
                    )
                ),
                array(
                    'foo' => 'bar',
                    'some' => array( 'thing' ),
                    'injected-settings' => array(
                        'Empire' => array( 'Darth Vader', 'Emperor', 'Moff Tarkin' ),
                        'Rebellion' => array( 'Luke Skywalker', 'Leïa Organa', 'Obi-Wan Kenobi', 'Han Solo' ),
                        'Chewbacca' => 'Arrrrrhhhhhh!',
                        'site.ini/SiteAccessRules/Rules' => array(
                            'access;disable',
                            'module;user/login',
                            'module;user/logout',
                        )
                    )
                )
            ),
            array(
                array(
                    'foo' => 'bar',
                    'some' => array( 'thing' ),
                    'injected-settings' => array(
                        'site.ini/SiteAccessRules/Rules' => array(
                            'access;disable',
                            'module;ezinfo/about',
                            'access;enable',
                            'module;foo',
                        )
                    )
                ),
                array(
                    'foo' => 'bar',
                    'some' => array( 'thing' ),
                    'injected-settings' => array(
                        'site.ini/SiteAccessRules/Rules' => array(
                            'access;disable',
                            'module;ezinfo/about',
                            'access;enable',
                            'module;foo',
                            'access;disable',
                            'module;user/login',
                            'module;user/logout',
                        )
                    )
                )
            ),
            array(
                array(
                    'foo' => 'bar',
                    'some' => array( 'thing' ),
                    'injected-settings' => array(
                        'site.ini/SiteAccessRules/Rules' => array(
                            'access;disable',
                            'module;ezinfo/about',
                            'access;enable',
                            'module;foo',
                        )
                    )
                ),
                array(
                    'foo' => 'bar',
                    'some' => array( 'thing' ),
                    'injected-settings' => array(
                        'site.ini/SiteAccessRules/Rules' => array(
                            'access;disable',
                            'module;ezinfo/about',
                            'access;enable',
                            'module;foo',
                            'access;disable',
                            'module;user/login',
                            'module;user/logout',
                        )
                    )
                )
            ),
        );
    }
}
