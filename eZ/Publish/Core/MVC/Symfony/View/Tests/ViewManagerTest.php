<?php

/**
 * File containing the ViewManagerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Tests;

use eZ\Publish\API\Repository\ContentService as APIContentService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\View\Manager;
use eZ\Publish\Core\MVC\Symfony\View\View;
use eZ\Publish\Core\MVC\Symfony\View\ViewProvider;
use eZ\Publish\Core\MVC\Symfony\View\Configurator;
use eZ\Publish\Core\Repository\ContentService;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

/**
 * @group mvc
 */
class ViewManagerTest extends TestCase
{
    /** @var \eZ\Publish\Core\MVC\Symfony\View\Manager */
    private $viewManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Twig\Environment */
    private $templateEngineMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcherMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\API\Repository\Repository */
    private $repositoryMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolverMock;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Configurator|\PHPUnit\Framework\MockObject\MockObject */
    private $viewConfigurator;

    private $viewBaseLayout = 'EzPublishCoreBundle::viewbase.html.twig';

    protected function setUp(): void
    {
        parent::setUp();
        $this->templateEngineMock = $this->createMock(Environment::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->repositoryMock = $this->createMock(Repository::class);
        $this->configResolverMock = $this->createMock(ConfigResolverInterface::class);
        $this->viewConfigurator = $this->createMock(Configurator::class);
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
        self::assertSame([], $this->viewManager->getAllContentViewProviders());
        $viewProvider = $this->createMock(ViewProvider::class);
        $this->viewManager->addContentViewProvider($viewProvider);
        self::assertSame([$viewProvider], $this->viewManager->getAllContentViewProviders());
    }

    public function testAddLocationViewProvider()
    {
        self::assertSame([], $this->viewManager->getAllLocationViewProviders());
        $viewProvider = $this->createMock(ViewProvider::class);
        $this->viewManager->addLocationViewProvider($viewProvider);
        self::assertSame([$viewProvider], $this->viewManager->getAllLocationViewProviders());
    }

    public function testContentViewProvidersPriority()
    {
        list($high, $medium, $low) = $this->createContentViewProviderMocks();
        $this->viewManager->addContentViewProvider($medium, 33);
        $this->viewManager->addContentViewProvider($high, 100);
        $this->viewManager->addContentViewProvider($low, -100);
        self::assertSame(
            [$high, $medium, $low],
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
            [$high, $medium, $low],
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
                $params + ['content' => $content, 'view_base_layout' => $this->viewBaseLayout])
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
        $params += ['content' => $content, 'view_base_layout' => $this->viewBaseLayout];
        $expectedTemplateResult = array_keys($params);
        $this->templateEngineMock
            ->expects($this->never())
            ->method('render');

        $templateResult = unserialize($this->viewManager->renderContent($content, 'full', $params));

        self::assertEqualsCanonicalizing($expectedTemplateResult, $templateResult);
    }

    public function testRenderLocation()
    {
        $content = new Content(['versionInfo' => new VersionInfo(['contentInfo' => new ContentInfo()])]);
        $location = new Location(['contentInfo' => new ContentInfo()]);

        // Configuring view provider behaviour
        $templateIdentifier = 'foo:bar:baz';
        $params = ['foo' => 'bar'];
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

        $languages = ['eng-GB'];
        $this->configResolverMock
            ->expects($this->any())
            ->method('getParameter')
            ->with('languages')
            ->will($this->returnValue($languages));

        $contentService = $this->createMock(APIContentService::class);

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
            ->with($templateIdentifier, $params + ['location' => $location, 'content' => $content, 'view_base_layout' => $this->viewBaseLayout])
            ->will($this->returnValue($expectedTemplateResult));

        self::assertSame($expectedTemplateResult, $this->viewManager->renderLocation($location, 'customViewType', $params));
    }

    public function testRenderLocationWithContentPassed()
    {
        $content = new Content(['versionInfo' => new VersionInfo(['contentInfo' => new ContentInfo()])]);
        $location = new Location(['contentInfo' => new ContentInfo()]);

        // Configuring view provider behaviour
        $templateIdentifier = 'foo:bar:baz';
        $params = ['foo' => 'bar', 'content' => $content];
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

        $contentService = $this->createMock(ContentService::class);

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
                $params + ['location' => $location, 'content' => $content, 'view_base_layout' => $this->viewBaseLayout])
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

        $contentService = $this->createMock(ContentService::class);

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
        $params += ['location' => $location, 'content' => $content, 'view_base_layout' => $this->viewBaseLayout];
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
        return [
            $this->createMock(ViewProvider::class),
            $this->createMock(ViewProvider::class),
            $this->createMock(ViewProvider::class),
        ];
    }

    private function createLocationViewProviderMocks()
    {
        return [
            $this->createMock(ViewProvider::class),
            $this->createMock(ViewProvider::class),
            $this->createMock(ViewProvider::class),
        ];
    }
}
