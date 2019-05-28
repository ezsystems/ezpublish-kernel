<?php

declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
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
    protected function createDecorator(ContentService $service): ContentService
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

        $parameters = ['random_value_5ced05ce154118.08177784'];

        $serviceMock->expects($this->exactly(1))->method('loadContentInfo')->with(...$parameters);

        $decoratedService->loadContentInfo(...$parameters);
    }

    public function testLoadContentInfoListDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [['random_value_5ced05ce154173.99718966']];

        $serviceMock->expects($this->exactly(1))->method('loadContentInfoList')->with(...$parameters);

        $decoratedService->loadContentInfoList(...$parameters);
    }

    public function testLoadContentInfoByRemoteIdDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce1541a6.54558542'];

        $serviceMock->expects($this->exactly(1))->method('loadContentInfoByRemoteId')->with(...$parameters);

        $decoratedService->loadContentInfoByRemoteId(...$parameters);
    }

    public function testLoadVersionInfoDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentInfo::class),
            'random_value_5ced05ce1541e5.29503590',
        ];

        $serviceMock->expects($this->exactly(1))->method('loadVersionInfo')->with(...$parameters);

        $decoratedService->loadVersionInfo(...$parameters);
    }

    public function testLoadVersionInfoByIdDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce154212.71705283',
            'random_value_5ced05ce154226.14877654',
        ];

        $serviceMock->expects($this->exactly(1))->method('loadVersionInfoById')->with(...$parameters);

        $decoratedService->loadVersionInfoById(...$parameters);
    }

    public function testLoadContentByContentInfoDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentInfo::class),
            ['random_value_5ced05ce154263.26527866'],
            'random_value_5ced05ce154274.48633738',
            'random_value_5ced05ce154288.12181629',
        ];

        $serviceMock->expects($this->exactly(1))->method('loadContentByContentInfo')->with(...$parameters);

        $decoratedService->loadContentByContentInfo(...$parameters);
    }

    public function testLoadContentByVersionInfoDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(VersionInfo::class),
            ['random_value_5ced05ce154b93.03098248'],
            'random_value_5ced05ce154bc5.33425740',
        ];

        $serviceMock->expects($this->exactly(1))->method('loadContentByVersionInfo')->with(...$parameters);

        $decoratedService->loadContentByVersionInfo(...$parameters);
    }

    public function testLoadContentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce154c25.68443488',
            ['random_value_5ced05ce154c34.23275680'],
            'random_value_5ced05ce154c41.25945372',
            'random_value_5ced05ce154c55.22828466',
        ];

        $serviceMock->expects($this->exactly(1))->method('loadContent')->with(...$parameters);

        $decoratedService->loadContent(...$parameters);
    }

    public function testLoadContentByRemoteIdDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce154c88.14138266',
            ['random_value_5ced05ce154c96.62330223'],
            'random_value_5ced05ce154ca4.47689455',
            'random_value_5ced05ce154cb6.74747836',
        ];

        $serviceMock->expects($this->exactly(1))->method('loadContentByRemoteId')->with(...$parameters);

        $decoratedService->loadContentByRemoteId(...$parameters);
    }

    public function testLoadContentListByContentInfoDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            ['random_value_5ced05ce154ce0.53430215'],
            ['random_value_5ced05ce154cf5.73360157'],
            'random_value_5ced05ce154d01.47432661',
        ];

        $serviceMock->expects($this->exactly(1))->method('loadContentListByContentInfo')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('createContent')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('updateContentMetadata')->with(...$parameters);

        $decoratedService->updateContentMetadata(...$parameters);
    }

    public function testDeleteContentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentInfo::class)];

        $serviceMock->expects($this->exactly(1))->method('deleteContent')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('createContentDraft')->with(...$parameters);

        $decoratedService->createContentDraft(...$parameters);
    }

    public function testLoadContentDraftsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(User::class)];

        $serviceMock->expects($this->exactly(1))->method('loadContentDrafts')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('updateContent')->with(...$parameters);

        $decoratedService->updateContent(...$parameters);
    }

    public function testPublishVersionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(VersionInfo::class)];

        $serviceMock->expects($this->exactly(1))->method('publishVersion')->with(...$parameters);

        $decoratedService->publishVersion(...$parameters);
    }

    public function testDeleteVersionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(VersionInfo::class)];

        $serviceMock->expects($this->exactly(1))->method('deleteVersion')->with(...$parameters);

        $decoratedService->deleteVersion(...$parameters);
    }

    public function testLoadVersionsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentInfo::class)];

        $serviceMock->expects($this->exactly(1))->method('loadVersions')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('copyContent')->with(...$parameters);

        $decoratedService->copyContent(...$parameters);
    }

    public function testLoadRelationsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(VersionInfo::class)];

        $serviceMock->expects($this->exactly(1))->method('loadRelations')->with(...$parameters);

        $decoratedService->loadRelations(...$parameters);
    }

    public function testLoadReverseRelationsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentInfo::class)];

        $serviceMock->expects($this->exactly(1))->method('loadReverseRelations')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('addRelation')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('deleteRelation')->with(...$parameters);

        $decoratedService->deleteRelation(...$parameters);
    }

    public function testRemoveTranslationDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentInfo::class),
            'random_value_5ced05ce156ca0.26332407',
        ];

        $serviceMock->expects($this->exactly(1))->method('removeTranslation')->with(...$parameters);

        $decoratedService->removeTranslation(...$parameters);
    }

    public function testDeleteTranslationDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentInfo::class),
            'random_value_5ced05ce156d02.84155908',
        ];

        $serviceMock->expects($this->exactly(1))->method('deleteTranslation')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('deleteTranslationFromDraft')->with(...$parameters);

        $decoratedService->deleteTranslationFromDraft(...$parameters);
    }

    public function testHideContentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentInfo::class)];

        $serviceMock->expects($this->exactly(1))->method('hideContent')->with(...$parameters);

        $decoratedService->hideContent(...$parameters);
    }

    public function testRevealContentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentInfo::class)];

        $serviceMock->expects($this->exactly(1))->method('revealContent')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('newContentCreateStruct')->with(...$parameters);

        $decoratedService->newContentCreateStruct(...$parameters);
    }

    public function testNewContentMetadataUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->exactly(1))->method('newContentMetadataUpdateStruct')->with(...$parameters);

        $decoratedService->newContentMetadataUpdateStruct(...$parameters);
    }

    public function testNewContentUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->exactly(1))->method('newContentUpdateStruct')->with(...$parameters);

        $decoratedService->newContentUpdateStruct(...$parameters);
    }
}
