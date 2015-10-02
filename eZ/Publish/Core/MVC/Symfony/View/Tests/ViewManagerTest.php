<?php

/**
 * File containing the ViewManagerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Tests;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\Manager;
use eZ\Publish\Core\MVC\Symfony\View\View;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Templating\EngineInterface
     */
    private $templateEngineMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcherMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\Repository
     */
    private $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolverMock;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\Configurator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $viewConfigurator;

    private $viewBaseLayout = 'EzPublishCoreBundle::viewbase.html.twig';

    protected function setUp()
    {
        parent::setUp();
        $this->templateEngineMock = $this->getMock('Symfony\\Component\\Templating\\EngineInterface');
        $this->eventDispatcherMock = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');
        $this->repositoryMock = $this->getMockBuilder('eZ\\Publish\\Core\\Repository\\Repository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configResolverMock = $this->getMock('eZ\\Publish\\Core\\MVC\\ConfigResolverInterface');
        $this->viewConfigurator = $this->getMock('eZ\Publish\Core\MVC\Symfony\View\Configurator');
        $this->viewManager = new Manager(
            $this->templateEngineMock,
            $this->eventDispatcherMock,
            $this->repositoryMock,
            $this->configResolverMock,
            $this->viewBaseLayout,
            $this->viewConfigurator
        );
    }

    public function testAddContentViewProvider()
    {
        self::assertSame(array(), $this->viewManager->getAllContentViewProviders());
        $viewProvider = $this->getMock('eZ\Publish\Core\MVC\Symfony\View\ViewProvider');
        $this->viewManager->addContentViewProvider($viewProvider);
        self::assertSame(array($viewProvider), $this->viewManager->getAllContentViewProviders());
    }

    public function testAddLocationViewProvider()
    {
        self::assertSame(array(), $this->viewManager->getAllLocationViewProviders());
        $viewProvider = $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\View\\ViewProvider');
        $this->viewManager->addLocationViewProvider($viewProvider);
        self::assertSame(array($viewProvider), $this->viewManager->getAllLocationViewProviders());
    }

    public function testContentViewProvidersPriority()
    {
        list($high, $medium, $low) = $this->createContentViewProviderMocks();
        $this->viewManager->addContentViewProvider($medium, 33);
        $this->viewManager->addContentViewProvider($high, 100);
        $this->viewManager->addContentViewProvider($low, -100);
        self::assertSame(
            array($high, $medium, $low),
            $this->viewManager->getAllContentViewProviders()
        );
    }

    public function testLocationViewProvidersPriority()
    {
        list($high, $medium, $low) = $this->createLocationViewProviderMocks();
        $this->viewManager->addLocationViewProvider($medium, 33);
        $this->viewManager->addLocationViewProvider($high, 100);
        $this->viewManager->addLocationViewProvider($low, -100);
        self::assertSame(
            array($high, $medium, $low),
            $this->viewManager->getAllLocationViewProviders()
        );
    }

    public function testRenderContent()
    {
        $content = new Content(
            ['versionInfo' => new VersionInfo(['contentInfo' => new ContentInfo()])]
        );

        $params = ['foo' => 'bar'];
        $templateIdentifier = 'foo:bar:baz';
        $this->viewConfigurator
            ->expects($this->once())
            ->method('configure')
            ->will(
                $this->returnCallback(
                    function (View $view) use ($templateIdentifier) {
                        $view->setTemplateIdentifier($templateIdentifier);
                    }
                )
            );

        // Configuring template engine behaviour
        $expectedTemplateResult = 'This is content rendering';
        $this->templateEngineMock
            ->expects($this->once())
            ->method('render')
            ->with(
                $templateIdentifier,
                $params + array('content' => $content, 'location' => null, 'viewbaseLayout' => $this->viewBaseLayout))
            ->will($this->returnValue($expectedTemplateResult));

        self::assertSame($expectedTemplateResult, $this->viewManager->renderContent($content, 'customViewType', $params));
    }

    public function testRenderContentWithClosure()
    {
        $content = new Content(
            ['versionInfo' => new VersionInfo(['contentInfo' => new ContentInfo()])]
        );

        // Configuring view provider behaviour
        $closure = function ($params) {
            return serialize(array_keys($params));
        };
        $params = ['foo' => 'bar'];
        $this->viewConfigurator
            ->expects($this->once())
            ->method('configure')
            ->will(
                $this->returnCallback(
                    function (View $view) use ($closure) {
                        $view->setTemplateIdentifier($closure);
                    }
                )
            );

        // Configuring template engine behaviour
        $params += array('content' => $content, 'viewbaseLayout' => $this->viewBaseLayout);
        $expectedTemplateResult = array_keys($params + ['location' => null]);
        $this->templateEngineMock
            ->expects($this->never())
            ->method('render');

        $templateResult = unserialize($this->viewManager->renderContent($content, 'full', $params));
        sort($expectedTemplateResult);
        sort($templateResult);

        self::assertEquals($expectedTemplateResult, $templateResult);
    }

    public function testRenderLocation()
    {
        $content = new Content(['versionInfo' => new VersionInfo(['contentInfo' => new ContentInfo()])]);
        $location = new Location(['contentInfo' => new ContentInfo()]);

        // Configuring view provider behaviour
        $templateIdentifier = 'foo:bar:baz';
        $params = array('foo' => 'bar');
        $this->viewConfigurator
            ->expects($this->once())
            ->method('configure')
            ->will(
                $this->returnCallback(
                    function (View $view) use ($templateIdentifier) {
                        $view->setTemplateIdentifier($templateIdentifier);
                    }
                )
            );

        $languages = array('eng-GB');
        $this->configResolverMock
            ->expects($this->any())
            ->method('getParameter')
            ->with('languages')
            ->will($this->returnValue($languages));

        $contentService = $this->getMock('eZ\Publish\API\Repository\ContentService');

        $contentService->expects($this->any())
            ->method('loadContentByContentInfo')
            ->with($location->contentInfo, $languages)
            ->will($this->returnValue($content));

        $this->repositoryMock
            ->expects($this->any())
            ->method('getContentService')
            ->will($this->returnValue($contentService));

        // Configuring template engine behaviour
        $expectedTemplateResult = 'This is location rendering';
        $this->templateEngineMock
            ->expects($this->once())
            ->method('render')
            ->with($templateIdentifier, $params + array('location' => $location, 'content' => $content, 'viewbaseLayout' => $this->viewBaseLayout))
            ->will($this->returnValue($expectedTemplateResult));

        self::assertSame($expectedTemplateResult, $this->viewManager->renderLocation($location, 'customViewType', $params));
    }

    public function testRenderLocationWithContentPassed()
    {
        $content = new Content(['versionInfo' => new VersionInfo(['contentInfo' => new ContentInfo()])]);
        $location = new Location(['contentInfo' => new ContentInfo()]);

        // Configuring view provider behaviour
        $templateIdentifier = 'foo:bar:baz';
        $params = array('foo' => 'bar', 'content' => $content);
        $this->viewConfigurator
            ->expects($this->once())
            ->method('configure')
            ->will(
                $this->returnCallback(
                    function (View $view) use ($templateIdentifier) {
                        $view->setTemplateIdentifier($templateIdentifier);
                    }
                )
            );

        $contentService = $this->getMockBuilder('eZ\\Publish\\Core\\Repository\\ContentService')
            ->disableOriginalConstructor()
            ->getMock();

        $contentService->expects($this->any())
            ->method('loadContentByContentInfo')
            ->with($content->contentInfo)
            ->will(
                $this->returnValue($content)
            );

        $this->repositoryMock
            ->expects($this->any())
            ->method('getContentService')
            ->will(
                $this->returnValue(
                    $contentService
                )
            );

        // Configuring template engine behaviour
        $expectedTemplateResult = 'This is location rendering';
        $this->templateEngineMock
            ->expects($this->once())
            ->method('render')
            ->with(
                $templateIdentifier,
                $params + ['location' => $location, 'content' => $content, 'viewbaseLayout' => $this->viewBaseLayout])
            ->will($this->returnValue($expectedTemplateResult));

        self::assertSame($expectedTemplateResult, $this->viewManager->renderLocation($location, 'customViewType', $params));
    }

    public function testRenderLocationWithClosure()
    {
        $content = new Content(['versionInfo' => new VersionInfo(['contentInfo' => new ContentInfo()])]);
        $location = new Location(['contentInfo' => new ContentInfo()]);

        // Configuring view provider behaviour
        $closure = function ($params) {
            return serialize(array_keys($params));
        };
        $params = array('foo' => 'bar');
        $this->viewConfigurator
            ->expects($this->once())
            ->method('configure')
            ->will(
                $this->returnCallback(
                    function (View $view) use ($closure) {
                        $view->setTemplateIdentifier($closure);
                    }
                )
            );

        $contentService = $this->getMockBuilder('eZ\\Publish\\Core\\Repository\\ContentService')
            ->disableOriginalConstructor()
            ->getMock();

        $contentService->expects($this->any())
            ->method('loadContentByContentInfo')
            ->with($content->contentInfo)
            ->will(
                $this->returnValue($content)
            );

        $this->repositoryMock
            ->expects($this->any())
            ->method('getContentService')
            ->will(
                $this->returnValue(
                    $contentService
                )
            );

        // Configuring template engine behaviour
        $params += array('location' => $location, 'content' => $content, 'viewbaseLayout' => $this->viewBaseLayout);
        $this->templateEngineMock
            ->expects($this->never())
            ->method('render');

        $expectedTemplateResult = array_keys($params);
        $templateResult = unserialize($this->viewManager->renderLocation($location, 'full', $params));
        sort($expectedTemplateResult);
        sort($templateResult);

        self::assertSame($expectedTemplateResult, $templateResult);
    }

    private function createContentViewProviderMocks()
    {
        return array(
            $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\View\\ViewProvider'),
            $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\View\\ViewProvider'),
            $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\View\\ViewProvider'),
        );
    }

    private function createLocationViewProviderMocks()
    {
        return array(
            $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\View\\ViewProvider'),
            $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\View\\ViewProvider'),
            $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\View\\ViewProvider'),
        );
    }
}
