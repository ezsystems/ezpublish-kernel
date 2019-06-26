<?php

/**
 * File containing the ViewControllerListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener;
use eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilder;
use eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilderRegistry;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ViewControllerListenerTest extends TestCase
{
    /** @var \Symfony\Component\HttpKernel\Controller\ControllerResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $controllerResolver;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var ViewControllerListener */
    private $controllerListener;

    /** @var \Symfony\Component\HttpKernel\Event\FilterControllerEvent|\PHPUnit\Framework\MockObject\MockObject */
    private $event;

    /** @var Request */
    private $request;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilderRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $viewBuilderRegistry;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Configurator|\PHPUnit\Framework\MockObject\MockObject */
    private $viewConfigurator;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilder|\PHPUnit\Framework\MockObject\MockObject */
    private $viewBuilderMock;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $eventDispatcher;

    protected function setUp()
    {
        parent::setUp();
        $this->controllerResolver = $this->createMock(ControllerResolverInterface::class);
        $this->viewBuilderRegistry = $this->createMock(ViewBuilderRegistry::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->controllerListener = new ViewControllerListener(
            $this->controllerResolver,
            $this->viewBuilderRegistry,
            $this->eventDispatcher,
            $this->logger
        );

        $this->request = new Request();
        $this->event = $this->createMock(FilterControllerArgumentsEvent::class);
        $this->event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        $this->viewBuilderMock = $this->createMock(ViewBuilder::class);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [KernelEvents::CONTROLLER => ['getController', 10]],
            $this->controllerListener->getSubscribedEvents()
        );
    }

    public function testGetControllerNoBuilder()
    {
        $initialController = 'Foo::bar';
        $this->request->attributes->set('_controller', $initialController);

        $this->viewBuilderRegistry
            ->expects($this->once())
            ->method('getFromRegistry')
            ->with('Foo::bar')
            ->willReturn(null);

        $this->event
            ->expects($this->never())
            ->method('setController');

        $this->controllerListener->getController($this->event);
    }

    public function testGetControllerWithClosure()
    {
        $initialController = function () {};
        $this->request->attributes->set('_controller', $initialController);

        $this->viewBuilderRegistry
            ->expects($this->once())
            ->method('getFromRegistry')
            ->with($initialController)
            ->willReturn(null);

        $this->event
            ->expects($this->never())
            ->method('setController');

        $this->controllerListener->getController($this->event);
    }

    public function testGetControllerMatchedView()
    {
        $id = 123;
        $viewType = 'full';

        $templateIdentifier = 'FooBundle:full:template.twig.html';
        $customController = 'FooBundle::bar';

        $this->request->attributes->add(
            [
                '_controller' => 'ez_content:viewLocation',
                'locationId' => $id,
                'viewType' => $viewType,
            ]
        );

        $this->viewBuilderRegistry
            ->expects($this->once())
            ->method('getFromRegistry')
            ->will($this->returnValue($this->viewBuilderMock));

        $viewObject = new ContentView($templateIdentifier);
        $viewObject->setControllerReference(new ControllerReference($customController));

        $this->viewBuilderMock
            ->expects($this->once())
            ->method('buildView')
            ->will($this->returnValue($viewObject));

        $this->event
            ->expects($this->once())
            ->method('setController');

        $this->controllerResolver
            ->expects($this->once())
            ->method('getController')
            ->will($this->returnValue(function () {}));

        $this->controllerListener->getController($this->event);
        $this->assertEquals($customController, $this->request->attributes->get('_controller'));

        $expectedView = new ContentView();
        $expectedView->setTemplateIdentifier($templateIdentifier);
        $expectedView->setControllerReference(new ControllerReference($customController));

        $this->assertEquals($expectedView, $this->request->attributes->get('view'));
    }
}
