<?php

/**
 * File containing the ControllerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller\Tests;

use eZ\Publish\Core\MVC\Symfony\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @mvc
 */
class ControllerTest extends TestCase
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Controller\Controller */
    protected $controller;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $templateEngineMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $containerMock;

    protected function setUp()
    {
        $this->templateEngineMock = $this->createMock(EngineInterface::class);
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->controller = $this->getMockForAbstractClass(Controller::class);
        $this->controller->setContainer($this->containerMock);
        $this->containerMock
            ->expects($this->any())
            ->method('get')
            ->with('templating')
            ->will($this->returnValue($this->templateEngineMock));
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Controller\Controller::render
     */
    public function testRender()
    {
        $view = 'some:valid:view.html.twig';
        $params = ['foo' => 'bar', 'truc' => 'muche'];
        $tplResult = "I'm a template result";
        $this->templateEngineMock
            ->expects($this->once())
            ->method('render')
            ->with($view, $params)
            ->will($this->returnValue($tplResult));
        $response = $this->controller->render($view, $params);
        self::assertInstanceOf(Response::class, $response);
        self::assertSame($tplResult, $response->getContent());
    }

    public function testRenderWithResponse()
    {
        $response = new Response();
        $view = 'some:valid:view.html.twig';
        $params = ['foo' => 'bar', 'truc' => 'muche'];
        $tplResult = "I'm a template result";
        $this->templateEngineMock
            ->expects($this->once())
            ->method('render')
            ->with($view, $params)
            ->will($this->returnValue($tplResult));

        self::assertSame($response, $this->controller->render($view, $params, $response));
        self::assertSame($tplResult, $response->getContent());
    }
}
