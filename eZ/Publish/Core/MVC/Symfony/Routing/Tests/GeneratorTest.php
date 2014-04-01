<?php
/**
 * File containing the GeneratorTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Routing\Tests;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Symfony\Component\Routing\RequestContext;
use PHPUnit_Framework_TestCase;

class GeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $siteAccessRouter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    protected function setUp()
    {
        parent::setUp();
        $this->siteAccessRouter = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessRouterInterface' );
        $this->logger = $this->getMock( 'Psr\\Log\\LoggerInterface' );
        $this->generator = $this->getMockForAbstractClass( 'eZ\Publish\Core\MVC\Symfony\Routing\Generator' );
        $this->generator->setSiteAccessRouter( $this->siteAccessRouter );
        $this->generator->setLogger( $this->logger );
    }

    public function generateProvider()
    {
        return array(
            array( 'foo_bar', array(), false ),
            array( 'foo_bar', array(), true ),
            array( 'foo_bar', array( 'some' => 'thing' ), true ),
            array( new Location(), array(), false ),
            array( new Location(), array(), true ),
            array( new Location(), array( 'some' => 'thing' ), true ),
            array( new \stdClass(), array(), false ),
            array( new \stdClass(), array(), true ),
            array( new \stdClass(), array( 'some' => 'thing' ), true ),
        );
    }

    /**
     * @dataProvider generateProvider
     */
    public function testSimpleGenerate( $urlResource, array $parameters, $absolute )
    {
        $matcher = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\URILexer' );
        $this->generator->setSiteAccess( new SiteAccess( 'test', 'fake', $matcher ) );

        $baseUrl = '/base/url';
        $requestContext = new RequestContext( $baseUrl );
        $this->generator->setRequestContext( $requestContext );

        $uri = '/some/thing';
        $this->generator
            ->expects( $this->once() )
            ->method( 'doGenerate' )
            ->with( $urlResource, $parameters )
            ->will( $this->returnValue( $uri ) );

        $fullUri = $baseUrl . $uri;
        $matcher
            ->expects( $this->once() )
            ->method( 'analyseLink' )
            ->with( $fullUri )
            ->will( $this->returnValue( $fullUri ) );

        if ( $absolute )
        {
            $fullUri = $requestContext->getScheme() . '://' . $requestContext->getHost() . $baseUrl . $uri;
        }

        $this->assertSame( $fullUri, $this->generator->generate( $urlResource, $parameters, $absolute ) );
    }

    /**
     * @dataProvider generateProvider
     */
    public function testGenerateWithSiteAccessNoReverseMatch( $urlResource, array $parameters, $absolute )
    {
        $matcher = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\URILexer' );
        $this->generator->setSiteAccess( new SiteAccess( 'test', 'test', $matcher ) );

        $baseUrl = '/base/url';
        $requestContext = new RequestContext( $baseUrl );
        $this->generator->setRequestContext( $requestContext );

        $uri = '/some/thing';
        $this->generator
            ->expects( $this->once() )
            ->method( 'doGenerate' )
            ->with( $urlResource, $parameters )
            ->will( $this->returnValue( $uri ) );

        $fullUri = $baseUrl . $uri;
        $matcher
            ->expects( $this->once() )
            ->method( 'analyseLink' )
            ->with( $fullUri )
            ->will( $this->returnValue( $fullUri ) );

        if ( $absolute )
        {
            $fullUri = $requestContext->getScheme() . '://' . $requestContext->getHost() . $baseUrl . $uri;
        }

        $siteAccessName = 'fake';
        $this->siteAccessRouter
            ->expects( $this->once() )
            ->method( 'matchByName' )
            ->with( $siteAccessName )
            ->will( $this->returnValue( null ) );
        $this->logger
            ->expects( $this->once() )
            ->method( 'notice' );
        $this->assertSame( $fullUri, $this->generator->generate( $urlResource, $parameters + array( 'siteaccess' => $siteAccessName ), $absolute ) );
    }
}
