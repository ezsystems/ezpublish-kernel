<?php
/**
 * File containing the DefaultRouterTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Routing;

use eZ\Bundle\EzPublishCoreBundle\Routing\DefaultRouter;
use Symfony\Component\HttpFoundation\Request;

class DefaultRouterTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchRequestWithSemanticPathinfo()
    {
        $pathinfo = '/siteaccess/foo/bar';
        $semanticPathinfo = '/foo/bar';
        $request = $this
            ->getMockBuilder( 'Symfony\\Component\\HttpFoundation\\Request' )
            ->setMethods( array( 'getPathInfo' ) )
            ->getMock();
        $request
            ->expects( $this->any() )
            ->method( 'getPathInfo' )
            ->will( $this->returnValue( $pathinfo ) );
        $request->attributes->set( 'semanticPathinfo', $semanticPathinfo );

        /** @var \PHPUnit_Framework_MockObject_MockObject|DefaultRouter $router */
        $router = $this
            ->getMockBuilder( 'eZ\\Bundle\\EzPublishCoreBundle\\Routing\\DefaultRouter' )
            ->disableOriginalConstructor()
            ->setMethods( array( 'match' ) )
            ->getMock();

        $matchedParameters = array( '_controller' => 'AcmeBundle:myAction' );
        $router
            ->expects( $this->once() )
            ->method( 'match' )
            ->with( $semanticPathinfo )
            ->will( $this->returnValue( $matchedParameters ) );
        $this->assertSame( $matchedParameters, $router->matchRequest( $request ) );
    }

    public function testMatchRequestRegularPathinfo()
    {
        $matchedParameters = array( '_controller' => 'AcmeBundle:myAction' );
        $pathinfo = '/siteaccess/foo/bar';

        $request = $this
            ->getMockBuilder( 'Symfony\\Component\\HttpFoundation\\Request' )
            ->setMethods( array( 'getPathInfo' ) )
            ->getMock();
        $request
            ->expects( $this->atLeastOnce() )
            ->method( 'getPathInfo' )
            ->will( $this->returnValue( $pathinfo ) );

        /** @var \PHPUnit_Framework_MockObject_MockObject|DefaultRouter $router */
        $router = $this
            ->getMockBuilder( 'eZ\\Bundle\\EzPublishCoreBundle\\Routing\\DefaultRouter' )
            ->disableOriginalConstructor()
            ->setMethods( array( 'match' ) )
            ->getMock();

        $router
            ->expects( $this->once() )
            ->method( 'match' )
            ->with( $pathinfo )
            ->will( $this->returnValue( $matchedParameters ) );
        $this->assertSame( $matchedParameters, $router->matchRequest( $request ) );
    }
}
