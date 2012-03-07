<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\ContentBase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;
use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest,
    eZ\Publish\API\Repository\Exceptions;

/**
 * Test case for Content service
 */
abstract class ContentBase extends BaseServiceTest
{
    /**
     * Test for the loadContentInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfo()
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function testLoadContentInfo()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfo( 93 );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo',
            $contentInfo
        );

        return $contentInfo;
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @depends testLoadContentInfo
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfo()
     *
     * @param $contentInfo
     * @return void
     */
    public function testLoadContentInfoValues( $contentInfo )
    {
        // Legacy fixture content ID=93 values
        $expectedValues = array(
            "contentId"        => 93,
            "name"             => "Additional learning resources",
            "sectionId"        => 1,
            "currentVersionNo" => 1,
            "published"        => true,
            "ownerId"          => 14,
            "modificationDate" => new \DateTime( "@1175069321" ),
            "publishedDate"    => new \DateTime( "@1174389699" ),
            "alwaysAvailable"  => true,
            "remoteId"         => "34ceeed46f3ff3a5b29b8f96eb5d0d81",
            "mainLanguageCode" => "eng-US",
            "mainLocationId"   => 95
        );

        $this->assertPropertiesCorrect(
            $expectedValues,
            $contentInfo
        );
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfoByRemoteId()
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function testLoadContentInfoByRemoteId()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfoByRemoteId( "34ceeed46f3ff3a5b29b8f96eb5d0d81" );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo',
            $contentInfo
        );

        return $contentInfo;
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @depends testLoadContentInfoByRemoteId
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfoByRemoteId()
     *
     * @param $contentInfo
     * @return void
     */
    public function testLoadContentInfoByRemoteIdValues( $contentInfo )
    {
        // Legacy fixture content 93 values
        $expectedValues = array(
            "contentId"        => 93,
            "name"             => "Additional learning resources",
            "sectionId"        => 1,
            "currentVersionNo" => 1,
            "published"        => true,
            "ownerId"          => 14,
            "modificationDate" => new \DateTime( "@1175069321" ),
            "publishedDate"    => new \DateTime( "@1174389699" ),
            "alwaysAvailable"  => true,
            "remoteId"         => "34ceeed46f3ff3a5b29b8f96eb5d0d81",
            "mainLanguageCode" => "eng-US",
            "mainLocationId"   => 95
        );

        $this->assertPropertiesCorrect(
            $expectedValues,
            $contentInfo
        );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @depends testLoadContentInfo
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo()
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function testLoadVersionInfo()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $contentInfo = $contentService->loadContentInfo( 93 );

        $versionInfo = $contentService->loadVersionInfo( $contentInfo );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo',
            $versionInfo
        );

        return $versionInfo;
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @depends testLoadVersionInfo
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo()
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @return void
     */
    public function testLoadVersionInfoValues( $versionInfo )
    {
        // Legacy fixture content 93 current version (1) values
        $expectedValues = array(
            "id"                  => 540,
            "versionNo"           => 1,
            "modificationDate"    => new \DateTime( "@1175069321" ),
            "creatorId"           => 14,
            "creationDate"        => new \DateTime( "@1175069305" ),
            "status"              => 1,
            "initialLanguageCode" => "eng-US",
            "languageCodes"       => array( "eng-US" )
        );

        $this->assertPropertiesCorrect(
            $expectedValues,
            $versionInfo
        );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @depends testLoadContentInfo
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo()
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     * @todo test for different version
     */
    public function testLoadVersionInfoWithSecondParameter()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $contentInfo = $contentService->loadContentInfo( 56 );

        $versionInfo = $contentService->loadVersionInfo( $contentInfo, 1 );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo',
            $versionInfo
        );

        return $versionInfo;
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @depends testLoadVersionInfo
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo()
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @return void
     */
    public function testLoadVersionInfoWithSecondParameterValues( $versionInfo )
    {
        // Legacy fixture content 93 values
        $expectedValues = array(
            "id"                  => 540,
            "versionNo"           => 1,
            "modificationDate"    => new \DateTime( "@1175069321" ),
            "creatorId"           => 14,
            "creationDate"        => new \DateTime( "@1175069305" ),
            "status"              => 1,
            "initialLanguageCode" => "eng-US",
            "languageCodes"       => array( "eng-US" )
        );

        $this->assertPropertiesCorrect(
            $expectedValues,
            $versionInfo
        );
    }

    /**
     * Test for the newContentCreateStruct() method.
     *
     * @group current
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentCreateStruct()
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct
     */
    public function testNewContentCreateStruct()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $contentTypeService = $this->repository->getContentTypeService();

        $folderContentType = $contentTypeService->loadContentType( 1 );

        $contentCreateStruct = $contentService->newContentCreateStruct(
            $folderContentType,
            "eng-GB"
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentCreateStruct',
            $contentCreateStruct
        );

        return array(
            "contentType"   => $folderContentType,
            "contentCreateStruct" => $contentCreateStruct
        );
    }

    /**
     * Test for the newContentCreateStruct() method.
     *
     * @group current
     * @depends testNewContentCreateStruct
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentCreateStruct()
     *
     * @param array $data
     * @return void
     */
    public function testNewContentCreateStructValues( array $data )
    {
        $contentType         = $data["contentType"];
        $contentCreateStruct = $data["contentCreateStruct"];

        $expectedValues = array(
            "fields"           => array(),
            "contentType"      => $contentType,
            "sectionId"        => null,
            "ownerId"          => null,
            "alwaysAvailable"  => null,
            "remoteId"         => null,
            "mainLanguageCode" => "eng-GB",
            "modificationDate" => null
        );

        $this->assertPropertiesCorrect(
            $expectedValues,
            $contentCreateStruct
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @group current
     * @depends testNewContentCreateStruct
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testCreateContent()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'comment' );

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreate->setField( 'subject', 'Hello' );
        $contentCreate->setField( 'author', 'Kenneth Kaunda' );
        $contentCreate->setField( 'message', 'Regards from Nigeria' );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;

        $contentCreate->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        $content = $contentService->createContent( $contentCreate );
        /* END: Use Case */

        $this->assertInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content', $content );

        return $content;
    }

    /**
     * Test for the newContentMetadataUpdateStruct() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentMetadataUpdateStruct()
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentMetaDataUpdateStruct
     */
    public function testNewContentMetadataUpdateStruct()
    {
        //$this->markTestIncomplete( "Test for ContentService::newContentMetadataUpdateStruct() is not implemented." );
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentMetadataUpdateStruct',
            $contentMetadataUpdateStruct
        );
        return $contentMetadataUpdateStruct;
    }

    /**
     * Test for the newContentMetadataUpdateStruct() method.
     *
     * @depends testNewContentMetadataUpdateStruct
     *
     * @param $contentMetadataUpdateStruct
     * @return void
     */
    public function testNewContentMetadataUpdateStructValues( $contentMetadataUpdateStruct )
    {
        $this->markTestIncomplete( "Test for ContentService::newContentMetadataUpdateStruct() is not implemented." );
    }

    /**
     * Test for the newContentUpdateStruct() method.
     *
     * @depends testNewContentMetadataUpdateStruct
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentUpdateStruct()
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    public function testNewContentUpdateStruct()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentUpdateStruct',
            $contentUpdateStruct
        );
        return $contentUpdateStruct;
    }

    /**
     * Test for the newContentUpdateStruct() method.
     *
     * @depends testNewContentUpdateStruct
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentUpdateStruct()
     *
     * @param $contentUpdateStruct
     * @return void
     */
    public function testNewContentUpdateStructValues( $contentUpdateStruct )
    {
        $this->markTestIncomplete( "Test for ContentService::newContentUpdateStruct() is not implemented." );
    }

    /**
     * Test for the newTranslationInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newTranslationInfo()
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TranslationInfo
     */
    public function testNewTranslationInfo()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $translationInfo = $contentService->newTranslationInfo();
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\TranslationInfo',
            $translationInfo
        );
        return $translationInfo;
    }

    /**
     * Test for the newTranslationInfo() method.
     *
     * @depends testNewTranslationInfo
     * @covers \eZ\Publish\Core\Repository\ContentService::newTranslationInfo()
     *
     * @param $translationInfo
     * @return void
     */
    public function testNewTranslationInfoValues( $translationInfo )
    {
        $this->markTestIncomplete( "Test for ContentService::newTranslationInfo() is not implemented." );
    }

    /**
     * Test for the newTranslationValues() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newTranslationValues()
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TranslationValues
     */
    public function testNewTranslationValues()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $translationValues = $contentService->newTranslationValues();
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\TranslationValues',
            $translationValues
        );
        return $translationValues;
    }

    /**
     * Test for the newTranslationValues() method.
     *
     * @depends testNewTranslationValues
     * @covers \eZ\Publish\Core\Repository\ContentService::newTranslationValues()
     *
     * @param $translationValues
     * @return void
     */
    public function testNewTranslationValuesValues( $translationValues )
    {
        $this->markTestIncomplete( "Test for ContentService::newTranslationValues() is not implemented." );
    }
}
