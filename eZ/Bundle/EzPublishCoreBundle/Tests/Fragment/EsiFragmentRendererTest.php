<?php
/**
 * File containing the EsiFragmentRendererTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Fragment;

use eZ\Bundle\EzPublishCoreBundle\Fragment\EsiFragmentRenderer;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use ReflectionObject;

class EsiFragmentRendererTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider setFragmentPathProvider
     */
    public function testSetFragmentPathNoUriLexer( $fragmentPath, SiteAccess $siteAccess = null )
    {
        $fragmentRenderer = $this->getFragmentRenderer();
        $fragmentRenderer->setSiteAccess( $siteAccess );
        $fragmentRenderer->setFragmentPath( $fragmentPath );

        $refFragmentRenderer = new ReflectionObject( $fragmentRenderer );
        $refGenerateFragmentMethod = $refFragmentRenderer->getMethod( 'generateFragmentUri' );
        $refGenerateFragmentMethod->setAccessible( true );
        $controllerIdentifier = 'some:controller';
        $controllerReference = new ControllerReference( $controllerIdentifier );

        $attributes = array( '_format' => 'html', '_locale' => 'en', '_controller' => $controllerIdentifier );
        $this->assertSame(
            "$fragmentPath?_path=" . urlencode( http_build_query( $attributes, '', '&' ) ),
            $refGenerateFragmentMethod->invoke( $fragmentRenderer, $controllerReference, new Request() )
        );
    }

    public function setFragmentPathProvider()
    {
        return array(
            array(
                '/_fragment',
                null
            ),
            array(
                '/_fragment',
                new SiteAccess
            ),
        );
    }

    public function testSetFragmentPathUriLexer()
    {
        $fragmentPath = '/_fragment';
        $expectedFragmentPath = "/my_siteaccess$fragmentPath";
        $siteAccessMatcher = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer' );
        $siteAccessMatcher
            ->expects( $this->once() )
            ->method( 'analyseLink' )
            ->with( $fragmentPath )
            ->will( $this->returnValue( $expectedFragmentPath ) );
        $siteAccess = new SiteAccess( 'test', 'test', $siteAccessMatcher );

        $fragmentRenderer = $this->getFragmentRenderer();
        $fragmentRenderer->setSiteAccess( $siteAccess );
        $fragmentRenderer->setFragmentPath( $fragmentPath );

        $refFragmentRenderer = new ReflectionObject( $fragmentRenderer );
        $refGenerateFragmentMethod = $refFragmentRenderer->getMethod( 'generateFragmentUri' );
        $refGenerateFragmentMethod->setAccessible( true );
        $controllerIdentifier = 'some:controller';
        $controllerReference = new ControllerReference( $controllerIdentifier );

        $attributes = array( '_format' => 'html', '_locale' => 'en', '_controller' => $controllerIdentifier );
        $this->assertSame(
            "$expectedFragmentPath?_path=" . urlencode( http_build_query( $attributes, '', '&' ) ),
            $refGenerateFragmentMethod->invoke( $fragmentRenderer, $controllerReference, new Request() )
        );
    }

    /**
     * @return \Symfony\Component\HttpKernel\Fragment\RoutableFragmentRenderer|SiteAccess\SiteAccessAware
     */
    protected function getFragmentRenderer()
    {
        $inlineFragmentRenderer = $this->getMockBuilder( 'Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer' )
            ->disableOriginalConstructor()
            ->getMock();

        return new EsiFragmentRenderer( new Esi(), $inlineFragmentRenderer );
    }
}
