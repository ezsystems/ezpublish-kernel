<?php
/**
 * File containing the HttpUtilsTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Tests;

use eZ\Publish\Core\MVC\Symfony\Security\HttpUtils;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit_Framework_TestCase;

class HttpUtilsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider generateUriStandardProvider
     */
    public function testGenerateUriStandard( $uri, $expected )
    {
        $httpUtils = new HttpUtils();
        $httpUtils->setSiteAccess( new SiteAccess );
        $request = Request::create( 'http://ezpublish.dev/' );
        $this->assertSame( $expected, $httpUtils->generateUri( $request, $uri ) );
    }

    public function generateUriStandardProvider()
    {
        return array(
            array( 'http://localhost/foo/bar', 'http://localhost/foo/bar' ),
            array( 'http://localhost/foo/bar?some=thing&toto=tata', 'http://localhost/foo/bar?some=thing&toto=tata' ),
            array( '/foo/bar?some=thing&toto=tata', 'http://ezpublish.dev/foo/bar?some=thing&toto=tata' ),
            array( '/foo/bar', 'http://ezpublish.dev/foo/bar' ),
        );
    }

    /**
     * @dataProvider generateUriProvider
     */
    public function testGenerateUri( $uri, $siteAccessUri, $expected )
    {
        $siteAccess = new SiteAccess( 'test', 'test' );
        if ( $uri[0] === '/' )
        {
            $matcher = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer' );
            $matcher
                ->expects( $this->once() )
                ->method( 'analyseLink' )
                ->with( $uri )
                ->will( $this->returnValue( $siteAccessUri . $uri ) );
            $siteAccess->matcher = $matcher;
        }

        $httpUtils = new HttpUtils();
        $httpUtils->setSiteAccess( $siteAccess );
        $request = Request::create( 'http://ezpublish.dev/' );
        $this->assertSame( $expected, $httpUtils->generateUri( $request, $uri ) );
    }

    public function generateUriProvider()
    {
        return array(
            array( 'http://localhost/foo/bar', null, 'http://localhost/foo/bar' ),
            array( 'http://localhost/foo/bar?some=thing&toto=tata', null, 'http://localhost/foo/bar?some=thing&toto=tata' ),
            array( '/foo/bar?some=thing&toto=tata', '/test_access', 'http://ezpublish.dev/test_access/foo/bar?some=thing&toto=tata' ),
            array( '/foo/bar', '/blabla', 'http://ezpublish.dev/blabla/foo/bar' ),
        );
    }

    public function testCheckRequestPathStandard()
    {
        $httpUtils = new HttpUtils();
        $httpUtils->setSiteAccess( new SiteAccess );
        $request = Request::create( "http://ezpublish.dev/foo/bar" );
        $this->assertTrue( $httpUtils->checkRequestPath( $request, '/foo/bar' ) );
    }

    /**
     * @dataProvider checkRequestPathProvider
     */
    public function testCheckRequestPath( $path, $siteAccessUri, $requestUri, $expected )
    {
        $siteAccess = new SiteAccess( 'test', 'test' );
        if ( $siteAccessUri !== null )
        {
            $matcher = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer' );
            $matcher
                ->expects( $this->once() )
                ->method( 'analyseLink' )
                ->with( $path )
                ->will( $this->returnValue( $siteAccessUri . $path ) );
            $siteAccess->matcher = $matcher;
        }

        $httpUtils = new HttpUtils();
        $httpUtils->setSiteAccess( $siteAccess );
        $request = Request::create( $requestUri );
        $this->assertSame( $expected, $httpUtils->checkRequestPath( $request, $path ) );
    }

    public function checkRequestPathProvider()
    {
        return array(
            array( '/foo/bar', null, 'http://localhost/foo/bar', true ),
            array( '/foo', null, 'http://localhost/foo/bar', false ),
            array( '/foo/bar', null, 'http://localhost/foo/bar?some=thing&toto=tata', true ),
            array( '/foo/bar', '/test_access', 'http://ezpublish.dev/test_access/foo/bar?some=thing&toto=tata', true ),
            array( '/foo', '/test_access', 'http://ezpublish.dev/test_access/foo/bar?some=thing&toto=tata', false ),
            array( '/foo/bar', '/blabla', 'http://ezpublish.dev/blabla/foo/bar', true ),
        );
    }
}
