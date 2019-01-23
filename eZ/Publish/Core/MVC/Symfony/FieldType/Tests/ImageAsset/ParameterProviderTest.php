<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\FieldType\Tests\ImageAsset;

use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\Core\FieldType\ImageAsset\Value as ImageAssetValue;
use eZ\Publish\Core\MVC\Symfony\FieldType\ImageAsset\ParameterProvider;
use eZ\Publish\Core\Repository\SiteAccessAware\Repository;
use PHPUnit\Framework\TestCase;

class ParameterProviderTest extends TestCase
{
    /** @var \eZ\Publish\API\Repository\ContentService|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var \eZ\Publish\API\Repository\ContentService|\PHPUnit\Framework\MockObject\MockObject */
    private $permissionsResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\FieldType\ImageAsset\ParameterProvider */
    private $parameterProvider;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(Repository::class);
        $this->permissionsResolver = $this->createMock(PermissionResolver::class);

        $this->permissionsResolver
            ->method('canUser')
            ->willReturn(true)
        ;

        $this->repository
            ->method('getPermissionResolver')
            ->willReturn($this->permissionsResolver)
        ;

        $this->parameterProvider = new ParameterProvider($this->repository);
    }

    public function dataProviderForTestGetViewParameters(): array
    {
        return [
            [ContentInfo::STATUS_PUBLISHED, ['available' => true]],
            [ContentInfo::STATUS_TRASHED, ['available' => false]],
        ];
    }

    /**
     * @dataProvider dataProviderForTestGetViewParameters
     */
    public function testGetViewParameters($status, array $expected): void
    {
        $destinationContentId = 1;

        $closure = function (Repository $repository) use ($destinationContentId) {
            return $repository->getContentService()->loadContentInfo($destinationContentId);
        };

        $this->repository
            ->method('sudo')
            ->with($closure)
            ->willReturn(new ContentInfo([
                'status' => $status,
            ]));

        $actual = $this->parameterProvider->getViewParameters(new Field([
            'value' => new ImageAssetValue($destinationContentId),
        ]));

        $this->assertEquals($expected, $actual);
    }

    public function testGetViewParametersHandleNotFoundException(): void
    {
        $destinationContentId = 1;

        $closure = function (Repository $repository) use ($destinationContentId) {
            return $repository->getContentService()->loadContentInfo($destinationContentId);
        };

        $this->repository
            ->expects($this->once())
            ->method('sudo')
            ->with($closure)
            ->willThrowException($this->createMock(NotFoundException::class));

        $actual = $this->parameterProvider->getViewParameters(new Field([
            'value' => new ImageAssetValue($destinationContentId),
        ]));

        $this->assertEquals([
            'available' => false,
        ], $actual);
    }

    public function testGetViewParametersHandleUnauthorizedException(): void
    {
        $destinationContentId = 1;

        $closure = function (Repository $repository) use ($destinationContentId) {
            return $repository->getContentService()->loadContentInfo($destinationContentId);
        };

        $this->repository
            ->expects($this->once())
            ->method('sudo')
            ->with($closure)
            ->willThrowException($this->createMock(UnauthorizedException::class));

        $actual = $this->parameterProvider->getViewParameters(new Field([
            'value' => new ImageAssetValue($destinationContentId),
        ]));

        $this->assertEquals([
            'available' => false,
        ], $actual);
    }
}
