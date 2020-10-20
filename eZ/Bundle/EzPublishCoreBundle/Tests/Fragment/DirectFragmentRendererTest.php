<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Fragment;

use eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener;
use eZ\Bundle\EzPublishCoreBundle\Fragment\DirectFragmentRenderer;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\Templating\Exception\InvalidResponseException;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\Renderer\TemplateRenderer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;
use Symfony\Component\HttpKernel\KernelInterface;

final class DirectFragmentRendererTest extends TestCase
{
    public function testSubRequestBuilding(): void
    {
        $controllerResolver = $this->getControllerResolverInterfaceMock();
        $controllerResolver
            ->expects($this->any())
            ->method('getController')
            ->with($this->callback(function (Request $request) {
                $this->assertEquals('/_fragment', $request->getPathInfo());
                $this->assertEquals('some::controller', $request->attributes->get('_controller'));
                $this->assertEquals('attribute_value', $request->attributes->get('some'));
                $this->assertEquals('else', $request->attributes->get('something'));
                $this->assertInstanceOf(SiteAccess::class, $request->attributes->get('siteaccess'));
                $this->assertEquals('test', $request->attributes->get('siteaccess')->name);

                return true;
            }))
            ->willReturn(function () {
                return 'rendered_response';
            });

        $controllerReference = new ControllerReference(
            'some::controller',
            [
                'some' => 'attribute_value',
                'something' => 'else',
            ]
        );

        $request = new Request();
        $request->attributes->set('siteaccess', new SiteAccess('test'));

        $controllerResolver
            ->method('getController')
            ->willReturn(function () {
                return new Response('response_body');
            });

        $directFragmentRenderer = $this->getDirectFragmentRenderer($controllerResolver);
        $response = $directFragmentRenderer->render($controllerReference, $request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('rendered_response', $response->getContent());
    }

    public function testControllerResponse(): void
    {
        $controllerResolver = $this->getControllerResolverInterfaceMock();

        $controllerResolver
            ->method('getController')
            ->willReturn(function () {
                return new Response('response_body');
            });

        $directFragmentRenderer = $this->getDirectFragmentRenderer($controllerResolver);
        $response = $directFragmentRenderer->render('', new Request(), []);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('response_body', $response->getContent());
    }

    public function testControllerViewResponse(): void
    {
        $contentView = new ContentView();
        $contentView->setTemplateIdentifier('template_identifier');

        $controllerResolverMock = $this->getControllerResolverInterfaceMock();
        $controllerResolverMock
            ->method('getController')
            ->willReturn(function (...$args) use ($contentView) {
                $contentView->setParameters($args);

                return $contentView;
            });

        $templateRendererMock = $this->getTemplateRendererMock();
        $templateRendererMock
            ->expects($this->once())
            ->method('render')
            ->with($contentView)
            ->willReturn('rendered_' . $contentView->getTemplateIdentifier());

        $directFragmentRenderer = $this->getDirectFragmentRenderer(
            $controllerResolverMock,
            $templateRendererMock
        );
        $response = $directFragmentRenderer->render('', new Request(), []);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('rendered_template_identifier', $response->getContent());
    }

    public function testControllerStringResponse(): void
    {
        $controllerResolver = $this->getControllerResolverInterfaceMock();

        $controllerResolver
            ->method('getController')
            ->willReturn(function () {
                return 'some_prerendered_response';
            });

        $directFragmentRenderer = $this->getDirectFragmentRenderer($controllerResolver);
        $response = $directFragmentRenderer->render('', new Request(), []);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('some_prerendered_response', $response->getContent());
    }

    public function testControllerUnhandledStringResponse(): void
    {
        $controllerResolver = $this->getControllerResolverInterfaceMock();

        $controllerResolver
            ->method('getController')
            ->willReturn(function (...$args) {
                return ['some_array' => $args];
            });

        $directFragmentRenderer = $this->getDirectFragmentRenderer($controllerResolver);

        $this->expectException(InvalidResponseException::class);

        $directFragmentRenderer->render('', new Request(), []);
    }

    /**
     * @return \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getControllerResolverInterfaceMock(): ControllerResolverInterface
    {
        return $this->createMock(ControllerResolverInterface::class);
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\View\Renderer\TemplateRenderer|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getTemplateRendererMock(): TemplateRenderer
    {
        return $this->createMock(TemplateRenderer::class);
    }

    private function getDirectFragmentRenderer(
        ControllerResolverInterface $controllerResolver,
        ?TemplateRenderer $templateRenderer = null
    ): DirectFragmentRenderer {
        return new DirectFragmentRenderer(
            $this->createMock(KernelInterface::class),
            $this->createMock(ViewControllerListener::class),
            $controllerResolver,
            new ArgumentMetadataFactory(),
            new RequestAttributeValueResolver(),
            $templateRenderer ?? $this->getTemplateRendererMock()
        );
    }
}
