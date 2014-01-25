<?php
/**
 * File containing the ContentServiceTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\Repository\DomainLogic\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\TranslationInfo;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\TranslationValues;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\Relation;
use eZ\Publish\Core\Repository\DomainLogic\Values\ContentType\ContentType;

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

    protected function getSignalSlotService( $coreService, SignalDispatcher $dispatcher )
    {
        return new ContentService( $coreService, $dispatcher );
    }

    public function serviceProvider()
    {
        $contentId = 42;
        $versionNo = 2;
        $remoteId = md5( 'One Ring to rule them all' );
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
            array( 'parentLocationId' => $copyParentLocationId )
        );
        $contentMetadataUpdateStruct = new ContentMetadataUpdateStruct();
        $contentUpdateStruct = new ContentUpdateStruct();

        $contentType = new ContentType(
            array(
                'fieldDefinitions' => array()
            )
        );
        $contentInfo = $this->getContentInfo( $contentId, $remoteId );
        $versionInfo = $this->getVersionInfo( $contentInfo, $versionNo );
        $content = $this->getContent( $versionInfo );
        $translationInfo = new TranslationInfo(
            array(
                'srcVersionInfo' => $versionInfo
            )
        );
        $translationValues = new TranslationValues();

        $user = $this->getUser( $userId, md5( 'Sauron' ), $userVersionNo );
        $usersDraft = array( $versionInfo );

        $copiedContent = $this->getContent(
            $this->getVersionInfo(
                $this->getContentInfo(
                    $copiedContentId, md5( 'Backup ring, just in case ;-)' )
                ),
                $copiedVersionNo
            )
        );

        $relations = array( new Relation() );
        $newRelation = new Relation();
        $relationDestContentInfo = $this->getContentInfo(
            $relationDestContentId, md5( 'Mordor' )
        );

        $translationInfoFilter = array();

        return array(
            array(
                'loadContentInfo',
                array( $contentId ),
                $contentInfo,
                0
            ),
            array(
                'loadContentInfoByRemoteId',
                array( $remoteId ),
                $contentInfo,
                0
            ),
            array(
                'loadVersionInfo',
                array( $contentInfo, $versionNo ),
                $versionInfo,
                0
            ),
            array(
                'loadVersionInfoById',
                array( $contentId, $versionNo ),
                $versionInfo,
                0
            ),
            array(
                'loadContentByContentInfo',
                array( $contentInfo, array( $language ), $versionNo ),
                $content,
                0
            ),
            array(
                'loadContentByVersionInfo',
                array( $versionInfo, array( $language ) ),
                $content,
                0
            ),
            array(
                'loadContent',
                array( $contentId, array( $language ), $versionNo ),
                $content,
                0
            ),
            array(
                'loadContentByRemoteId',
                array( $remoteId, array( $language ), $versionNo ),
                $content,
                0
            ),
            array(
                'createContent',
                array( $contentCreateStruct, array( $locationCreateStruct ) ),
                $content,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\CreateContentSignal',
                array(
                    'contentId' => $contentId,
                    'versionNo' => $versionNo
                )
            ),
            array(
                'updateContentMetadata',
                array( $contentInfo, $contentMetadataUpdateStruct ),
                $content,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\UpdateContentMetadataSignal',
                array( 'contentId' => $contentId )
            ),
            array(
                'deleteContent',
                array( $contentInfo ),
                $contentInfo,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteContentSignal',
                array( 'contentId' => $contentId )
            ),
            array(
                'createContentDraft',
                array( $contentInfo, $versionInfo, $user ),
                $content,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\CreateContentDraftSignal',
                array(
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                    'userId' => $userId
                )
            ),
            array(
                'loadContentDrafts',
                array( $user ),
                $usersDraft,
                0
            ),
            array(
                'translateVersion',
                array( $translationInfo, $translationValues, $user ),
                $content,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\TranslateVersionSignal',
                array(
                    'contentId' => $contentId,
                    'versionNo' => $versionNo,
                    'userId' => $userId
                )
            ),
            array(
                'updateContent',
                array( $versionInfo, $contentUpdateStruct ),
                $content,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\UpdateContentSignal',
                array(
                    'contentId' => $contentId,
                    'versionNo' => $versionNo
                )
            ),
            array(
                'publishVersion',
                array( $versionInfo ),
                $content,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal',
                array(
                    'contentId' => $contentId,
                    'versionNo' => $versionNo
                )
            ),
            array(
                'deleteVersion',
                array( $versionInfo ),
                $versionInfo,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteVersionSignal',
                array(
                    'contentId' => $contentId,
                    'versionNo' => $versionNo
                )
            ),
            array(
                'loadVersions',
                array( $contentInfo ),
                array( $versionInfo ),
                0
            ),
            array(
                'copyContent',
                array( $contentInfo, $copyLocationCreateStruct, $versionInfo ),
                $copiedContent,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\CopyContentSignal',
                array(
                    'srcContentId' => $contentId,
                    'srcVersionNo' => $versionNo,
                    'dstContentId' => $copiedContentId,
                    'dstVersionNo' => $copiedVersionNo,
                    'dstParentLocationId' => $copyParentLocationId
                )
            ),
            array(
                'loadRelations',
                array( $versionInfo ),
                $relations,
                0
            ),
            array(
                'loadReverseRelations',
                array( $contentInfo ),
                $relations,
                0
            ),
            array(
                'addRelation',
                array( $versionInfo, $relationDestContentInfo ),
                $newRelation,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\AddRelationSignal',
                array(
                    'srcContentId' => $contentId,
                    'srcVersionNo' => $versionNo,
                    'dstContentId' => $relationDestContentId
                )
            ),
            array(
                'deleteRelation',
                array( $versionInfo, $relationDestContentInfo ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteRelationSignal',
                array(
                    'srcContentId' => $contentId,
                    'srcVersionNo' => $versionNo,
                    'dstContentId' => $relationDestContentId
                )
            ),
            array(
                'addTranslationInfo',
                array( $translationInfo ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentService\AddTranslationInfoSignal',
                array()
            ),
            array(
                'loadTranslationInfos',
                array( $contentInfo, $translationInfoFilter ),
                array( $translationInfo ),
                0
            ),
            array(
                'newContentCreateStruct',
                array( $contentType, $language ),
                array( $contentCreateStruct ),
                0
            ),
            array(
                'newContentMetadataUpdateStruct',
                array(),
                array( $contentMetadataUpdateStruct ),
                0
            ),
            array(
                'newContentUpdateStruct',
                array(),
                array( $contentUpdateStruct ),
                0
            ),
            array(
                'newTranslationInfo',
                array(),
                array( $translationInfo ),
                0
            ),
            array(
                'newTranslationValues',
                array(),
                array( $translationValues ),
                0
            ),
        );
    }
}
