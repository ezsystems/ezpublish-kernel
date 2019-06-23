<?php

/**
 * File containing the ContentServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\TranslationInfo;
use eZ\Publish\Core\Repository\Values\Content\TranslationValues;
use eZ\Publish\Core\Repository\Values\Content\Relation;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\ContentService;

class ContentServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\ContentService'
        );
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
        $translationInfo = new TranslationInfo(
            [
                'srcVersionInfo' => $versionInfo,
            ]
        );
        $translationValues = new TranslationValues();

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
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\CreateContentSignal',
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
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\UpdateContentMetadataSignal',
                ['contentId' => $contentId],
            ],
            [
                'deleteContent',
                [$contentInfo],
                $contentInfo,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteContentSignal',
                ['contentId' => $contentId],
            ],
            [
                'createContentDraft',
                [$contentInfo, $versionInfo, $user],
                $content,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\CreateContentDraftSignal',
                [
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
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
                'translateVersion',
                [$translationInfo, $translationValues, $user],
                $content,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\TranslateVersionSignal',
                [
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                    'userId' => $userId,
                ],
            ],
            [
                'updateContent',
                [$versionInfo, $contentUpdateStruct],
                $content,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\UpdateContentSignal',
                [
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                ],
            ],
            [
                'publishVersion',
                [$versionInfo],
                $content,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal',
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
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteVersionSignal',
                [
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                ],
            ],
            [
                'loadVersions',
                [$contentInfo],
                [$versionInfo],
                0,
            ],
            [
                'copyContent',
                [$contentInfo, $copyLocationCreateStruct, $versionInfo],
                $copiedContent,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\CopyContentSignal',
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
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\AddRelationSignal',
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
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteRelationSignal',
                [
                    'srcContentId' => $contentId,
                    'srcVersionNo' => $versionNo,
                    'dstContentId' => $relationDestContentId,
                ],
            ],
            [
                'addTranslationInfo',
                [$translationInfo],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\AddTranslationInfoSignal',
                [],
            ],
            [
                'loadTranslationInfos',
                [$contentInfo, $translationInfoFilter],
                [$translationInfo],
                0,
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
            [
                'newTranslationInfo',
                [],
                [$translationInfo],
                0,
            ],
            [
                'newTranslationValues',
                [],
                [$translationValues],
                0,
            ],
        ];
    }
}
