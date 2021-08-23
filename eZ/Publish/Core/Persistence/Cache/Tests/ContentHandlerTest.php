<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\API\Repository\Values\Content\Relation as APIRelation;
use eZ\Publish\SPI\Persistence\Content\Relation as SPIRelation;
use eZ\Publish\Core\Persistence\Cache\ContentHandler;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Handler as SPIContentHandler;

/**
 * Test case for Persistence\Cache\ContentHandler.
 */
class ContentHandlerTest extends AbstractInMemoryCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'contentHandler';
    }

    public function getHandlerClassName(): string
    {
        return SPIContentHandler::class;
    }

    /**
     * @return array
     */
    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tags, array? $key, ?mixed $returnValue
        return [
            ['create', [new CreateStruct()]],
            ['createDraftFromVersion', [2, 1, 14], [['content_version_list', [2], true]], [], ['ez-c-2-vl']],
            ['copy', [2, 1]],
            ['loadDraftsForUser', [14]],
            ['setStatus', [2, 0, 1], [['content_version', [2, 1], false]], ['c-2-v-1']],
            ['setStatus', [2, 1, 1], [['content', [2], false]], ['c-2']],
            ['updateMetadata', [2, new MetadataUpdateStruct()], [['content', [2], false]], ['c-2']],
            ['updateContent', [2, 1, new UpdateStruct()], [['content_version', [2, 1], false]], ['c-2-v-1']],
            //['deleteContent', [2]], own tests for relations complexity
            ['deleteVersion', [2, 1], [['content_version', [2, 1], false]], ['c-2-v-1']],
            ['addRelation', [new RelationCreateStruct()]],
            ['removeRelation', [66, APIRelation::COMMON]],
            ['loadRelations', [2, 1, 3]],
            ['loadReverseRelations', [2, 3]],
            ['publish', [2, 3, new MetadataUpdateStruct()], [['content', [2], false]], ['c-2']],
            ['listVersions', [2, 1], [['content', [2], false]], [], [], [new VersionInfo(['versionNo' => 1, 'contentInfo' => new ContentInfo(['id' => 2])])]],
        ];
    }

    /**
     * @return array
     */
    public function providerForCachedLoadMethodsHit(): array
    {
        $info = new ContentInfo(['id' => 2]);
        $version = new VersionInfo(['versionNo' => 1, 'contentInfo' => $info]);
        $content = new Content(['fields' => [], 'versionInfo' => $version]);

        // string $method, array $arguments, string $key, array? $tagGeneratorArguments, array? $tagGeneratorResults, mixed? $data, bool $multi = false, array $additionalCalls
        return [
            ['load', [2, 1], 'ez-c-2-1-' . ContentHandler::ALL_TRANSLATIONS_KEY, [['content', [], true]], ['ez-c'], $content],
            ['load', [2, 1, ['eng-GB', 'eng-US']], 'ez-c-2-1-eng-GB|eng-US', [['content', [], true]], ['ez-c'], $content],
            ['load', [2], 'ez-c-2-' . ContentHandler::ALL_TRANSLATIONS_KEY, [['content', [], true]], ['ez-c'], $content],
            ['load', [2, null, ['eng-GB', 'eng-US']], 'ez-c-2-eng-GB|eng-US', [['content', [], true]], ['ez-c'], $content],
            ['loadContentList', [[2]], 'ez-c-2-' . ContentHandler::ALL_TRANSLATIONS_KEY, [['content', [], true]], ['ez-c'], [2 => $content], true],
            ['loadContentList', [[5], ['eng-GB', 'eng-US']], 'ez-c-5-eng-GB|eng-US', [['content', [], true]], ['ez-c'], [5 => $content], true],
            ['loadContentInfo', [2], 'ez-ci-2', [['content_info', [], true]], ['ez-ci'], $info],
            ['loadContentInfoList', [[2]], 'ez-ci-2', [['content_info', [], true]], ['ez-ci'], [2 => $info], true],
            ['loadContentInfoByRemoteId', ['3d8jrj'], 'ez-cibri-3d8jrj', [['content_info_by_remote_id', [], true]], ['ez-cibri'], $info],
            ['loadVersionInfo', [2, 1], 'ez-cvi-2-1', [['content_version_info', [2], true]], ['ez-cvi-2'], $version],
            ['loadVersionInfo', [2], 'ez-cvi-2', [['content_version_info', [2], true]], ['ez-cvi-2'], $version],
            ['listVersions', [2], 'ez-c-2-vl', [['content_version_list', [2], true]], ['ez-c-2-vl'], [$version]],
        ];
    }

    /**
     * @return array
     */
    public function providerForCachedLoadMethodsMiss(): array
    {
        $info = new ContentInfo([
            'id' => 2,
            'contentTypeId' => 3,
        ]);
        $version = new VersionInfo(['versionNo' => 1, 'contentInfo' => $info]);
        $content = new Content(['fields' => [], 'versionInfo' => $version]);

        // string $method, array $arguments, string $key, array? $tagGeneratorArguments, array? $tagGeneratorResults, mixed? $data, bool $multi = false, array $additionalCalls
        return [
            [
                'load',
                [2, 1],
                'ez-c-2-1-' . ContentHandler::ALL_TRANSLATIONS_KEY,
                [
                    ['content', [], true],
                    ['content_fields_type', [3], false],
                    ['content_version', [2, 1], false],
                    ['content', [2], false],
                ],
                ['ez-c', 'cft-2', 'c-2-v-1', 'c-2'],
                $content,
            ],
            [
                'load',
                [2, 1, ['eng-GB', 'eng-US']],
                'ez-c-2-1-eng-GB|eng-US',
                [
                    ['content', [], true],
                    ['content_fields_type', [3], false],
                    ['content_version', [2, 1], false],
                    ['content', [2], false],
                ],
                ['ez-c', 'cft-2', 'c-2-v-1', 'c-2'],
                $content,
            ],
            [
                'load',
                [2],
                'ez-c-2-' . ContentHandler::ALL_TRANSLATIONS_KEY,
                [
                    ['content', [], true],
                    ['content_fields_type', [3], false],
                    ['content_version', [2, 1], false],
                    ['content', [2], false],
                ],
                ['ez-c', 'cft-2', 'c-2-v-1', 'c-2'],
                $content,
            ],
            [
                'load',
                [2, null, ['eng-GB', 'eng-US']],
                'ez-c-2-eng-GB|eng-US',
                [
                    ['content', [], true],
                    ['content_fields_type', [3], false],
                    ['content_version', [2, 1], false],
                    ['content', [2], false],
                ],
                ['ez-c', 'cft-2', 'c-2-v-1', 'c-2'],
                $content,
            ],
            [
                'loadContentList',
                [[2]],
                'ez-c-2-' . ContentHandler::ALL_TRANSLATIONS_KEY,
                [
                    ['content', [], true],
                    ['content_fields_type', [3], false],
                    ['content_version', [2, 1], false],
                    ['content', [2], false],
                ],
                ['ez-c', 'cft-2', 'c-2-v-1', 'c-2'],
                [2 => $content],
                true,
            ],
            [
                'loadContentList',
                [[5], ['eng-GB', 'eng-US']],
                'ez-c-5-eng-GB|eng-US',
                [
                    ['content', [], true],
                    ['content_fields_type', [3], false],
                    ['content_version', [2, 1], false],
                    ['content', [2], false],
                ],
                ['ez-c', 'cft-2', 'c-2-v-1', 'c-2'],
                [5 => $content],
                true,
            ],
            [
                'loadContentInfo',
                [2],
                'ez-ci-2',
                [
                    ['content_info', [], true],
                    ['content', [2], false],
                ],
                ['ez-ci', 'c-2'],
                $info,
            ],
            [
                'loadContentInfoList',
                [[2]],
                'ez-ci-2',
                [
                    ['content_info', [], true],
                    ['content', [2], false],
                ],
                ['ez-ci', 'c-2'],
                [2 => $info],
                true,
            ],
            [
                'loadContentInfoByRemoteId',
                ['3d8jrj'], 'ez-cibri-3d8jrj',
                [
                    ['content_info_by_remote_id', [], true],
                    ['content', [2], false],
                ],
                ['ez-cibri', 'c-2'],
                $info,
            ],
            [
                'loadVersionInfo',
                [2, 1],
                'ez-cvi-2-1',
                [
                    ['content_version_info', [2], true],
                    ['content_version', [2, 1], false],
                    ['content', [2], false],
                ],
                ['ez-cvi-2', 'c-2-v-1', 'c-2'],
                $version,
            ],
            [
                'loadVersionInfo',
                [2],
                'ez-cvi-2',
                [
                    ['content_version_info', [2], true],
                    ['content_version', [2, 1], false],
                    ['content', [2], false],
                ],
                ['ez-cvi-2', 'c-2-v-1', 'c-2'],
                $version,
            ],
            [
                'listVersions',
                [2],
                'ez-c-2-vl',
                [
                    ['content_version_list', [2], true],
                    ['content', [2], false],
                    ['content_version', [2, 1], false],
                    ['content', [2], false],
                ],
                ['ez-c-2-vl', 'c-2', 'c-2-v-1', 'c-2'],
                [$version],
            ],
        ];
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentHandler::deleteContent
     */
    public function testDeleteContent()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->createMock(SPIContentHandler::class);
        $this->persistenceHandlerMock
            ->expects($this->exactly(2))
            ->method('contentHandler')
            ->willReturn($innerHandlerMock);

        $innerHandlerMock
            ->expects($this->once())
            ->method('loadReverseRelations')
            ->with(2, APIRelation::FIELD | APIRelation::ASSET)
            ->willReturn(
                [
                    new SPIRelation(['sourceContentId' => 42]),
                ]
            );

        $innerHandlerMock
            ->expects($this->once())
            ->method('deleteContent')
            ->with(2)
            ->willReturn(true);

        $this->cacheMock
            ->expects($this->never())
            ->method('deleteItem');

        $this->tagGeneratorMock
            ->expects($this->exactly(2))
            ->method('generate')
            ->withConsecutive(
                ['content', [42], false],
                ['content', [2], false]
            )
            ->willReturnOnConsecutiveCalls('c-42', 'c-2');

        $this->cacheMock
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(['c-42', 'c-2']);

        $handler = $this->persistenceCacheHandler->contentHandler();
        $handler->deleteContent(2);
    }
}
