<?php
/**
 * File containing the RichText EmbedRenderer test
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType\Tests\RichText;

use eZ\Publish\Core\MVC\Symfony\FieldType\RichText\EmbedRenderer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Exception;
use PHPUnit_Framework_TestCase;

class EmbedRendererTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->repositoryMock = $this->getRepositoryMock();
        $this->controllerManagerMock = $this->getControllerManagerMock();
        $this->fragmentHandlerMock = $this->getFragmentHandlerMock();
        $this->loggerMock = $this->getLoggerMock();
        parent::setUp();
    }

    public function testRenderContent()
    {
        $contentId = 42;
        $viewType = "full";
        $parameters = array( "PARAMETERS" );
        $rendered = "RENDERED";
        $renderingStrategy = "esi";
        $contentInfoMock = $this->getContentInfoMock();
        $controllerReferenceMock = $this->getControllerReferenceMock();

        $this->repositoryMock
            ->expects( $this->once() )
            ->method( "sudo" )
            ->with( $this->isInstanceOf( "Closure" ) )
            ->will( $this->returnValue( $contentInfoMock ) );

        $this->controllerManagerMock
            ->expects( $this->once() )
            ->method( "getControllerReference" )
            ->with( $contentInfoMock, "full" )
            ->will( $this->returnValue( $controllerReferenceMock ) );

        $this->fragmentHandlerMock
            ->expects( $this->once() )
            ->method( "render" )
            ->with( $controllerReferenceMock, $renderingStrategy, $parameters )
            ->will( $this->returnValue( $rendered ) );

        $renderedContent = $this
            ->getEmbedRenderer( $renderingStrategy )
            ->renderContent( $contentId, $viewType, $parameters );

        $this->assertSame( $rendered, $renderedContent );
    }

    public function testRenderContentDefaultController()
    {
        $contentId = 42;
        $viewType = "full";
        $parameters = array( "PARAMETERS" );
        $rendered = "RENDERED";
        $renderingStrategy = "esi";
        $contentInfoMock = $this->getContentInfoMock();

        $this->repositoryMock
            ->expects( $this->once() )
            ->method( "sudo" )
            ->with( $this->isInstanceOf( "Closure" ) )
            ->will( $this->returnValue( $contentInfoMock ) );

        $this->controllerManagerMock
            ->expects( $this->once() )
            ->method( "getControllerReference" )
            ->with( $contentInfoMock, "full" )
            ->will( $this->returnValue( null ) );

        $this->fragmentHandlerMock
            ->expects( $this->once() )
            ->method( "render" )
            ->with(
                $this->isInstanceOf( "Symfony\\Component\\HttpKernel\\Controller\\ControllerReference" ),
                $renderingStrategy,
                $parameters
            )
            ->will( $this->returnValue( $rendered ) );

        $renderedContent = $this
            ->getEmbedRenderer( $renderingStrategy )
            ->renderContent( $contentId, $viewType, $parameters );

        $this->assertSame( $rendered, $renderedContent );
    }

    public function testRenderContentThrowsAccessDeniedException()
    {
        $contentId = 42;
        $viewType = "full";
        $parameters = array( "PARAMETERS" );
        $rendered = null;
        $renderingStrategy = "esi";
        $contentInfoMock = $this->getContentInfoMock();

        $this->repositoryMock
            ->expects( $this->once() )
            ->method( "sudo" )
            ->with( $this->isInstanceOf( "Closure" ) )
            ->will( $this->returnValue( $contentInfoMock ) );

        $this->controllerManagerMock
            ->expects( $this->once() )
            ->method( "getControllerReference" )
            ->with( $contentInfoMock, "full" )
            ->will( $this->returnValue( null ) );

        $this->fragmentHandlerMock
            ->expects( $this->once() )
            ->method( "render" )
            ->with(
                $this->isInstanceOf( "Symfony\\Component\\HttpKernel\\Controller\\ControllerReference" ),
                $renderingStrategy,
                $parameters
            )
            ->will( $this->throwException( new AccessDeniedException() ) );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: access denied to embed Content #{$contentId}" );

        $renderedContent = $this
            ->getEmbedRenderer( $renderingStrategy )
            ->renderContent( $contentId, $viewType, $parameters );

        $this->assertSame( $rendered, $renderedContent );
    }

    public function testRenderContentThrowsNotFoundHttpException()
    {
        $contentId = 42;
        $viewType = "full";
        $parameters = array( "PARAMETERS" );
        $rendered = null;
        $renderingStrategy = "esi";
        $contentInfoMock = $this->getContentInfoMock();

        $this->repositoryMock
            ->expects( $this->once() )
            ->method( "sudo" )
            ->with( $this->isInstanceOf( "Closure" ) )
            ->will( $this->returnValue( $contentInfoMock ) );

        $this->controllerManagerMock
            ->expects( $this->once() )
            ->method( "getControllerReference" )
            ->with( $contentInfoMock, "full" )
            ->will( $this->returnValue( null ) );

        $this->fragmentHandlerMock
            ->expects( $this->once() )
            ->method( "render" )
            ->with(
                $this->isInstanceOf( "Symfony\\Component\\HttpKernel\\Controller\\ControllerReference" ),
                $renderingStrategy,
                $parameters
            )
            ->will( $this->throwException( new NotFoundHttpException() ) );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: Content #{$contentId} not found" );

        $renderedContent = $this
            ->getEmbedRenderer( $renderingStrategy )
            ->renderContent( $contentId, $viewType, $parameters );

        $this->assertSame( $rendered, $renderedContent );
    }

    public function testRenderContentThrowsNotFoundException()
    {
        $contentId = 42;
        $viewType = "full";
        $parameters = array( "PARAMETERS" );
        $rendered = null;

        $this->repositoryMock
            ->expects( $this->once() )
            ->method( "sudo" )
            ->with( $this->isInstanceOf( "Closure" ) )
            ->will( $this->throwException( new NotFoundException( "Content", $contentId ) ) );

        $this->controllerManagerMock
            ->expects( $this->never() )
            ->method( "getControllerReference" );

        $this->fragmentHandlerMock
            ->expects( $this->never() )
            ->method( "render" );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: Content #{$contentId} not found" );

        $renderedContent = $this->getEmbedRenderer( "esi" )->renderContent( $contentId, $viewType, $parameters );

        $this->assertSame( $rendered, $renderedContent );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Rendering threw an exception
     */
    public function testRenderContentThrowsException()
    {
        $contentId = 42;
        $viewType = "full";
        $parameters = array( "PARAMETERS" );
        $rendered = null;
        $renderingStrategy = "esi";
        $contentInfoMock = $this->getContentInfoMock();

        $this->repositoryMock
            ->expects( $this->once() )
            ->method( "sudo" )
            ->with( $this->isInstanceOf( "Closure" ) )
            ->will( $this->returnValue( $contentInfoMock ) );

        $this->controllerManagerMock
            ->expects( $this->once() )
            ->method( "getControllerReference" )
            ->with( $contentInfoMock, "full" )
            ->will( $this->returnValue( null ) );

        $this->fragmentHandlerMock
            ->expects( $this->once() )
            ->method( "render" )
            ->with(
                $this->isInstanceOf( "Symfony\\Component\\HttpKernel\\Controller\\ControllerReference" ),
                $renderingStrategy,
                $parameters
            )
            ->will( $this->throwException( new Exception( "Rendering threw an exception" ) ) );

        $this->getEmbedRenderer( $renderingStrategy )->renderContent( $contentId, $viewType, $parameters );
    }

    public function testRenderLocation()
    {
        $locationId = 42;
        $viewType = "full";
        $parameters = array( "PARAMETERS" );
        $rendered = "RENDERED";
        $renderingStrategy = "esi";
        $contentInfoMock = $this->getContentInfoMock();
        $controllerReferenceMock = $this->getControllerReferenceMock();

        $this->repositoryMock
            ->expects( $this->once() )
            ->method( "sudo" )
            ->with( $this->isInstanceOf( "Closure" ) )
            ->will( $this->returnValue( $contentInfoMock ) );

        $this->controllerManagerMock
            ->expects( $this->once() )
            ->method( "getControllerReference" )
            ->with( $contentInfoMock, "full" )
            ->will( $this->returnValue( $controllerReferenceMock ) );

        $this->fragmentHandlerMock
            ->expects( $this->once() )
            ->method( "render" )
            ->with( $controllerReferenceMock, $renderingStrategy, $parameters )
            ->will( $this->returnValue( $rendered ) );

        $renderedContent = $this
            ->getEmbedRenderer( $renderingStrategy )
            ->renderLocation( $locationId, $viewType, $parameters );

        $this->assertSame( $rendered, $renderedContent );
    }

    public function testRenderLocationDefaultController()
    {
        $locationId = 42;
        $viewType = "full";
        $parameters = array( "PARAMETERS" );
        $rendered = "RENDERED";
        $renderingStrategy = "esi";
        $contentInfoMock = $this->getContentInfoMock();

        $this->repositoryMock
            ->expects( $this->once() )
            ->method( "sudo" )
            ->with( $this->isInstanceOf( "Closure" ) )
            ->will( $this->returnValue( $contentInfoMock ) );

        $this->controllerManagerMock
            ->expects( $this->once() )
            ->method( "getControllerReference" )
            ->with( $contentInfoMock, "full" )
            ->will( $this->returnValue( null ) );

        $this->fragmentHandlerMock
            ->expects( $this->once() )
            ->method( "render" )
            ->with(
                $this->isInstanceOf( "Symfony\\Component\\HttpKernel\\Controller\\ControllerReference" ),
                $renderingStrategy,
                $parameters
            )
            ->will( $this->returnValue( $rendered ) );

        $renderedContent = $this
            ->getEmbedRenderer( $renderingStrategy )
            ->renderContent( $locationId, $viewType, $parameters );

        $this->assertSame( $rendered, $renderedContent );
    }

    public function testRenderLocationThrowsAccessDeniedException()
    {
        $locationId = 42;
        $viewType = "full";
        $parameters = array( "PARAMETERS" );
        $rendered = null;
        $renderingStrategy = "esi";
        $contentInfoMock = $this->getContentInfoMock();

        $this->repositoryMock
            ->expects( $this->once() )
            ->method( "sudo" )
            ->with( $this->isInstanceOf( "Closure" ) )
            ->will( $this->returnValue( $contentInfoMock ) );

        $this->controllerManagerMock
            ->expects( $this->once() )
            ->method( "getControllerReference" )
            ->with( $contentInfoMock, "full" )
            ->will( $this->returnValue( null ) );

        $this->fragmentHandlerMock
            ->expects( $this->once() )
            ->method( "render" )
            ->with(
                $this->isInstanceOf( "Symfony\\Component\\HttpKernel\\Controller\\ControllerReference" ),
                $renderingStrategy,
                $parameters
            )
            ->will( $this->throwException( new AccessDeniedException() ) );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: access denied to embed Location #{$locationId}" );

        $renderedContent = $this
            ->getEmbedRenderer( $renderingStrategy )
            ->renderLocation( $locationId, $viewType, $parameters );

        $this->assertSame( $rendered, $renderedContent );
    }

    public function testRenderLocationThrowsNotFoundHttpException()
    {
        $locationId = 42;
        $viewType = "full";
        $parameters = array( "PARAMETERS" );
        $rendered = null;
        $renderingStrategy = "esi";
        $contentInfoMock = $this->getContentInfoMock();

        $this->repositoryMock
            ->expects( $this->once() )
            ->method( "sudo" )
            ->with( $this->isInstanceOf( "Closure" ) )
            ->will( $this->returnValue( $contentInfoMock ) );

        $this->controllerManagerMock
            ->expects( $this->once() )
            ->method( "getControllerReference" )
            ->with( $contentInfoMock, "full" )
            ->will( $this->returnValue( null ) );

        $this->fragmentHandlerMock
            ->expects( $this->once() )
            ->method( "render" )
            ->with(
                $this->isInstanceOf( "Symfony\\Component\\HttpKernel\\Controller\\ControllerReference" ),
                $renderingStrategy,
                $parameters
            )
            ->will( $this->throwException( new NotFoundHttpException() ) );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: Location #{$locationId} not found" );

        $renderedContent = $this
            ->getEmbedRenderer( $renderingStrategy )
            ->renderLocation( $locationId, $viewType, $parameters );

        $this->assertSame( $rendered, $renderedContent );
    }

    public function testRenderLocationThrowsNotFoundException()
    {
        $locationId = 42;
        $viewType = "full";
        $parameters = array( "PARAMETERS" );
        $rendered = null;

        $this->repositoryMock
            ->expects( $this->once() )
            ->method( "sudo" )
            ->with( $this->isInstanceOf( "Closure" ) )
            ->will( $this->throwException( new NotFoundException( "Location", $locationId ) ) );

        $this->controllerManagerMock
            ->expects( $this->never() )
            ->method( "getControllerReference" );

        $this->fragmentHandlerMock
            ->expects( $this->never() )
            ->method( "render" );

        $this->loggerMock
            ->expects( $this->once() )
            ->method( "error" )
            ->with( "Could not render embedded resource: Location #{$locationId} not found" );

        $renderedContent = $this->getEmbedRenderer( "esi" )->renderLocation( $locationId, $viewType, $parameters );

        $this->assertSame( $rendered, $renderedContent );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Rendering threw an exception
     */
    public function testRenderLocationThrowsException()
    {
        $locationId = 42;
        $viewType = "full";
        $parameters = array( "PARAMETERS" );
        $rendered = null;
        $renderingStrategy = "esi";
        $contentInfoMock = $this->getContentInfoMock();

        $this->repositoryMock
            ->expects( $this->once() )
            ->method( "sudo" )
            ->with( $this->isInstanceOf( "Closure" ) )
            ->will( $this->returnValue( $contentInfoMock ) );

        $this->controllerManagerMock
            ->expects( $this->once() )
            ->method( "getControllerReference" )
            ->with( $contentInfoMock, "full" )
            ->will( $this->returnValue( null ) );

        $this->fragmentHandlerMock
            ->expects( $this->once() )
            ->method( "render" )
            ->with(
                $this->isInstanceOf( "Symfony\\Component\\HttpKernel\\Controller\\ControllerReference" ),
                $renderingStrategy,
                $parameters
            )
            ->will( $this->throwException( new Exception( "Rendering threw an exception" ) ) );

        $this->getEmbedRenderer( $renderingStrategy )->renderLocation( $locationId, $viewType, $parameters );
    }

    protected function getEmbedRenderer( $renderingStrategy )
    {
        return new EmbedRenderer(
            $this->repositoryMock,
            $this->controllerManagerMock,
            $this->fragmentHandlerMock,
            $renderingStrategy,
            $this->loggerMock
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpKernel\Controller\ControllerReference
     */
    protected function getControllerReferenceMock()
    {
        return $this
            ->getMockBuilder( "Symfony\\Component\\HttpKernel\\Controller\\ControllerReference" )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected function getContentInfoMock()
    {
        return $this->getMockForAbstractClass(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo"
        );
    }

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\Repository
     */
    protected $repositoryMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\Repository
     */
    protected function getRepositoryMock()
    {
        return $this
            ->getMockBuilder( "eZ\\Publish\\Core\\Repository\\Repository" )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\Symfony\Controller\ManagerInterface
     */
    protected $controllerManagerMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\Symfony\Controller\ManagerInterface
     */
    protected function getControllerManagerMock()
    {
        return $this->getMock( "eZ\\Publish\\Core\\MVC\\Symfony\\Controller\\ManagerInterface" );
    }

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpKernel\Fragment\FragmentHandler
     */
    protected $fragmentHandlerMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpKernel\Fragment\FragmentHandler
     */
    protected function getFragmentHandlerMock()
    {
        return $this->getMock( "Symfony\\Component\\HttpKernel\\Fragment\\FragmentHandler" );
    }

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Psr\Log\LoggerInterface
     */
    protected $loggerMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Psr\Log\LoggerInterface
     */
    protected function getLoggerMock()
    {
        return $this->getMock(
            "Psr\\Log\\LoggerInterface"
        );
    }
}
