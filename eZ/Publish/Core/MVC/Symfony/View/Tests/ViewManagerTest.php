<?php
/**
 * File containing the ViewManagerTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests;

use eZ\Publish\Core\MVC\Symfony\View\Manager;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use PHPUnit_Framework_TestCase;

/**
 * @group mvc
 */
class ViewManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\Manager
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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolverMock;

    private $viewBaseLayout = 'EzPublishCoreBundle::viewbase.html.twig';

    protected function setUp()
    {
        parent::setUp();
        $this->templateEngineMock = $this->getMock( 'Symfony\\Component\\Templating\\EngineInterface' );
        $this->eventDispatcherMock = $this->getMock( 'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface' );
        $this->repositoryMock = $this->getMockBuilder( 'eZ\\Publish\\Core\\Repository\\DomainLogic\\Repository' )
            ->disableOriginalConstructor()
            ->getMock();
        $this->configResolverMock = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
        $this->viewManager = new Manager(
            $this->templateEngineMock,
            $this->eventDispatcherMock,
            $this->repositoryMock,
            $this->configResolverMock,
            $this->viewBaseLayout
        );
    }

    public function testAddContentViewProvider()
    {
        self::assertSame( array(), $this->viewManager->getAllContentViewProviders() );
        $viewProvider = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Content' );
        $this->viewManager->addContentViewProvider( $viewProvider );
        self::assertSame( array( $viewProvider ), $this->viewManager->getAllContentViewProviders() );
    }

    public function testAddLocationViewProvider()
    {
        self::assertSame( array(), $this->viewManager->getAllLocationViewProviders() );
        $viewProvider = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Location' );
        $this->viewManager->addLocationViewProvider( $viewProvider );
        self::assertSame( array( $viewProvider ), $this->viewManager->getAllLocationViewProviders() );
    }

    public function testContentViewProvidersPriority()
    {
        list( $high, $medium, $low ) = $this->createContentViewProviderMocks();
        $this->viewManager->addContentViewProvider( $medium, 33 );
        $this->viewManager->addContentViewProvider( $high, 100 );
        $this->viewManager->addContentViewProvider( $low, -100 );
        self::assertSame(
            array( $high, $medium, $low ),
            $this->viewManager->getAllContentViewProviders()
        );
    }

    public function testLocationViewProvidersPriority()
    {
        list( $high, $medium, $low ) = $this->createLocationViewProviderMocks();
        $this->viewManager->addLocationViewProvider( $medium, 33 );
        $this->viewManager->addLocationViewProvider( $high, 100 );
        $this->viewManager->addLocationViewProvider( $low, -100 );
        self::assertSame(
            array( $high, $medium, $low ),
            $this->viewManager->getAllLocationViewProviders()
        );
    }

    public function testRenderContent()
    {
        $viewProvider = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Content' );
        $this->viewManager->addContentViewProvider( $viewProvider );

        // Configuring content mocks
        $content = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content' );
        $versionInfo = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo' );
        $contentInfo = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' );
        $content
            ->expects( $this->once() )
            ->method( 'getVersionInfo' )
            ->will( $this->returnValue( $versionInfo ) );
        $versionInfo
            ->expects( $this->once() )
            ->method( 'getContentInfo' )
            ->will( $this->returnValue( $contentInfo ) );

        // Configuring view provider behaviour
        $templateIdentifier = 'foo:bar:baz';
        $params = array( 'foo' => 'bar' );
        $viewProvider
            ->expects( $this->once() )
            ->method( 'getView' )
            ->with( $contentInfo, 'customViewType' )
            ->will(
                $this->returnValue(
                    new ContentView( $templateIdentifier, $params )
                )
            );

        // Configuring template engine behaviour
        $expectedTemplateResult = 'This is content rendering';
        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( 'render' )
            ->with( $templateIdentifier, $params + array( 'content' => $content, 'viewbaseLayout' => $this->viewBaseLayout ) )
            ->will( $this->returnValue( $expectedTemplateResult ) );

        self::assertSame( $expectedTemplateResult, $this->viewManager->renderContent( $content, 'customViewType' ) );
    }

    public function testRenderContentWithClosure()
    {
        $viewProvider = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Content' );
        $this->viewManager->addContentViewProvider( $viewProvider );

        // Configuring content mocks
        $content = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content' );
        $versionInfo = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo' );
        $contentInfo = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' );
        $content
            ->expects( $this->once() )
            ->method( 'getVersionInfo' )
            ->will( $this->returnValue( $versionInfo ) );
        $versionInfo
            ->expects( $this->once() )
            ->method( 'getContentInfo' )
            ->will( $this->returnValue( $contentInfo ) );

        // Configuring view provider behaviour
        $closure = function ( $params )
        {
            return serialize( array_keys( $params ) );
        };
        $params = array( 'foo' => 'bar' );
        $viewProvider
            ->expects( $this->once() )
            ->method( 'getView' )
            ->with( $contentInfo )
            ->will(
                $this->returnValue(
                    new ContentView( $closure, $params )
                )
            );

        // Configuring template engine behaviour
        $params += array( 'content' => $content, 'viewbaseLayout' => $this->viewBaseLayout );
        $expectedTemplateResult = serialize( array_keys( $params ) );
        $this->templateEngineMock
            ->expects( $this->never() )
            ->method( 'render' );

        self::assertSame( $expectedTemplateResult, $this->viewManager->renderContent( $content ) );
    }

    public function testRenderLocation()
    {
        $viewProvider = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Location' );
        $this->viewManager->addLocationViewProvider( $viewProvider );

        $location = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' );
        $content = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content' );
        $contentInfo = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' );

        // Configuring view provider behaviour
        $templateIdentifier = 'foo:bar:baz';
        $params = array( 'foo' => 'bar' );
        $viewProvider
            ->expects( $this->once() )
            ->method( 'getView' )
            ->with( $location, 'customViewType' )
            ->will(
                $this->returnValue(
                    new ContentView( $templateIdentifier, $params )
                )
            );

        $languages = array( 'eng-GB' );
        $this->configResolverMock
            ->expects( $this->once() )
            ->method( "getParameter" )
            ->with( 'languages' )
            ->will( $this->returnValue( $languages ) );

        $contentService = $this->getMockBuilder( "eZ\\Publish\\Core\\Repository\\DomainLogic\\ContentService" )
            ->disableOriginalConstructor()
            ->getMock();

        $contentService->expects( $this->any() )
            ->method( "loadContentByContentInfo" )
            ->with( $contentInfo, $languages )
            ->will(
                $this->returnValue( $content )
            );

        $this->repositoryMock
            ->expects( $this->any() )
            ->method( "getContentService" )
            ->will(
                $this->returnValue(
                    $contentService
                )
            );

        $location->expects( $this->any() )
            ->method( "getContentInfo" )
            ->will( $this->returnValue( $contentInfo ) );

        // Configuring template engine behaviour
        $expectedTemplateResult = 'This is location rendering';
        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( 'render' )
            ->with( $templateIdentifier, $params + array( 'location' => $location, 'content' => $content, 'viewbaseLayout' => $this->viewBaseLayout ) )
            ->will( $this->returnValue( $expectedTemplateResult ) );

        self::assertSame( $expectedTemplateResult, $this->viewManager->renderLocation( $location, 'customViewType' ) );
    }

    public function testRenderLocationWithContentPassed()
    {
        $viewProvider = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Location' );
        $this->viewManager->addLocationViewProvider( $viewProvider );

        $location = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' );
        $content = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content' );
        $contentInfo = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' );

        // Configuring view provider behaviour
        $templateIdentifier = 'foo:bar:baz';
        $params = array( 'foo' => 'bar', 'content' => $content );
        $viewProvider
            ->expects( $this->once() )
            ->method( 'getView' )
            ->with( $location, 'customViewType' )
            ->will(
                $this->returnValue(
                    new ContentView( $templateIdentifier, $params )
                )
            );

        $contentService = $this->getMockBuilder( "eZ\\Publish\\Core\\Repository\\DomainLogic\\ContentService" )
            ->disableOriginalConstructor()
            ->getMock();

        $contentService->expects( $this->any() )
            ->method( "loadContentByContentInfo" )
            ->with( $contentInfo )
            ->will(
                $this->returnValue( $content )
            );

        $this->repositoryMock
            ->expects( $this->any() )
            ->method( "getContentService" )
            ->will(
                $this->returnValue(
                    $contentService
                )
            );

        $location->expects( $this->any() )
            ->method( "getContentInfo" )
            ->will( $this->returnValue( $contentInfo ) );

        // Configuring template engine behaviour
        $expectedTemplateResult = 'This is location rendering';
        $this->templateEngineMock
            ->expects( $this->once() )
            ->method( 'render' )
            ->with( $templateIdentifier, $params + array( 'location' => $location, 'content' => $content, 'viewbaseLayout' => $this->viewBaseLayout ) )
            ->will( $this->returnValue( $expectedTemplateResult ) );

        self::assertSame( $expectedTemplateResult, $this->viewManager->renderLocation( $location, 'customViewType' ) );
    }

    public function testRenderLocationWithClosure()
    {
        $viewProvider = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Location' );
        $this->viewManager->addLocationViewProvider( $viewProvider );

        $location = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' );
        $content = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content' );
        $contentInfo = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' );

        // Configuring view provider behaviour
        $closure = function ( $params )
        {
            return serialize( array_keys( $params ) );
        };
        $params = array( 'foo' => 'bar' );
        $viewProvider
            ->expects( $this->once() )
            ->method( 'getView' )
            ->with( $location )
            ->will(
                $this->returnValue(
                    new ContentView( $closure, $params )
                )
            );

        $contentService = $this->getMockBuilder( "eZ\\Publish\\Core\\Repository\\DomainLogic\\ContentService" )
            ->disableOriginalConstructor()
            ->getMock();

        $contentService->expects( $this->any() )
            ->method( "loadContentByContentInfo" )
            ->with( $contentInfo )
            ->will(
                $this->returnValue( $content )
            );

        $this->repositoryMock
            ->expects( $this->any() )
            ->method( "getContentService" )
            ->will(
                $this->returnValue(
                    $contentService
                )
            );

        $location->expects( $this->any() )
            ->method( "getContentInfo" )
            ->will( $this->returnValue( $contentInfo ) );

        // Configuring template engine behaviour
        $params += array( 'location' => $location, 'content' => $content, 'viewbaseLayout' => $this->viewBaseLayout );
        $expectedTemplateResult = serialize( array_keys( $params ) );
        $this->templateEngineMock
            ->expects( $this->never() )
            ->method( 'render' );

        self::assertSame( $expectedTemplateResult, $this->viewManager->renderLocation( $location ) );
    }

    private function createContentViewProviderMocks()
    {
        return array(
            $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Content' ),
            $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Content' ),
            $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Content' ),
        );
    }

    private function createLocationViewProviderMocks()
    {
        return array(
            $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Location' ),
            $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Location' ),
            $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Location' ),
        );
    }
}
