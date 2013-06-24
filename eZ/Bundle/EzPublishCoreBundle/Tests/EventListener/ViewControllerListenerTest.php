<?php
/**
 * File containing the ViewControllerListenerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class ViewControllerListenerTest extends \PHPUnit_Framework_TestCase
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
    private $repository;

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
        $this->repository = $this->getMock( 'eZ\\Publish\\API\\Repository\\Repository' );
        $this->logger = $this->getMock( 'Psr\\Log\\LoggerInterface' );
        $this->controllerListener = new ViewControllerListener( $this->controllerResolver, $this->controllerManager, $this->repository, $this->logger );

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

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener::__construct
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener::getController
     */
    public function testGetControllerNonViewController()
    {
        $initialController = 'Foo::bar';
        $this->request->attributes->set( '_controller', $initialController );
        $this->repository
            ->expects( $this->never() )
            ->method( 'getLocationService' );
        $this->repository
            ->expects( $this->never() )
            ->method( 'getContentService' );

        $this->controllerResolver
            ->expects( $this->never() )
            ->method( 'getControllerReference' );

        $this->event
            ->expects( $this->never() )
            ->method( 'setController' );

        $this->assertNull( $this->controllerListener->getController( $this->event ) );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener::__construct
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener::getController
     */
    public function testGetControllerInvalidParams()
    {
        // Don't add locationId / contentId to request attributes to enforce failure
        $this->request->attributes->set( '_controller', 'ez_content:viewLocation' );
        $this->repository
            ->expects( $this->never() )
            ->method( 'getLocationService' );
        $this->repository
            ->expects( $this->never() )
            ->method( 'getContentService' );

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

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener::__construct
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener::getController
     */
    public function testGetControllerNoMatchedController()
    {
        $id = 123;
        $viewType = 'full';
        $this->request->attributes->add(
            array(
                '_controller' => 'ez_content:viewLocation',
                'locationId' => $id,
                'viewType' => $viewType
            )
        );

        $locationServiceMock = $this->getMock( 'eZ\\Publish\\API\\Repository\\LocationService' );
        $valueObject = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' );
        $locationServiceMock
            ->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $id )
            ->will( $this->returnValue( $valueObject ) );
        $this->repository
            ->expects( $this->once() )
            ->method( 'getLocationService' )
            ->will( $this->returnValue( $locationServiceMock ) );
        $this->controllerManager
            ->expects( $this->once() )
            ->method( 'getControllerReference' )
            ->will( $this->returnValue( null ) );

        $this->event
            ->expects( $this->never() )
            ->method( 'setController' );

        $this->assertNull( $this->controllerListener->getController( $this->event ) );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener::__construct
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener::getController
     */
    public function testGetControllerLocation()
    {
        $id = 123;
        $viewType = 'full';
        $this->request->attributes->add(
            array(
                '_controller' => 'ez_content:viewLocation',
                'locationId' => $id,
                'viewType' => $viewType
            )
        );

        $locationServiceMock = $this->getMock( 'eZ\\Publish\\API\\Repository\\LocationService' );
        $valueObject = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' );
        $locationServiceMock
            ->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $id )
            ->will( $this->returnValue( $valueObject ) );
        $this->repository
            ->expects( $this->once() )
            ->method( 'getLocationService' )
            ->will( $this->returnValue( $locationServiceMock ) );

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
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener::__construct
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener::getController
     */
    public function testGetControllerContentInfo()
    {
        $id = 123;
        $viewType = 'full';
        $this->request->attributes->add(
            array(
                '_controller' => 'ez_content:viewLocation',
                'contentId' => $id,
                'viewType' => $viewType
            )
        );

        $contentServiceMock = $this->getMock( 'eZ\\Publish\\API\\Repository\\ContentService' );
        $valueObject = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' );
        $contentServiceMock
            ->expects( $this->once() )
            ->method( 'loadContentInfo' )
            ->with( $id )
            ->will( $this->returnValue( $valueObject ) );
        $this->repository
            ->expects( $this->once() )
            ->method( 'getContentService' )
            ->will( $this->returnValue( $contentServiceMock ) );

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
    }
}
