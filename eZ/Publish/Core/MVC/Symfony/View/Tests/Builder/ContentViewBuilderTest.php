<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Tests\Builder;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Helper\ContentInfoLocationLoader;
use eZ\Publish\Core\MVC\Symfony\View\Builder\ContentViewBuilder;
use eZ\Publish\Core\MVC\Symfony\View\Configurator;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group mvc
 */
class ContentViewBuilderTest extends TestCase
{
    /** @var \eZ\Publish\API\Repository\Repository|MockObject */
    private $repository;

    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface|MockObject */
    private $authorizationChecker;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Configurator|MockObject */
    private $viewConfigurator;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\ParametersInjector|MockObject */
    private $parametersInjector;

    /** @var \eZ\Publish\Core\Helper\ContentInfoLocationLoader|MockObject */
    private $contentInfoLocationLoader;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Builder\ContentViewBuilder|MockObject */
    private $contentViewBuilder;

    public function setUp()
    {
        $this->repository = $this->getMockBuilder(Repository::class)->disableOriginalConstructor()->setMethods(['sudo'])->getMock();
        $this->authorizationChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $this->viewConfigurator = $this->getMockBuilder(Configurator::class)->getMock();
        $this->parametersInjector = $this->getMockBuilder(ParametersInjector::class)->getMock();
        $this->contentInfoLocationLoader = $this->getMockBuilder(ContentInfoLocationLoader::class)->getMock();
        $this->contentViewBuilder = new ContentViewBuilder(
            $this->repository,
            $this->authorizationChecker,
            $this->viewConfigurator,
            $this->parametersInjector,
            $this->contentInfoLocationLoader
        );
    }

    public function testMatches(): void
    {
        $this->assertTrue($this->contentViewBuilder->matches('ez_content:55'));
        $this->assertFalse($this->contentViewBuilder->matches('dummy_value'));
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function testBuildViewWithoutLocationIdAndContentId(): void
    {
        $parameters = [
            'viewType' => '',
            '_controller' => '',
        ];

        $this->contentViewBuilder->buildView($parameters);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testBuildViewWithInvalidLocationId(): void
    {
        $parameters = [
            'viewType' => '',
            '_controller' => '',
            'locationId' => 865,
        ];

        $this->repository
            ->expects($this->once())
            ->method('sudo')
            ->willThrowException(new NotFoundException('location', 865));

        $this->contentViewBuilder->buildView($parameters);
    }

    /**
     * @expectedException \eZ\Publish\Core\MVC\Exception\HiddenLocationException
     */
    public function testBuildViewWithHiddenLocation(): void
    {
        $parameters = [
            'viewType' => '',
            '_controller' => '',
            'locationId' => 2,
        ];

        $location = new Location(['invisible' => true]);

        $this->repository
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($location);

        $this->contentViewBuilder->buildView($parameters);
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function testBuildViewWithoutContentReadPermission(): void
    {
        $location = new Location(
            [
                'invisible' => false,
                'content' => new Content([
                    'versionInfo' => new VersionInfo([
                        'contentInfo' => new ContentInfo(),
                    ]),
                ]),
            ]
        );

        $parameters = [
            'viewType' => '',
            '_controller' => '',
            'locationId' => 2,
        ];

        // It's call for LocationService::loadLocation()
        $this->repository
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($location);

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn(false);

        $this->contentViewBuilder->buildView($parameters);
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function testBuildEmbedViewWithoutContentViewEmbedPermission(): void
    {
        $location = new Location(
            [
                'invisible' => false,
                'contentInfo' => new ContentInfo([
                    'id' => 120,
                ]),
                'content' => new Content([
                    'versionInfo' => new VersionInfo([
                        'contentInfo' => new ContentInfo([
                            'id' => 91,
                        ]),
                    ]),
                ]),
            ]
        );

        $parameters = [
            'viewType' => 'embed',
            '_controller' => '',
            'locationId' => 2,
        ];

        // It's call for LocationService::loadLocation()
        $this->repository
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($location);

        $this->authorizationChecker
            ->expects($this->at(0))
            ->method('isGranted')
            ->willReturn(false);

        $this->authorizationChecker
            ->expects($this->at(1))
            ->method('isGranted')
            ->willReturn(false);

        $this->contentViewBuilder->buildView($parameters);
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function testBuildViewWithContentWhichDoesNotBelongsToLocation(): void
    {
        $location = new Location(
            [
                'invisible' => false,
                'contentInfo' => new ContentInfo([
                   'id' => 120,
                ]),
                'content' => new Content([
                    'versionInfo' => new VersionInfo([
                        'contentInfo' => new ContentInfo([
                            'id' => 91,
                        ]),
                    ]),
                ]),
            ]
        );

        $parameters = [
            'viewType' => '',
            '_controller' => '',
            'locationId' => 2,
        ];

        // It's call for LocationService::loadLocation()
        $this->repository
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($location);

        $this->authorizationChecker
            ->expects($this->at(0))
            ->method('isGranted')
            ->willReturn(true);

        $this->contentViewBuilder->buildView($parameters);
    }

    public function testBuildViewWithDeprecatedControllerReference(): void
    {
        $contentInfo = new ContentInfo(['id' => 120]);
        $content = new Content([
            'versionInfo' => new VersionInfo([
                'contentInfo' => $contentInfo,
            ]),
        ]);
        $location = new Location(
            [
                'invisible' => false,
                'contentInfo' => $contentInfo,
                'content' => $content,
            ]
        );

        $expectedView = new ContentView(null, [], 'full');
        $expectedView->setControllerReference(new ControllerReference('ez_content:viewAction'));
        $expectedView->setLocation($location);
        $expectedView->setContent($content);

        $parameters = [
            'viewType' => 'full',
            '_controller' => 'ez_content:viewLocation',
            'locationId' => 2,
        ];

        $this->repository
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($location);

        $this->authorizationChecker
            ->expects($this->at(0))
            ->method('isGranted')
            ->willReturn(true);

        $this->assertEquals($expectedView, $this->contentViewBuilder->buildView($parameters));
    }

    public function testBuildView(): void
    {
        $contentInfo = new ContentInfo(['id' => 120]);
        $content = new Content([
            'versionInfo' => new VersionInfo([
                'contentInfo' => $contentInfo,
            ]),
        ]);
        $location = new Location(
            [
                'invisible' => false,
                'contentInfo' => $contentInfo,
                'content' => $content,
            ]
        );

        $expectedView = new ContentView(null, [], 'full');
        $expectedView->setControllerReference(new ControllerReference('ez_content:viewAction'));
        $expectedView->setLocation($location);
        $expectedView->setContent($content);

        $parameters = [
            'viewType' => 'full',
            '_controller' => 'ez_content:viewContent',
            'locationId' => 2,
        ];

        $this->repository
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($location);

        $this->authorizationChecker
            ->expects($this->at(0))
            ->method('isGranted')
            ->willReturn(true);

        $this->assertEquals($expectedView, $this->contentViewBuilder->buildView($parameters));
    }
}
