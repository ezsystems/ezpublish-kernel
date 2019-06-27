<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Tests\Renderer;

use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use eZ\Publish\Core\MVC\Symfony\View\Renderer\TemplateRenderer;
use eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent;
use PHPUnit\Framework\TestCase;

class TemplateRendererTest extends TestCase
{
    /** @var \eZ\Publish\Core\MVC\Symfony\View\Renderer\TemplateRenderer */
    private $renderer;

    /** @var \Symfony\Component\Templating\EngineInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $templateEngineMock;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $eventDispatcherMock;

    public function setUp()
    {
        $this->templateEngineMock = $this->createMock(EngineInterface::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->renderer = new TemplateRenderer(
            $this->templateEngineMock,
            $this->eventDispatcherMock
        );
    }

    public function testRender()
    {
        $view = $this->createView();
        $view->setTemplateIdentifier('path/to/template.html.twig');

        $this->eventDispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                MVCEvents::PRE_CONTENT_VIEW,
                $this->isInstanceOf(PreContentViewEvent::class)
            );

        $this->templateEngineMock
            ->expects($this->once())
            ->method('render')
            ->with(
                'path/to/template.html.twig',
                $view->getParameters()
            );

        $this->renderer->render($view);
    }

    /**
     * @expectedException \eZ\Publish\Core\MVC\Exception\NoViewTemplateException
     */
    public function testRenderNoViewTemplate()
    {
        $this->renderer->render($this->createView());
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\View\View
     */
    protected function createView()
    {
        $view = new ContentView();

        return $view;
    }
}
