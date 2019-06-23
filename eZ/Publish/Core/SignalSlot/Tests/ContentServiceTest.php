<?php

/**
 * File containing the ContentServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\ContentService as APIContentService;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\Core\Repository\Values\Content\Relation;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteTranslationSignal;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\ContentService;
use eZ\Publish\Core\SignalSlot\Signal\ContentService as ContentServiceSignals;

class ContentServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->createMock(APIContentService::class);
    }

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new ContentService($coreService, $dispatcher);
    }

    public function serviceProvider()
    {
        $contentId = 42;
        $versionNo = 2;
        $remoteId = md5('One Ring to rule them all');
        $language = 'fre-FR';
        $userId = 14;
        $userVersionNo = 5;
        $copiedContentId = 43;
        $copiedVersionNo = 1;
        $copyParentLocationId = 50;
        $relationDestContentId = 44;

        $contentCreateStruct = new ContentCreateStruct();
        $locationCreateStruct = new LocationCreateStruct();
        $copyLocationCreateStruct = new LocationCreateStruct(
            ['parentLocationId' => $copyParentLocationId]
        );
        $contentMetadataUpdateStruct = new ContentMetadataUpdateStruct();
        $contentUpdateStruct = new ContentUpdateStruct();

        $contentType = new ContentType(
            [
                'fieldDefinitions' => [],
            ]
        );
        $contentInfo = $this->getContentInfo($contentId, $remoteId);
        $versionInfo = $this->getVersionInfo($contentInfo, $versionNo);
        $content = $this->getContent($versionInfo);

        $user = $this->getUser($userId, md5('Sauron'), $userVersionNo);
        $usersDraft = [$versionInfo];

        $copiedContent = $this->getContent(
            $this->getVersionInfo(
                $this->getContentInfo($copiedContentId, md5('Backup ring, just in case ;-)')),
                $copiedVersionNo
            )
        );

        $relations = [new Relation()];
        $newRelation = new Relation();
        $relationDestContentInfo = $this->getContentInfo($relationDestContentId, md5('Mordor'));

        $translationInfoFilter = [];

        return [
            [
                'loadContentInfo',
                [$contentId],
                $contentInfo,
                0,
            ],
            [
                'loadContentInfoList',
                [[$contentId]],
                [$contentInfo],
                0,
            ],
            [
                'loadContentInfoByRemoteId',
                [$remoteId],
                $contentInfo,
                0,
            ],
            [
                'loadVersionInfo',
                [$contentInfo, $versionNo],
                $versionInfo,
                0,
            ],
            [
                'loadVersionInfoById',
                [$contentId, $versionNo],
                $versionInfo,
                0,
            ],
            [
                'loadContentByContentInfo',
                [$contentInfo, [$language], $versionNo, true],
                $content,
                0,
            ],
            [
                'loadContentByVersionInfo',
                [$versionInfo, [$language], true],
                $content,
                0,
            ],
            [
                'loadContent',
                [$contentId, [$language], $versionNo, true],
                $content,
                0,
            ],
            [
                'loadContentByRemoteId',
                [$remoteId, [$language], $versionNo, true],
                $content,
                0,
            ],
            [
                'createContent',
                [$contentCreateStruct, [$locationCreateStruct]],
                $content,
                1,
                ContentServiceSignals\CreateContentSignal::class,
                [
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                ],
            ],
            [
                'updateContentMetadata',
                [$contentInfo, $contentMetadataUpdateStruct],
                $content,
                1,
                ContentServiceSignals\UpdateContentMetadataSignal::class,
                ['contentId' => $contentId],
            ],
            [
                'deleteContent',
                [$contentInfo],
                $contentInfo,
                1,
                ContentServiceSignals\DeleteContentSignal::class,
                ['contentId' => $contentId],
            ],
            [
                'createContentDraft',
                [$contentInfo, $versionInfo, $user],
                $content,
                1,
                ContentServiceSignals\CreateContentDraftSignal::class,
                [
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                    'newVersionNo' => $content->getVersionInfo()->versionNo,
                    'userId' => $userId,
                ],
            ],
            [
                'loadContentDrafts',
                [$user],
                $usersDraft,
                0,
            ],
            [
                'updateContent',
                [$versionInfo, $contentUpdateStruct],
                $content,
                1,
                ContentServiceSignals\UpdateContentSignal::class,
                [
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                ],
            ],
            [
                'publishVersion',
                [$versionInfo, []],
                $content,
                1,
                ContentServiceSignals\PublishVersionSignal::class,
                [
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                ],
            ],
            [
                'deleteVersion',
                [$versionInfo],
                $versionInfo,
                1,
                ContentServiceSignals\DeleteVersionSignal::class,
                [
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                ],
            ],
            [
                'loadVersions',
                [$contentInfo, null],
                [$versionInfo],
                0,
            ],
            [
                'copyContent',
                [$contentInfo, $copyLocationCreateStruct, $versionInfo],
                $copiedContent,
                1,
                ContentServiceSignals\CopyContentSignal::class,
                [
                    'srcContentId' => $contentId,
                    'srcVersionNo' => $versionNo,
                    'dstContentId' => $copiedContentId,
                    'dstVersionNo' => $copiedVersionNo,
                    'dstParentLocationId' => $copyParentLocationId,
                ],
            ],
            [
                'loadRelations',
                [$versionInfo],
                $relations,
                0,
            ],
            [
                'loadReverseRelations',
                [$contentInfo],
                $relations,
                0,
            ],
            [
                'addRelation',
                [$versionInfo, $relationDestContentInfo],
                $newRelation,
                1,
                ContentServiceSignals\AddRelationSignal::class,
                [
                    'srcContentId' => $contentId,
                    'srcVersionNo' => $versionNo,
                    'dstContentId' => $relationDestContentId,
                ],
            ],
            [
                'deleteRelation',
                [$versionInfo, $relationDestContentInfo],
                null,
                1,
                ContentServiceSignals\DeleteRelationSignal::class,
                [
                    'srcContentId' => $contentId,
                    'srcVersionNo' => $versionNo,
                    'dstContentId' => $relationDestContentId,
                ],
            ],
            [
                'deleteTranslation',
                [$contentInfo, $language],
                null,
                2,
                DeleteTranslationSignal::class,
                ['contentId' => $contentId, 'languageCode' => $language],
            ],
            [
                'newContentCreateStruct',
                [$contentType, $language],
                [$contentCreateStruct],
                0,
            ],
            [
                'newContentMetadataUpdateStruct',
                [],
                [$contentMetadataUpdateStruct],
                0,
            ],
            [
                'newContentUpdateStruct',
                [],
                [$contentUpdateStruct],
                0,
            ],
        ];
    }
}
