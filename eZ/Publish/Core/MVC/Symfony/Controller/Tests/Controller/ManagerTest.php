<?php
/**
 * File containing the ManagerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller\Tests\Controller;

use eZ\Publish\Core\MVC\Symfony\Controller\Manager;
use PHPUnit_Framework_TestCase;

class ManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Manager
     */
    private $controllerManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $locationMatcherFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $contentMatcherFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $blockMatcherFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->locationMatcherFactory = $this
            ->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\LocationMatcherFactory' )
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentMatcherFactory = $this
            ->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentMatcherFactory' )
            ->disableOriginalConstructor()
            ->getMock();
        $this->blockMatcherFactory = $this
            ->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\BlockMatcherFactory' )
            ->disableOriginalConstructor()
            ->getMock();
        $this->controllerManager = new Manager(
            $this->locationMatcherFactory,
            $this->contentMatcherFactory,
            $this->blockMatcherFactory,
            $this->getMock( 'Psr\\Log\\LoggerInterface' )
        );
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::getControllerReference
     */
    public function testGetControllerReferenceInvalidValueObject()
    {
        $this->controllerManager->getControllerReference( $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\ValueObject' ), 'full' );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::getControllerReference
     */
    public function testGetControllerReferenceLocationMatchFail()
    {
        $valueObject = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' );
        $viewType = 'full';

        $this->locationMatcherFactory
            ->expects( $this->once() )
            ->method( 'match' )
            ->with( $valueObject, $viewType )
            ->will( $this->returnValue( null ) );

        $this->assertNull( $this->controllerManager->getControllerReference( $valueObject, $viewType ) );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::getControllerReference
     */
    public function testGetControllerReferenceLocationNoController()
    {
        $valueObject = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' );
        $viewType = 'full';

        $this->locationMatcherFactory
            ->expects( $this->once() )
            ->method( 'match' )
            ->with( $valueObject, $viewType )
            ->will( $this->returnValue( array( 'template' => 'foo.html.twig' ) ) );

        $this->assertNull( $this->controllerManager->getControllerReference( $valueObject, $viewType ) );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::getControllerReference
     */
    public function testGetControllerReferenceLocation()
    {
        $valueObject = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' );
        $viewType = 'full';
        $controllerIdentifier = 'AcmeTestBundle:Test:foo';

        $this->locationMatcherFactory
            ->expects( $this->once() )
            ->method( 'match' )
            ->with( $valueObject, $viewType )
            ->will( $this->returnValue( array( 'controller' => $controllerIdentifier ) ) );

        $controllerReference = $this->controllerManager->getControllerReference( $valueObject, $viewType );
        $this->assertInstanceOf( 'Symfony\\Component\\HttpKernel\\Controller\\ControllerReference', $controllerReference );
        $this->assertSame( $controllerIdentifier, $controllerReference->controller );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::getControllerReference
     */
    public function testGetControllerReferenceContentInfoMatchFail()
    {
        $valueObject = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' );
        $viewType = 'full';

        $this->contentMatcherFactory
            ->expects( $this->once() )
            ->method( 'match' )
            ->with( $valueObject, $viewType )
            ->will( $this->returnValue( null ) );

        $this->assertNull( $this->controllerManager->getControllerReference( $valueObject, $viewType ) );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::getControllerReference
     */
    public function testGetControllerReferenceContentInfoNoController()
    {
        $valueObject = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' );
        $viewType = 'full';

        $this->contentMatcherFactory
            ->expects( $this->once() )
            ->method( 'match' )
            ->with( $valueObject, $viewType )
            ->will( $this->returnValue( array( 'template' => 'foo.html.twig' ) ) );

        $this->assertNull( $this->controllerManager->getControllerReference( $valueObject, $viewType ) );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::getControllerReference
     */
    public function testGetControllerReferenceContentInfo()
    {
        $valueObject = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' );
        $viewType = 'full';
        $controllerIdentifier = 'AcmeTestBundle:Test:foo';

        $this->contentMatcherFactory
            ->expects( $this->once() )
            ->method( 'match' )
            ->with( $valueObject, $viewType )
            ->will( $this->returnValue( array( 'controller' => $controllerIdentifier ) ) );

        $controllerReference = $this->controllerManager->getControllerReference( $valueObject, $viewType );
        $this->assertInstanceOf( 'Symfony\\Component\\HttpKernel\\Controller\\ControllerReference', $controllerReference );
        $this->assertSame( $controllerIdentifier, $controllerReference->controller );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::getControllerReference
     */
    public function testGetControllerReferenceBlockMatchFail()
    {
        $valueObject = $this->getMock( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Block' );
        $viewType = 'block';

        $this->blockMatcherFactory
            ->expects( $this->once() )
            ->method( 'match' )
            ->with( $valueObject, $viewType )
            ->will( $this->returnValue( null ) );

        $this->assertNull( $this->controllerManager->getControllerReference( $valueObject, $viewType ) );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::getControllerReference
     */
    public function testGetControllerReferenceBlockNoController()
    {
        $valueObject = $this->getMock( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Block' );
        $viewType = 'block';

        $this->blockMatcherFactory
            ->expects( $this->once() )
            ->method( 'match' )
            ->with( $valueObject, $viewType )
            ->will( $this->returnValue( array( 'template' => 'foo.html.twig' ) ) );

        $this->assertNull( $this->controllerManager->getControllerReference( $valueObject, $viewType ) );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Controller\Manager::getControllerReference
     */
    public function testGetControllerReferenceBlock()
    {
        $valueObject = $this->getMock( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Block' );
        $viewType = 'block';
        $controllerIdentifier = 'AcmeTestBundle:Test:foo';

        $this->blockMatcherFactory
            ->expects( $this->once() )
            ->method( 'match' )
            ->with( $valueObject, $viewType )
            ->will( $this->returnValue( array( 'controller' => $controllerIdentifier ) ) );

        $controllerReference = $this->controllerManager->getControllerReference( $valueObject, $viewType );
        $this->assertInstanceOf( 'Symfony\\Component\\HttpKernel\\Controller\\ControllerReference', $controllerReference );
        $this->assertSame( $controllerIdentifier, $controllerReference->controller );
    }
}
