<?php
/**
 * File containing the SecurityTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Tests\LegacyMapper;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Legacy\Event\PostBuildKernelEvent;
use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelWebHandlerEvent;
use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Bundle\EzPublishLegacyBundle\LegacyMapper\Security;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit_Framework_TestCase;

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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $securityContext;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this->getMock( 'eZ\Publish\API\Repository\Repository' );
        $this->configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $this->securityContext = $this->getMock( 'Symfony\Component\Security\Core\SecurityContextInterface' );
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

        $listener = new Security( $this->repository, $this->configResolver, $this->securityContext );
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

        $listener = new Security( $this->repository, $this->configResolver, $this->securityContext );
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

        $this->repository
            ->expects( $this->never() )
            ->method( 'getCurrentUser' );
        $legacyKernel
            ->expects( $this->never() )
            ->method( 'runCallback' );

        $listener = new Security( $this->repository, $this->configResolver, $this->securityContext );
        $listener->setEnabled( false );
        $listener->onKernelBuilt( $event );
    }

    public function testOnKerneBuiltNotAuthenticated()
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
        $this->securityContext
            ->expects( $this->once() )
            ->method( 'isGranted' )
            ->with( 'IS_AUTHENTICATED_REMEMBERED' )
            ->will( $this->returnValue( false ) );
        $this->repository
            ->expects( $this->never() )
            ->method( 'getCurrentUser' );
        $legacyKernel
            ->expects( $this->never() )
            ->method( 'runCallback' );

        $listener = new Security( $this->repository, $this->configResolver, $this->securityContext );
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

        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'legacy_mode' )
            ->will( $this->returnValue( false ) );
        $this->securityContext
            ->expects( $this->once() )
            ->method( 'isGranted' )
            ->with( 'IS_AUTHENTICATED_REMEMBERED' )
            ->will( $this->returnValue( true ) );

        $userId = 123;
        $user = $this->generateUser( $userId );
        $this->repository
            ->expects( $this->once() )
            ->method( 'getCurrentUser' )
            ->will( $this->returnValue( $user ) );

        $legacyKernel
            ->expects( $this->once() )
            ->method( 'runCallback' );

        $listener = new Security( $this->repository, $this->configResolver, $this->securityContext );
        $listener->onKernelBuilt( $event );
    }

    /**
     * @param $userId
     *
     * @return \eZ\Publish\Core\Repository\Values\User\User
     */
    private function generateUser( $userId )
    {
        $versionInfo = $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\Content\VersionInfo' );
        $versionInfo
            ->expects( $this->any() )
            ->method( 'getContentInfo' )
            ->will( $this->returnValue( new ContentInfo( array( 'id' => $userId ) ) ) );
        $content = $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\Content\Content' );
        $content
            ->expects( $this->any() )
            ->method( 'getVersionInfo' )
            ->will( $this->returnValue( $versionInfo ) );

        return new User( array( 'content' => $content ) );
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
        $listener = new Security( $this->repository, $this->configResolver, $this->securityContext );
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
        $listener = new Security( $this->repository, $this->configResolver, $this->securityContext );
        $listener->onLegacyKernelWebBuild( $event );
        $this->assertSame( $expected, $event->getParameters()->all() );
    }

    public function onLegacyKernelWebBuildProvider()
    {
        return array(
            array(
                array(),
                array(
                    'injected-merge-settings' => array(
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
                    'injected-merge-settings' => array(
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
                    'injected-merge-settings' => array(
                        'Empire' => array( 'Darth Vader', 'Emperor', 'Moff Tarkin' ),
                        'Rebellion' => array( 'Luke Skywalker', 'Leïa Organa', 'Obi-Wan Kenobi', 'Han Solo' ),
                        'Chewbacca' => 'Arrrrrhhhhhh!'
                    )
                ),
                array(
                    'foo' => 'bar',
                    'some' => array( 'thing' ),
                    'injected-merge-settings' => array(
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
                    'injected-merge-settings' => array(
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
                    'injected-merge-settings' => array(
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
                    'injected-merge-settings' => array(
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
                    'injected-merge-settings' => array(
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
