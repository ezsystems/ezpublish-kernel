<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use eZ\Publish\API\Repository\Values\Content\Relation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\SPI\Repository\Decorator\ContentServiceDecorator;

class ContentServiceDecoratorTest extends TestCase
{
    private const EXAMPLE_CONTENT_ID = 1;
    private const EXAMPLE_LANGUAGE_CODE = 'eng-GB';
    private const EXAMPLE_CONTENT_REMOTE_ID = 'example';
    private const EXAMPLE_VERSION_NO = 1;

    protected function createDecorator(MockObject $service): ContentService
    {
        return new class($service) extends ContentServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(ContentService::class);
    }

    public function testLoadContentInfoDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [self::EXAMPLE_CONTENT_ID];

        $serviceMock->expects($this->once())->method('loadContentInfo')->with(...$parameters)->willReturn($this->createMock(ContentInfo::class));

        $decoratedService->loadContentInfo(...$parameters);
    }

    public function testLoadContentInfoListDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [['random_value_5ced05ce154173.99718966']];

        $serviceMock->expects($this->once())->method('loadContentInfoList')->with(...$parameters)->willReturn([]);

        $decoratedService->loadContentInfoList(...$parameters);
    }

    public function testLoadContentInfoByRemoteIdDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce1541a6.54558542'];

        $serviceMock->expects($this->once())->method('loadContentInfoByRemoteId')->with(...$parameters)->willReturn($this->createMock(ContentInfo::class));

        $decoratedService->loadContentInfoByRemoteId(...$parameters);
    }

    public function testLoadVersionInfoDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentInfo::class),
            self::EXAMPLE_VERSION_NO,
        ];

        $serviceMock->expects($this->once())->method('loadVersionInfo')->with(...$parameters)->willReturn($this->createMock(VersionInfo::class));

        $decoratedService->loadVersionInfo(...$parameters);
    }

    public function testLoadVersionInfoByIdDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            self::EXAMPLE_CONTENT_ID,
            self::EXAMPLE_VERSION_NO,
        ];

        $serviceMock->expects($this->once())->method('loadVersionInfoById')->with(...$parameters)->willReturn($this->createMock(VersionInfo::class));

        $decoratedService->loadVersionInfoById(...$parameters);
    }

    public function testLoadContentByContentInfoDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentInfo::class),
            [self::EXAMPLE_LANGUAGE_CODE],
            self::EXAMPLE_VERSION_NO,
            true,
        ];

        $serviceMock->expects($this->once())->method('loadContentByContentInfo')->with(...$parameters)->willReturn($this->createMock(Content::class));

        $decoratedService->loadContentByContentInfo(...$parameters);
    }

    public function testLoadContentByVersionInfoDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(VersionInfo::class),
            [self::EXAMPLE_LANGUAGE_CODE],
            true,
        ];

        $serviceMock->expects($this->once())->method('loadContentByVersionInfo')->with(...$parameters)->willReturn($this->createMock(Content::class));

        $decoratedService->loadContentByVersionInfo(...$parameters);
    }

    public function testLoadContentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            self::EXAMPLE_CONTENT_ID,
            [self::EXAMPLE_LANGUAGE_CODE],
            self::EXAMPLE_VERSION_NO,
            true,
        ];

        $serviceMock->expects($this->once())->method('loadContent')->with(...$parameters)->willReturn($this->createMock(Content::class));

        $decoratedService->loadContent(...$parameters);
    }

    public function testLoadContentByRemoteIdDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            self::EXAMPLE_CONTENT_REMOTE_ID,
            [self::EXAMPLE_LANGUAGE_CODE],
            self::EXAMPLE_VERSION_NO,
            true,
        ];

        $serviceMock->expects($this->once())->method('loadContentByRemoteId')->with(...$parameters)->willReturn($this->createMock(Content::class));

        $decoratedService->loadContentByRemoteId(...$parameters);
    }

    public function testLoadContentListByContentInfoDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            [$this->createMock(ContentInfo::class)],
            [self::EXAMPLE_LANGUAGE_CODE],
            true,
        ];

        $serviceMock->expects($this->once())->method('loadContentListByContentInfo')->with(...$parameters)->willReturn([]);

        $decoratedService->loadContentListByContentInfo(...$parameters);
    }

    public function testCreateContentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentCreateStruct::class),
            ['random_value_5ced05ce155881.06739513'],
        ];

        $serviceMock->expects($this->once())->method('createContent')->with(...$parameters)->willReturn($this->createMock(Content::class));

        $decoratedService->createContent(...$parameters);
    }

    public function testUpdateContentMetadataDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(ContentMetadataUpdateStruct::class),
        ];

        $serviceMock->expects($this->once())->method('updateContentMetadata')->with(...$parameters)->willReturn($this->createMock(Content::class));

        $decoratedService->updateContentMetadata(...$parameters);
    }

    public function testDeleteContentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentInfo::class)];

        $serviceMock->expects($this->once())->method('deleteContent')->with(...$parameters)->willReturn([]);

        $decoratedService->deleteContent(...$parameters);
    }

    public function testCreateContentDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(VersionInfo::class),
            $this->createMock(User::class),
        ];

        $serviceMock->expects($this->once())->method('createContentDraft')->with(...$parameters)->willReturn($this->createMock(Content::class));

        $decoratedService->createContentDraft(...$parameters);
    }

    public function testLoadContentDraftsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(User::class)];

        $serviceMock->expects($this->once())->method('loadContentDrafts')->with(...$parameters)->willReturn([]);

        $decoratedService->loadContentDrafts(...$parameters);
    }

    public function testUpdateContentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentUpdateStruct::class),
        ];

        $serviceMock->expects($this->once())->method('updateContent')->with(...$parameters)->willReturn($this->createMock(Content::class));

        $decoratedService->updateContent(...$parameters);
    }

    public function testPublishVersionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(VersionInfo::class)];

        $serviceMock->expects($this->once())->method('publishVersion')->with(...$parameters)->willReturn($this->createMock(Content::class));

        $decoratedService->publishVersion(...$parameters);
    }

    public function testDeleteVersionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(VersionInfo::class)];

        $serviceMock->expects($this->once())->method('deleteVersion')->with(...$parameters);

        $decoratedService->deleteVersion(...$parameters);
    }

    public function testLoadVersionsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentInfo::class)];

        $serviceMock->expects($this->once())->method('loadVersions')->with(...$parameters)->willReturn([]);

        $decoratedService->loadVersions(...$parameters);
    }

    public function testCopyContentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(LocationCreateStruct::class),
            $this->createMock(VersionInfo::class),
        ];

        $serviceMock->expects($this->once())->method('copyContent')->with(...$parameters)->willReturn($this->createMock(Content::class));

        $decoratedService->copyContent(...$parameters);
    }

    public function testLoadRelationsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(VersionInfo::class)];

        $serviceMock->expects($this->once())->method('loadRelations')->with(...$parameters)->willReturn([]);

        $decoratedService->loadRelations(...$parameters);
    }

    public function testLoadReverseRelationsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentInfo::class)];

        $serviceMock->expects($this->once())->method('loadReverseRelations')->with(...$parameters)->willReturn([]);

        $decoratedService->loadReverseRelations(...$parameters);
    }

    public function testAddRelationDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentInfo::class),
        ];

        $serviceMock->expects($this->once())->method('addRelation')->with(...$parameters)->willReturn($this->createMock(Relation::class));

        $decoratedService->addRelation(...$parameters);
    }

    public function testDeleteRelationDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentInfo::class),
        ];

        $serviceMock->expects($this->once())->method('deleteRelation')->with(...$parameters);

        $decoratedService->deleteRelation(...$parameters);
    }

    public function testRemoveTranslationDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentInfo::class),
            self::EXAMPLE_LANGUAGE_CODE,
        ];

        $serviceMock->expects($this->once())->method('removeTranslation')->with(...$parameters);

        $decoratedService->removeTranslation(...$parameters);
    }

    public function testDeleteTranslationDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentInfo::class),
            self::EXAMPLE_LANGUAGE_CODE,
        ];

        $serviceMock->expects($this->once())->method('deleteTranslation')->with(...$parameters);

        $decoratedService->deleteTranslation(...$parameters);
    }

    public function testDeleteTranslationFromDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(VersionInfo::class),
            'random_value_5ced05ce156d37.22902273',
        ];

        $serviceMock->expects($this->once())->method('deleteTranslationFromDraft')->with(...$parameters)->willReturn($this->createMock(Content::class));

        $decoratedService->deleteTranslationFromDraft(...$parameters);
    }

    public function testHideContentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentInfo::class)];

        $serviceMock->expects($this->once())->method('hideContent')->with(...$parameters);

        $decoratedService->hideContent(...$parameters);
    }

    public function testRevealContentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentInfo::class)];

        $serviceMock->expects($this->once())->method('revealContent')->with(...$parameters);

        $decoratedService->revealContent(...$parameters);
    }

    public function testNewContentCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentType::class),
            'random_value_5ced05ce156db7.87562997',
        ];

        $serviceMock->expects($this->once())->method('newContentCreateStruct')->with(...$parameters)->willReturn($this->createMock(ContentCreateStruct::class));

        $decoratedService->newContentCreateStruct(...$parameters);
    }

    public function testNewContentMetadataUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('newContentMetadataUpdateStruct')->with(...$parameters)->willReturn($this->createMock(ContentMetadataUpdateStruct::class));

        $decoratedService->newContentMetadataUpdateStruct(...$parameters);
    }

    public function testNewContentUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('newContentUpdateStruct')->with(...$parameters)->willReturn($this->createMock(ContentUpdateStruct::class));

        $decoratedService->newContentUpdateStruct(...$parameters);
    }
}
