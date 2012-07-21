<?php
/**
 * File containing the ControllerTest class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\Controller\Tests;

use Symfony\Component\HttpFoundation\Response;

/**
 * @mvc
 */
class ControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\MVC\Controller\Controller
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateEngineMock;

    protected function setUp()
    {
        $this->templateEngineMock = $this->getMock( 'Symfony\\Component\\Templating\\EngineInterface' );
        $this->controller = $this->getMockForAbstractClass( 'eZ\\Publish\\MVC\\Controller\\Controller' );
        $this->controller->setTemplateEngine( $this->templateEngineMock );
    }
    /**
     * @covers \eZ\Publish\MVC\Controller::setTemplateEngine
     * @covers \eZ\Publish\MVC\Controller::render
     */
    public function testRender()
    {
        $view = 'some:valid:view.html.twig';
        $params = array( 'foo' => 'bar', 'truc' => 'muche' );
        $tplResult = "I'm a template result";
        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( 'render' )
            ->with( $view, $params )
            ->will( $this->returnValue( $tplResult ) )
        ;
        $response = $this->controller->render( $view, $params );
        self::assertInstanceOf( 'Symfony\\Component\\HttpFoundation\\Response', $response );
        self::assertSame( $tplResult, $response->getContent() );
    }

    public function testRenderWithResponse()
    {
        $response = new Response();
        $view = 'some:valid:view.html.twig';
        $params = array( 'foo' => 'bar', 'truc' => 'muche' );
        $tplResult = "I'm a template result";
        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( 'render' )
            ->with( $view, $params )
            ->will( $this->returnValue( $tplResult ) )
        ;

        self::assertSame( $response, $this->controller->render( $view, $params, $response ) );
        self::assertSame( $tplResult, $response->getContent() );
    }
}
