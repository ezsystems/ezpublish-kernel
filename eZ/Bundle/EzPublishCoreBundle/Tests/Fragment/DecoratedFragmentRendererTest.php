<?php
/**
 * File containing the DecoratedFragmentRendererTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Fragment;

use eZ\Bundle\EzPublishCoreBundle\Fragment\DecoratedFragmentRenderer;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class DecoratedFragmentRendererTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $innerRenderer;

    protected function setUp()
    {
        parent::setUp();
        $this->innerRenderer = $this->getMock( 'Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface' );
    }

    public function testSetFragmentPathNotRoutableRenderer()
    {
        $matcher = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer' );
        $siteAccess = new SiteAccess( 'test', 'test', $matcher );
        $matcher
            ->expects( $this->never() )
            ->method( 'analyseLink' );

        $renderer = new DecoratedFragmentRenderer( $this->innerRenderer );
        $renderer->setSiteAccess( $siteAccess );
        $renderer->setFragmentPath( 'foo' );
    }

    public function testSetFragmentPath()
    {
        $matcher = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer' );
        $siteAccess = new SiteAccess( 'test', 'test', $matcher );
        $matcher
            ->expects( $this->once() )
            ->method( 'analyseLink' )
            ->with( '/foo' )
            ->will( $this->returnValue( '/bar/foo' ) );

        $innerRenderer = $this->getMock( 'Symfony\Component\HttpKernel\Fragment\RoutableFragmentRenderer' );
        $innerRenderer
            ->expects( $this->once() )
            ->method( 'setFragmentPath' )
            ->with( '/bar/foo' );
        $renderer = new DecoratedFragmentRenderer( $innerRenderer );
        $renderer->setSiteAccess( $siteAccess );
        $renderer->setFragmentPath( '/foo' );
    }

    public function testGetName()
    {
        $name = 'test';
        $this->innerRenderer
            ->expects( $this->once() )
            ->method( 'getName' )
            ->will( $this->returnValue( $name ) );

        $renderer = new DecoratedFragmentRenderer( $this->innerRenderer );
        $this->assertSame( $name, $renderer->getName() );
    }

    public function testRendererAbsoluteUrl()
    {
        $url = 'http://phoenix-rises.fm/foo/bar';
        $request = new Request();
        $options = array( 'foo' => 'bar' );
        $expectedReturn = '/_fragment?foo=bar';
        $this->innerRenderer
            ->expects( $this->once() )
            ->method( 'render' )
            ->with( $url, $request, $options )
            ->will( $this->returnValue( $expectedReturn ) );

        $renderer = new DecoratedFragmentRenderer( $this->innerRenderer );
        $this->assertSame( $expectedReturn, $renderer->render( $url, $request, $options ) );
    }

    public function testRendererControllerReference()
    {
        $reference = new ControllerReference( 'FooBundle:bar:baz' );
        $siteAccess = new SiteAccess( 'test', 'test' );
        $request = new Request();
        $request->attributes->set( 'siteaccess', $siteAccess );
        $options = array( 'foo' => 'bar' );
        $expectedReturn = '/_fragment?foo=bar';
        $this->innerRenderer
            ->expects( $this->once() )
            ->method( 'render' )
            ->with( $reference, $request, $options )
            ->will( $this->returnValue( $expectedReturn ) );

        $renderer = new DecoratedFragmentRenderer( $this->innerRenderer );
        $this->assertSame( $expectedReturn, $renderer->render( $reference, $request, $options ) );
        $this->assertTrue( isset( $reference->attributes['serialized_siteaccess'] ) );
        $this->assertSame( serialize( $siteAccess ), $reference->attributes['serialized_siteaccess'] );
    }

    /**
     * @dataProvider siteAccessProvider
     */
    public function testFragmentContainsSiteaccessMatcher( $siteAccess, $isMatcherExpected )
    {
        $reference = new ControllerReference( 'FooBundle:bar:baz' );
        $request = new Request();
        $request->attributes->set( 'siteaccess', $siteAccess );
        $renderer = new DecoratedFragmentRenderer( $this->innerRenderer );

        $expectedMatcherObject = $isMatcherExpected ? $siteAccess->matcher : null;

        $renderer->render( $reference, $request );

        $unserializedSiteaccess = unserialize( $reference->attributes['serialized_siteaccess'] );
        $this->assertEquals( $expectedMatcherObject, $unserializedSiteaccess->matcher );
    }

    /**
     * @return array
     */
    public function siteAccessProvider()
    {
        $uriLexerMock = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\URILexer' );
        return array(
            'host:map'      => array( $this->buildSiteAccess( 'host:map', 'Map\\Host' ), false ),
            'host:text'     => array( $this->buildSiteAccess( 'host:text', 'HostText' ), false ),
            'uri:text'      => array( $this->buildSiteAccess( 'uri:text', 'URIText' ), true ),
            'host:regexp'   => array( $this->buildSiteAccess( 'host:regexp', 'Regex\\Host' ), false ),
            'uri:regexp'    => array( $this->buildSiteAccess( 'uri:regexp', 'Regex\\URI' ), false ),
            // URILexers:
            'uri:map'       => array( $this->buildSiteAccess( 'uri:map', 'Map\\URI' ), true ),
            'uri:element'   => array( $this->buildSiteAccess( 'uri:element', 'URIElement' ), true ),
            'compound:logicalAnd' => array( $this->buildSiteAccess( 'compound:logicalAnd', 'Compound\\LogicalAnd' ), true ),
            'compound:logicalOr'  => array( $this->buildSiteAccess( 'compound:logicalOr', 'Compound\\LogicalOr' ), true ),
            'URILexerMock'  => array( $this->buildSiteAccess( 'URILexerMock', $uriLexerMock  ), true ),
        );
    }

    /**
     * @param string $type     siteaccess type identifier
     * @param string $matcher  siteaccess matcher class
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    private function buildSiteAccess( $type, $matcher )
    {
        if ( !is_object( $matcher ) )
        {
            $class = "eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\Matcher\\$matcher";
            $matcher = new $class( array() );
        }
        return new SiteAccess( 'ezdemo_site', $type, $matcher );
    }
}
