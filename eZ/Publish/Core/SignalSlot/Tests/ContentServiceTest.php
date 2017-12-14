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
            array('parentLocationId' => $copyParentLocationId)
        );
        $contentMetadataUpdateStruct = new ContentMetadataUpdateStruct();
        $contentUpdateStruct = new ContentUpdateStruct();

        $contentType = new ContentType(
            array(
                'fieldDefinitions' => array(),
            )
        );
        $contentInfo = $this->getContentInfo($contentId, $remoteId);
        $versionInfo = $this->getVersionInfo($contentInfo, $versionNo);
        $content = $this->getContent($versionInfo);

        $user = $this->getUser($userId, md5('Sauron'), $userVersionNo);
        $usersDraft = array($versionInfo);

        $copiedContent = $this->getContent(
            $this->getVersionInfo(
                $this->getContentInfo($copiedContentId, md5('Backup ring, just in case ;-)')),
                $copiedVersionNo
            )
        );

        $relations = array(new Relation());
        $newRelation = new Relation();
        $relationDestContentInfo = $this->getContentInfo($relationDestContentId, md5('Mordor'));

        $translationInfoFilter = array();

        return array(
            array(
                'loadContentInfo',
                array($contentId),
                $contentInfo,
                0,
            ),
            array(
                'loadContentInfoByRemoteId',
                array($remoteId),
                $contentInfo,
                0,
            ),
            array(
                'loadVersionInfo',
                array($contentInfo, $versionNo),
                $versionInfo,
                0,
            ),
            array(
                'loadVersionInfoById',
                array($contentId, $versionNo),
                $versionInfo,
                0,
            ),
            array(
                'loadContentByContentInfo',
                array($contentInfo, array($language), $versionNo, true),
                $content,
                0,
            ),
            array(
                'loadContentByVersionInfo',
                array($versionInfo, array($language), true),
                $content,
                0,
            ),
            array(
                'loadContent',
                array($contentId, array($language), $versionNo, true),
                $content,
                0,
            ),
            array(
                'loadContentByRemoteId',
                array($remoteId, array($language), $versionNo, true),
                $content,
                0,
            ),
            array(
                'createContent',
                array($contentCreateStruct, array($locationCreateStruct)),
                $content,
                1,
                ContentServiceSignals\CreateContentSignal::class,
                array(
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                ),
            ),
            array(
                'updateContentMetadata',
                array($contentInfo, $contentMetadataUpdateStruct),
                $content,
                1,
                ContentServiceSignals\UpdateContentMetadataSignal::class,
                array('contentId' => $contentId),
            ),
            array(
                'deleteContent',
                array($contentInfo),
                $contentInfo,
                1,
                ContentServiceSignals\DeleteContentSignal::class,
                array('contentId' => $contentId),
            ),
            array(
                'createContentDraft',
                array($contentInfo, $versionInfo, $user),
                $content,
                1,
                ContentServiceSignals\CreateContentDraftSignal::class,
                array(
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                    'userId' => $userId,
                ),
            ),
            array(
                'loadContentDrafts',
                array($user),
                $usersDraft,
                0,
            ),
            array(
                'updateContent',
                array($versionInfo, $contentUpdateStruct),
                $content,
                1,
                ContentServiceSignals\UpdateContentSignal::class,
                array(
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                ),
            ),
            array(
                'publishVersion',
                array($versionInfo),
                $content,
                1,
                ContentServiceSignals\PublishVersionSignal::class,
                array(
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                ),
            ),
            array(
                'deleteVersion',
                array($versionInfo),
                $versionInfo,
                1,
                ContentServiceSignals\DeleteVersionSignal::class,
                array(
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                ),
            ),
            array(
                'loadVersions',
                array($contentInfo),
                array($versionInfo),
                0,
            ),
            array(
                'copyContent',
                array($contentInfo, $copyLocationCreateStruct, $versionInfo),
                $copiedContent,
                1,
                ContentServiceSignals\CopyContentSignal::class,
                array(
                    'srcContentId' => $contentId,
                    'srcVersionNo' => $versionNo,
                    'dstContentId' => $copiedContentId,
                    'dstVersionNo' => $copiedVersionNo,
                    'dstParentLocationId' => $copyParentLocationId,
                ),
            ),
            array(
                'loadRelations',
                array($versionInfo),
                $relations,
                0,
            ),
            array(
                'loadReverseRelations',
                array($contentInfo),
                $relations,
                0,
            ),
            array(
                'addRelation',
                array($versionInfo, $relationDestContentInfo),
                $newRelation,
                1,
                ContentServiceSignals\AddRelationSignal::class,
                array(
                    'srcContentId' => $contentId,
                    'srcVersionNo' => $versionNo,
                    'dstContentId' => $relationDestContentId,
                ),
            ),
            array(
                'deleteRelation',
                array($versionInfo, $relationDestContentInfo),
                null,
                1,
                ContentServiceSignals\DeleteRelationSignal::class,
                array(
                    'srcContentId' => $contentId,
                    'srcVersionNo' => $versionNo,
                    'dstContentId' => $relationDestContentId,
                ),
            ),
            array(
                'deleteTranslation',
                array($contentInfo, $language),
                null,
                2,
                DeleteTranslationSignal::class,
                array('contentId' => $contentId, 'languageCode' => $language),
            ),
            array(
                'newContentCreateStruct',
                array($contentType, $language),
                array($contentCreateStruct),
                0,
            ),
            array(
                'newContentMetadataUpdateStruct',
                array(),
                array($contentMetadataUpdateStruct),
                0,
            ),
            array(
                'newContentUpdateStruct',
                array(),
                array($contentUpdateStruct),
                0,
            ),
        );
    }
}
