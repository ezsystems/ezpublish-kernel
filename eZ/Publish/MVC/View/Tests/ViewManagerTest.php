<?php
/**
 * File containing the ViewManagerTest class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\View\Tests;

use eZ\Publish\MVC\View\Manager,
    eZ\Publish\MVC\View\ContentView;

/**
 * @group mvc
 */
class ViewManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\MVC\View\Manager
     */
    private $viewManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $templateEngineMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcherMock;

    protected  function setUp()
    {
        parent::setUp();
        $this->templateEngineMock = $this->getMock( 'Symfony\\Component\\Templating\\EngineInterface' );
        $this->eventDispatcherMock = $this->getMock( 'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface' );
        $this->viewManager = new Manager(
            $this->templateEngineMock,
            $this->eventDispatcherMock
        );
    }

    /**
     * @covers \eZ\Publish\MVC\View\Manager::addViewProvider
     * @covers \eZ\Publish\MVC\View\Manager::getAllViewProviders
     */
    public function testAddViewProvider()
    {
        self::assertSame( array(), $this->viewManager->getAllViewProviders() );
        $viewProvider = $this->getMock( 'eZ\\Publish\\MVC\\View\\ContentViewProvider' );
        $this->viewManager->addViewProvider( $viewProvider );
        self::assertSame( array( $viewProvider ), $this->viewManager->getAllViewProviders() );
    }

    /**
     * @covers \eZ\Publish\MVC\View\Manager::addViewProvider
     * @covers \eZ\Publish\MVC\View\Manager::sortViewProviders
     * @covers \eZ\Publish\MVC\View\Manager::getAllViewProviders
     */
    public function testViewProvidersPriority()
    {
        list( $high, $medium, $low ) = $this->createViewProviderMocks();
        $this->viewManager->addViewProvider( $medium, 33 );
        $this->viewManager->addViewProvider( $high, 100 );
        $this->viewManager->addViewProvider( $low, -100 );
        self::assertSame(
            array( $high, $medium, $low ),
            $this->viewManager->getAllViewProviders()
        );
    }

    /**
     * @covers \eZ\Publish\MVC\View\Manager::renderContent
     * @covers \eZ\Publish\MVC\View\Manager::renderContentView
     */
    public function testRenderContent()
    {
        $viewProvider = $this->getMock( 'eZ\\Publish\\MVC\\View\\ContentViewProvider' );
        $this->viewManager->addViewProvider( $viewProvider );

        // Configuring content mocks
        $content = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content' );
        $versionInfo = $this->getMock( 'eZ\\Publish\\Core\\Repository\\Values\\Content\\VersionInfo' );
        $contentInfo = $this->getMock( 'eZ\\Publish\\Core\\Repository\\Values\\Content\\ContentInfo' );
        $content
            ->expects( $this->once() )
            ->method( 'getVersionInfo' )
            ->will( $this->returnValue( $versionInfo ) )
        ;
        $versionInfo
            ->expects( $this->once() )
            ->method( 'getContentInfo' )
            ->will( $this->returnValue( $contentInfo ) )
        ;

        // Configuring view provider behaviour
        $templateIdentifier = 'foo:bar:baz';
        $params = array( 'foo' => 'bar' );
        $viewProvider
            ->expects( $this->once() )
            ->method( 'getViewForContent' )
            ->with( $contentInfo, 'customViewType' )
            ->will(
                $this->returnValue(
                    new ContentView( $templateIdentifier, $params )
                )
            )
        ;

        // Configuring template engine behaviour
        $expectedTemplateResult = 'This is content rendering';
        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( 'render' )
            ->with( $templateIdentifier, $params + array( 'content' => $content ) )
            ->will( $this->returnValue( $expectedTemplateResult ) )
        ;

        self::assertSame( $expectedTemplateResult, $this->viewManager->renderContent( $content, 'customViewType' ) );
    }

    /**
     * @covers \eZ\Publish\MVC\View\Manager::renderContent
     * @covers \eZ\Publish\MVC\View\Manager::renderContentView
     */
    public function testRenderContentWithClosure()
    {
        $viewProvider = $this->getMock( 'eZ\\Publish\\MVC\\View\\ContentViewProvider' );
        $this->viewManager->addViewProvider( $viewProvider );

        // Configuring content mocks
        $content = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content' );
        $versionInfo = $this->getMock( 'eZ\\Publish\\Core\\Repository\\Values\\Content\\VersionInfo' );
        $contentInfo = $this->getMock( 'eZ\\Publish\\Core\\Repository\\Values\\Content\\ContentInfo' );
        $content
            ->expects( $this->once() )
            ->method( 'getVersionInfo' )
            ->will( $this->returnValue( $versionInfo ) )
        ;
        $versionInfo
            ->expects( $this->once() )
            ->method( 'getContentInfo' )
            ->will( $this->returnValue( $contentInfo ) )
        ;

        // Configuring view provider behaviour
        $closure = function ( $params )
        {
            return serialize( array_keys( $params ) );
        };
        $params = array( 'foo' => 'bar' );
        $viewProvider
            ->expects( $this->once() )
            ->method( 'getViewForContent' )
            ->with( $contentInfo )
            ->will(
            $this->returnValue(
                new ContentView( $closure, $params )
            )
        )
        ;

        // Configuring template engine behaviour
        $params += array( 'content' => $content );
        $expectedTemplateResult = serialize( array_keys( $params ) );
        $this->templateEngineMock
            ->expects( $this->never() )
            ->method( 'render' )
        ;

        self::assertSame( $expectedTemplateResult, $this->viewManager->renderContent( $content ) );
    }

    /**
     * @covers \eZ\Publish\MVC\View\Manager::renderLocation
     * @covers \eZ\Publish\MVC\View\Manager::renderContentView
     */
    public function testRenderLocation()
    {
        $viewProvider = $this->getMock( 'eZ\\Publish\\MVC\\View\\ContentViewProvider' );
        $this->viewManager->addViewProvider( $viewProvider );

        $location = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' );
        $content = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content' );

        // Configuring view provider behaviour
        $templateIdentifier = 'foo:bar:baz';
        $params = array( 'foo' => 'bar' );
        $viewProvider
            ->expects( $this->once() )
            ->method( 'getViewForLocation' )
            ->with( $location, 'customViewType' )
            ->will(
            $this->returnValue(
                new ContentView( $templateIdentifier, $params )
            )
        )
        ;

        // Configuring template engine behaviour
        $expectedTemplateResult = 'This is location rendering';
        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( 'render' )
            ->with( $templateIdentifier, $params + array( 'location' => $location, 'content' => $content ) )
            ->will( $this->returnValue( $expectedTemplateResult ) )
        ;

        self::assertSame( $expectedTemplateResult, $this->viewManager->renderLocation( $location, $content, 'customViewType' ) );
    }

    /**
     * @covers \eZ\Publish\MVC\View\Manager::renderLocation
     * @covers \eZ\Publish\MVC\View\Manager::renderContentView
     */
    public function testRenderLocationWithClosure()
    {
        $viewProvider = $this->getMock( 'eZ\\Publish\\MVC\\View\\ContentViewProvider' );
        $this->viewManager->addViewProvider( $viewProvider );

        $location = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' );
        $content = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content' );

        // Configuring view provider behaviour
        $closure = function ( $params )
        {
            return serialize( array_keys( $params ) );
        };
        $params = array( 'foo' => 'bar' );
        $viewProvider
            ->expects( $this->once() )
            ->method( 'getViewForLocation' )
            ->with( $location )
            ->will(
            $this->returnValue(
                new ContentView( $closure, $params )
            )
        )
        ;

        // Configuring template engine behaviour
        $params += array( 'location' => $location, 'content' => $content );
        $expectedTemplateResult = serialize( array_keys( $params ) );
        $this->templateEngineMock
            ->expects( $this->never() )
            ->method( 'render' )
        ;

        self::assertSame( $expectedTemplateResult, $this->viewManager->renderLocation( $location, $content ) );
    }

    private function createViewProviderMocks()
    {
        return array(
            $this->getMock( 'eZ\\Publish\\MVC\\View\\ContentViewProvider' ),
            $this->getMock( 'eZ\\Publish\\MVC\\View\\ContentViewProvider' ),
            $this->getMock( 'eZ\\Publish\\MVC\\View\\ContentViewProvider' ),
        );
    }
}
