<?php
/**
 * File containing the PageControllerListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\PageControllerListener;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class PageControllerListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $controllerResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $controllerManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $pageService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var ViewControllerListener
     */
    private $controllerListener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $event;

    /**
     * @var Request
     */
    private $request;

    protected function setUp()
    {
        parent::setUp();
        $this->controllerResolver = $this->getMock( 'Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface' );
        $this->controllerManager = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\Controller\\ManagerInterface' );
        $this->pageService = $this->
            getMockBuilder( 'eZ\\Publish\\Core\\FieldType\\Page\\PageService' )
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMock( 'Psr\\Log\\LoggerInterface' );
        $this->controllerListener = new PageControllerListener( $this->controllerResolver, $this->controllerManager, $this->pageService, $this->logger );

        $this->request = new Request;
        $this->event = $this
            ->getMockBuilder( 'Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent' )
            ->disableOriginalConstructor()
            ->getMock();
        $this->event
            ->expects( $this->any() )
            ->method( 'getRequest' )
            ->will( $this->returnValue( $this->request ) );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array( KernelEvents::CONTROLLER => 'getController' ),
            $this->controllerListener->getSubscribedEvents()
        );
    }

    public function testGetControllerNonPageController()
    {
        $initialController = 'Foo::bar';
        $this->request->attributes->set( '_controller', $initialController );
        $this->pageService
            ->expects( $this->never() )
            ->method( 'loadBlock' );

        $this->controllerResolver
            ->expects( $this->never() )
            ->method( 'getControllerReference' );

        $this->event
            ->expects( $this->never() )
            ->method( 'setController' );

        $this->assertNull( $this->controllerListener->getController( $this->event ) );
    }

    public function testGetControllerInvalidParams()
    {
        // Don't add id / block to request attributes to enforce failure
        $this->request->attributes->set( '_controller', 'ez_page:viewBlock' );
        $this->pageService
            ->expects( $this->never() )
            ->method( 'loadBlock' );

        $this->controllerResolver
            ->expects( $this->never() )
            ->method( 'getControllerReference' );

        $this->logger
            ->expects( $this->once() )
            ->method( 'error' );

        $this->event
            ->expects( $this->never() )
            ->method( 'setController' );

        $this->assertNull( $this->controllerListener->getController( $this->event ) );
    }

    public function testGetControllerNoMatchedController()
    {
        $id = 123;
        $this->request->attributes->add(
            array(
                '_controller' => 'ez_page:viewBlock',
                'id' => $id
            )
        );

        $valueObject = $this->getMock( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Block' );
        $this->pageService
            ->expects( $this->once() )
            ->method( 'loadBlock' )
            ->with( $id )
            ->will( $this->returnValue( $valueObject ) );
        $this->controllerManager
            ->expects( $this->once() )
            ->method( 'getControllerReference' )
            ->will( $this->returnValue( null ) );

        $this->event
            ->expects( $this->never() )
            ->method( 'setController' );

        $this->assertNull( $this->controllerListener->getController( $this->event ) );
    }

    public function testGetControllerBlockId()
    {
        $id = 123;
        $this->request->attributes->add(
            array(
                '_controller' => 'ez_page:viewBlockById',
                'id' => $id
            )
        );

        $valueObject = $this->getMock( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Block' );
        $this->pageService
            ->expects( $this->once() )
            ->method( 'loadBlock' )
            ->with( $id )
            ->will( $this->returnValue( $valueObject ) );

        $controllerIdentifier = 'AcmeTestBundle:Default:foo';
        $controllerCallable = 'DefaultController::fooAction';
        $controllerReference = new ControllerReference( $controllerIdentifier );
        $this->controllerManager
            ->expects( $this->once() )
            ->method( 'getControllerReference' )
            ->will( $this->returnValue( $controllerReference ) );
        $this->controllerResolver
            ->expects( $this->once() )
            ->method( 'getController' )
            ->with( $this->request )
            ->will( $this->returnValue( $controllerCallable ) );
        $this->event
            ->expects( $this->once() )
            ->method( 'setController' )
            ->with( $controllerCallable );

        $this->assertNull( $this->controllerListener->getController( $this->event ) );
        $this->assertSame( $controllerIdentifier, $this->request->attributes->get( '_controller' ) );
        $this->assertSame( $valueObject, $this->request->attributes->get( 'block' ) );
    }

    public function testGetControllerBlock()
    {
        $id = 123;
        $block = $this
            ->getMockBuilder( 'eZ\Publish\Core\FieldType\Page\Parts\Block' )
            ->setConstructorArgs( array( array( 'id' => $id ) ) )
            ->getMockForAbstractClass();
        $viewType = 'block';
        $this->request->attributes->add(
            array(
                '_controller' => 'ez_page:viewBlock',
                'block' => $block
            )
        );

        $controllerIdentifier = 'AcmeTestBundle:Default:foo';
        $controllerCallable = 'DefaultController::fooAction';
        $controllerReference = new ControllerReference( $controllerIdentifier );
        $this->controllerManager
            ->expects( $this->once() )
            ->method( 'getControllerReference' )
            ->with( $block, $viewType )
            ->will( $this->returnValue( $controllerReference ) );
        $this->controllerResolver
            ->expects( $this->once() )
            ->method( 'getController' )
            ->with( $this->request )
            ->will( $this->returnValue( $controllerCallable ) );
        $this->event
            ->expects( $this->once() )
            ->method( 'setController' )
            ->with( $controllerCallable );

        $this->assertNull( $this->controllerListener->getController( $this->event ) );
        $this->assertSame( $controllerIdentifier, $this->request->attributes->get( '_controller' ) );
        $this->assertSame( $id, $this->request->attributes->get( 'id' ) );
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testGetControllerBlockUnauthorizedException()
    {
        $id = 123;
        $this->request->attributes->add(
            array(
                '_controller' => 'ez_page:viewBlock',
                'id' => $id
            )
        );

        $this->pageService
            ->expects( $this->once() )
            ->method( 'loadBlock' )
            ->with( $id )
            ->will( $this->throwException( new UnauthorizedException( 'foo', 'bar' ) ) );

        $this->controllerListener->getController( $this->event );
    }
}
