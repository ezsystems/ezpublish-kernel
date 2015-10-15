<?php

/**
 * File containing the ViewControllerListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class ViewControllerListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\HttpKernel\Controller\ControllerResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $controllerResolver;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var ViewControllerListener
     */
    private $controllerListener;

    /**
     * @var \Symfony\Component\HttpKernel\Event\FilterControllerEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $event;

    /**
     * @var Request
     */
    private $request;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilderRegistry|\PHPUnit_Framework_MockObject_MockObject */
    private $viewBuilderRegistry;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Configurator|\PHPUnit_Framework_MockObject_MockObject */
    private $viewConfigurator;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilder|\PHPUnit_Framework_MockObject_MockObject */
    private $viewBuilderMock;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $eventDispatcher;

    protected function setUp()
    {
        parent::setUp();
        $this->controllerResolver = $this->getMock('Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface');
        $this->viewBuilderRegistry = $this->getMock('eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilderRegistry');
        $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->logger = $this->getMock('Psr\\Log\\LoggerInterface');
        $this->controllerListener = new ViewControllerListener(
            $this->controllerResolver,
            $this->viewBuilderRegistry,
            $this->eventDispatcher,
            $this->logger
        );

        $this->request = new Request();
        $this->event = $this
            ->getMockBuilder('Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $this->event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        $this->viewBuilderMock = $this->getMock('eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilder');
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array(KernelEvents::CONTROLLER => array('getController', 10)),
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

    public function testGetControllerMatchedView()
    {
        $id = 123;
        $viewType = 'full';

        $templateIdentifier = 'FooBundle:full:template.twig.html';
        $customController = 'FooBundle::bar';

        $this->request->attributes->add(
            array(
                '_controller' => 'ez_content:viewLocation',
                'locationId' => $id,
                'viewType' => $viewType,
            )
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

        $this->controllerListener->getController($this->event);
        $this->assertEquals($customController, $this->request->attributes->get('_controller'));

        $expectedView = new ContentView();
        $expectedView->setTemplateIdentifier($templateIdentifier);
        $expectedView->setControllerReference(new ControllerReference($customController));

        $this->assertEquals($expectedView, $this->request->attributes->get('view'));
    }
}
