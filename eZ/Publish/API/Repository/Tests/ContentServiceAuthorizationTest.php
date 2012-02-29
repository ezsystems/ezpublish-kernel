<?php
/**
 * File containing the ContentServiceAuthorizationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the ContentServiceAuthorization using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentServiceAuthorization
 */
class ContentServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateContentThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateContentThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContent() is not implemented." );
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentInfo()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentInfoThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentInfo() is not implemented." );
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentInfoByRemoteId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentInfoByRemoteIdThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContenInfotByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfo()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadVersionInfoThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadVersionInfo() is not implemented." );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfo($contentInfo, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadVersionInfoThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadVersionInfo() is not implemented." );
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadVersionInfoByIdThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadVersionInfoById() is not implemented." );
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById($contentId, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadVersionInfoByIdThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadVersionInfoById() is not implemented." );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentByContentInfoThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByContentInfo() is not implemented." );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentByContentInfoThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByContentInfo() is not implemented." );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentByContentInfoThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByContentInfo() is not implemented." );
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByVersionInfo()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentByVersionInfoThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByVersionInfo() is not implemented." );
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByVersionInfo($versionInfo, $languages)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentByVersionInfoThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByVersionInfo() is not implemented." );
    }

    /**
     * Test for the loadContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContent() is not implemented." );
    }

    /**
     * Test for the loadContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContent() is not implemented." );
    }

    /**
     * Test for the loadContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContent() is not implemented." );
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentByRemoteIdThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentByRemoteIdThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentByRemoteIdThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByRemoteId() is not implemented." );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateContentMetadataThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::updateContentMetadata() is not implemented." );
    }

    /**
     * Test for the deleteContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteContentThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::deleteContent() is not implemented." );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateContentDraftThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContentDraft() is not implemented." );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft($contentInfo, $versionInfo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateContentDraftThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContentDraft() is not implemented." );
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentDrafts()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentDraftsThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentDrafts() is not implemented." );
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentDrafts($user)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentDraftsThrowsUnauthorizedExceptionWithFirstParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentDrafts() is not implemented." );
    }

    /**
     * Test for the translateVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::translateVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testTranslateVersionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::translateVersion() is not implemented." );
    }

    /**
     * Test for the translateVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::translateVersion($translationInfo, $translationValues, $user)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testTranslateVersionThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::translateVersion() is not implemented." );
    }

    /**
     * Test for the updateContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateContentThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::updateContent() is not implemented." );
    }

    /**
     * Test for the publishVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testPublishVersionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::publishVersion() is not implemented." );
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteVersionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::deleteVersion() is not implemented." );
    }

    /**
     * Test for the loadVersions() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersions()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadVersionsThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadVersions() is not implemented." );
    }

    /**
     * Test for the copyContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::copyContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCopyContentThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::copyContent() is not implemented." );
    }

    /**
     * Test for the copyContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::copyContent($contentInfo, $destinationLocationCreateStruct, $versionInfo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCopyContentThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::copyContent() is not implemented." );
    }

    /**
     * Test for the findSingle() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findSingle()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindSingleThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::findSingle() is not implemented." );
    }

    /**
     * Test for the findSingle() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findSingle($query, $fieldFilters, $filterOnUserPermissions)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindSingleThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::findSingle() is not implemented." );
    }

    /**
     * Test for the loadRelations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadRelations()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadRelationsThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadRelations() is not implemented." );
    }

    /**
     * Test for the loadReverseRelations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadReverseRelations()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadReverseRelationsThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadReverseRelations() is not implemented." );
    }

    /**
     * Test for the addRelation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::addRelation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAddRelationThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::addRelation() is not implemented." );
    }

    /**
     * Test for the deleteRelation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteRelation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteRelationThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::deleteRelation() is not implemented." );
    }

    /**
     * Test for the addTranslationInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::addTranslationInfo()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAddTranslationInfoThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::addTranslationInfo() is not implemented." );
    }

    /**
     * Test for the loadTranslationInfos() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadTranslationInfos()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadTranslationInfosThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadTranslationInfos() is not implemented." );
    }

    /**
     * Test for the loadTranslationInfos() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadTranslationInfos($contentInfo, $filter)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadTranslationInfosThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadTranslationInfos() is not implemented." );
    }

    /**
     * Test for the findContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findContent($query, $fieldFilters, $filterOnUserPermissions)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testFindContent
     */
    public function testFindContentWithUserPermissionFilter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::findContent() is not implemented." );
    }

    /**
     * Test for the findSingle() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findSingle($query, $fieldFilters, $filterOnUserPermissions)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testFindContent
     */
    public function testFindSingleWithUserPermissionFilter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::findSingle() is not implemented." );
    }

}