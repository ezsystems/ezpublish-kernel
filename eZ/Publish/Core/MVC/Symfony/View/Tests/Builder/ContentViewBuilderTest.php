<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\Builder;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Helper\ContentInfoLocationLoader;
use eZ\Publish\Core\MVC\Exception\HiddenLocationException;
use eZ\Publish\Core\MVC\Symfony\View\Builder\ContentViewBuilder;
use eZ\Publish\Core\MVC\Symfony\View\Configurator;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpFoundation\RequestStack;
use PHPUnit\Framework\TestCase;

/**
 * @group mvc
 */
class ContentViewBuilderTest extends TestCase
{
    /** @var \eZ\Publish\API\Repository\Repository|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Configurator|\PHPUnit\Framework\MockObject\MockObject */
    private $viewConfigurator;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\ParametersInjector|\PHPUnit\Framework\MockObject\MockObject */
    private $parametersInjector;

    /** @var \eZ\Publish\Core\Helper\ContentInfoLocationLoader|\PHPUnit\Framework\MockObject\MockObject */
    private $contentInfoLocationLoader;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Builder\ContentViewBuilder|\PHPUnit\Framework\MockObject\MockObject */
    private $contentViewBuilder;

    /** @var \eZ\Publish\API\Repository\PermissionResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $permissionResolver;

    /** @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit\Framework\MockObject\MockObject */
    private $requestStack;

    public function setUp(): void
    {
        $this->repository = $this->getMockBuilder(Repository::class)->disableOriginalConstructor()->setMethods(['sudo', 'getPermissionResolver'])->getMock();
        $this->viewConfigurator = $this->getMockBuilder(Configurator::class)->getMock();
        $this->parametersInjector = $this->getMockBuilder(ParametersInjector::class)->getMock();
        $this->contentInfoLocationLoader = $this->getMockBuilder(ContentInfoLocationLoader::class)->getMock();
        $this->permissionResolver = $this->getMockBuilder(PermissionResolver::class)->getMock();
        $this->requestStack = $this->getMockBuilder(RequestStack::class)->getMock();
        $this->repository
            ->expects($this->any())
            ->method('getPermissionResolver')
            ->willReturn($this->permissionResolver);

        $this->contentViewBuilder = new ContentViewBuilder(
            $this->repository,
            $this->viewConfigurator,
            $this->parametersInjector,
            $this->requestStack,
            $this->contentInfoLocationLoader
        );
    }

    public function testMatches(): void
    {
        $this->assertTrue($this->contentViewBuilder->matches('ez_content:55'));
        $this->assertFalse($this->contentViewBuilder->matches('dummy_value'));
    }

    public function testBuildViewWithoutLocationIdAndContentId(): void
    {
        $parameters = [
            'viewType' => 'full',
            '_controller' => 'ez_content:viewContent',
        ];

        $this->expectException(InvalidArgumentException::class);

        $this->contentViewBuilder->buildView($parameters);
    }

    public function testBuildViewWithInvalidLocationId(): void
    {
        $parameters = [
            'viewType' => 'full',
            '_controller' => 'ez_content:viewContent',
            'locationId' => 865,
        ];

        $this->repository
            ->expects($this->once())
            ->method('sudo')
            ->willThrowException(new NotFoundException('location', 865));

        $this->expectException(APINotFoundException::class);

        $this->contentViewBuilder->buildView($parameters);
    }

    public function testBuildViewWithHiddenLocation(): void
    {
        $parameters = [
            'viewType' => 'full',
            '_controller' => 'ez_content:viewContent',
            'locationId' => 2,
        ];

        $location = new Location(['invisible' => true]);

        $this->repository
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($location);

        $this->expectException(HiddenLocationException::class);

        $this->contentViewBuilder->buildView($parameters);
    }

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
            'viewType' => 'full',
            '_controller' => 'ez_content:viewContent',
            'locationId' => 2,
        ];

        // It's call for LocationService::loadLocation()
        $this->repository
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($location);

        $this->permissionResolver
            ->expects($this->any())
            ->method('canUser')
            ->willReturn(false);

        $this->expectException(UnauthorizedException::class);

        $this->contentViewBuilder->buildView($parameters);
    }

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
            '_controller' => 'ez_content:viewContent',
            'locationId' => 2,
        ];

        // It's call for LocationService::loadLocation()
        $this->repository
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($location);

        $this->permissionResolver
            ->expects($this->at(0))
            ->method('canUser')
            ->willReturn(false);

        $this->permissionResolver
            ->expects($this->at(1))
            ->method('canUser')
            ->willReturn(false);

        $this->expectException(UnauthorizedException::class);

        $this->contentViewBuilder->buildView($parameters);
    }

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
            'viewType' => 'full',
            '_controller' => 'ez_content:viewContent',
            'locationId' => 2,
        ];

        // It's call for LocationService::loadLocation()
        $this->repository
            ->expects($this->once())
            ->method('sudo')
            ->willReturn($location);

        $this->permissionResolver
            ->expects($this->at(0))
            ->method('canUser')
            ->willReturn(true);

        $this->expectException(InvalidArgumentException::class);

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

        $this->permissionResolver
            ->expects($this->at(0))
            ->method('canUser')
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

        $this->permissionResolver
            ->expects($this->at(0))
            ->method('canUser')
            ->willReturn(true);

        $this->assertEquals($expectedView, $this->contentViewBuilder->buildView($parameters));
    }
}
