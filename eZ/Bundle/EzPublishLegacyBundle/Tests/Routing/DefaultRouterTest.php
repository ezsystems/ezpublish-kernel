<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Tests\Routing\DefaultRouterTest;

use eZ\Bundle\EzPublishCoreBundle\Tests\Routing\DefaultRouterTest as BaseTest;
use Symfony\Component\HttpFoundation\Request;

class DefaultRouterTest extends BaseTest
{
    protected function getRouterClass()
    {
        return 'eZ\Bundle\EzPublishLegacyBundle\Routing\DefaultRouter';
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testMatchRequestLegacyMode()
    {
        $pathinfo = '/siteaccess/foo/bar';
        $semanticPathinfo = '/foo/bar';
        $request = Request::create( $pathinfo );
        $request->attributes->set( 'semanticPathinfo', $semanticPathinfo );

        /** @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Bundle\EzPublishLegacyBundle\Routing\DefaultRouter $router */
        $router = $this->generateRouter( array( 'match' ) );

        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'legacy_mode' )
            ->will( $this->returnValue( true ) );

        $matchedParameters = array( '_route' => 'my_route' );
        $router
            ->expects( $this->once() )
            ->method( 'match' )
            ->with( $semanticPathinfo )
            ->will( $this->returnValue( $matchedParameters ) );

        $router->matchRequest( $request );
    }

    public function testMatchRequestLegacyModeAuthorizedRoute()
    {
        $pathinfo = '/siteaccess/foo/bar';
        $semanticPathinfo = '/foo/bar';
        $request = Request::create( $pathinfo );
        $request->attributes->set( 'semanticPathinfo', $semanticPathinfo );

        /** @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Bundle\EzPublishLegacyBundle\Routing\DefaultRouter $router */
        $router = $this->generateRouter( array( 'match' ) );
        $router->setLegacyAwareRoutes( array( 'my_legacy_aware_route' ) );

        $matchedParameters = array( '_route' => 'my_legacy_aware_route' );
        $router
            ->expects( $this->once() )
            ->method( 'match' )
            ->with( $semanticPathinfo )
            ->will( $this->returnValue( $matchedParameters ) );

        $this->configResolver->expects( $this->never() )->method( 'getParameter' );

        $this->assertSame( $matchedParameters, $router->matchRequest( $request ) );
    }
}
