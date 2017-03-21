<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\API\Repository\Values\Content\Relation as APIRelation;
use eZ\Publish\SPI\Persistence\Content\Relation as SPIRelation;
use eZ\Publish\Core\Persistence\Cache\ContentHandler;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Handler;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * Test case for Persistence\Cache\ContentHandler.
 */
class ContentHandlerTest extends AbstractCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'contentHandler';
    }

    public function getHandlerClassName(): string
    {
        return Handler::class;
    }

    /**
     * @return array
     */
    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tags, string? $key
        return [
            ['create', [new CreateStruct()]],
            ['createDraftFromVersion', [2, 1, 14]],
            ['copy', [2, 1]],
            ['loadDraftsForUser', [14]],
            ['setStatus', [2, 0, 1], ['content-2-version-list'], 'ez-content-version-info-2-1'],
            ['setStatus', [2, 1, 1], ['content-2'], 'ez-content-version-info-2-1'],
            ['updateMetadata', [2, new MetadataUpdateStruct()], ['content-2']],
            ['updateContent', [2, 1, new UpdateStruct()], ['content-2']],
            //['deleteContent', [2]], own tests for relations complexity
            ['deleteVersion', [2, 1], ['content-2']],
            ['addRelation', [new RelationCreateStruct()]],
            ['removeRelation', [66, APIRelation::COMMON]],
            ['loadRelations', [2, 1, 3]],
            ['loadReverseRelations', [2, 3]],
            ['publish', [2, 3, new MetadataUpdateStruct()], ['content-2']],
        ];
    }

    /**
     * @return array
     */
    public function providerForCachedLoadMethods(): array
    {
        $info = new ContentInfo(['id' => 2]);
        $version = new VersionInfo(['versionNo' => 1, 'contentInfo' => $info]);
        $content = new Content(['fields' => [], 'versionInfo' => $version]);

        // string $method, array $arguments, string $key, mixed? $data
        return [
            ['load', [2, 1], 'ez-content-2-1-' . ContentHandler::ALL_TRANSLATIONS_KEY, $content],
            ['load', [2, 1, ['eng-GB', 'eng-US']], 'ez-content-2-1-eng-GB|eng-US', $content],
            ['loadContentInfo', [2], 'ez-content-info-2', $info],
            ['loadContentInfoByRemoteId', ['3d8jrj'], 'ez-content-info-byRemoteId-3d8jrj', $info],
            ['loadVersionInfo', [2, 1], 'ez-content-version-info-2-1', $version],
            ['listVersions', [2], 'ez-content-2-version-list', [$version]],
            ['listVersions', [2, 1], 'ez-content-2-version-list-byStatus-1', [$version]],
        ];
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentHandler::deleteContent
     */
    public function testDeleteContent()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->exactly(2))
            ->method('contentHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('loadReverseRelations')
            ->with(2, APIRelation::FIELD)
            ->will(
                $this->returnValue(
                    array(
                        new SPIRelation(array('sourceContentId' => 42)),
                    )
                )
            );

        $innerHandlerMock
            ->expects($this->once())
            ->method('deleteContent')
            ->with(2)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->never())
            ->method('deleteItem');

        $this->cacheMock
            ->expects($this->at(0))
            ->method('invalidateTags')
            ->with(['content-2']);

        $this->cacheMock
            ->expects($this->at(1))
            ->method('invalidateTags')
            ->with(['content-fields-42']);

        $handler = $this->persistenceCacheHandler->contentHandler();
        $handler->deleteContent(2);
    }
}
