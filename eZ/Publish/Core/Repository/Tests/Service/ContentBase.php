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
    eZ\Publish\API\Repository\Exceptions,
    eZ\Publish\Core\Repository\Values\Content\VersionInfo,
    eZ\Publish\API\Repository\Values\Content\LocationCreateStruct,
    eZ\Publish\API\Repository\Values\Content\Content as APIContent,
    eZ\Publish\Core\Repository\Values\Content\Content,
    eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Test case for Content service
 */
abstract class ContentBase extends BaseServiceTest
{
    protected $testContentType;

    protected function getContentInfoExpectedValues()
    {
        // Legacy fixture content ID=4 values
        return array(
            "contentId"        => 4,
            "name"             => "Users",
            "sectionId"        => 2,
            "currentVersionNo" => 1,
            "published"        => true,
            "ownerId"          => 14,
            "modificationDate" => new \DateTime( "@1033917596" ),
            "publishedDate"    => new \DateTime( "@1033917596" ),
            "alwaysAvailable"  => true,
            "remoteId"         => "f5c88a2209584891056f987fd965b0ba",
            "mainLanguageCode" => "eng-US",
            "mainLocationId"   => 5
        );
    }

    protected function getVersionInfoExpectedValues()
    {
        // Legacy fixture content 4 current version (1) values
        return array(
            "id"                  => 4,
            "versionNo"           => 1,
            "modificationDate"    => new \DateTime( "@0" ),
            "creatorId"           => 14,
            "creationDate"        => new \DateTime( "@0" ),
            "status"              => VersionInfo::STATUS_PUBLISHED,
            "initialLanguageCode" => "eng-US",
            "languageCodes"       => array( "eng-US" )
        );
    }

    /**
     *
     *
     * @param array $languages
     *
     * @return mixed
     */
    protected function getFieldValuesExpectedValues( array $languages = null )
    {
        // Legacy fixture content ID=4 field values
        $fieldValues =  array(
            "eng-US" => array(
                "name" => array( "eng-US" => "Users" ),
                "description" => array( "eng-US" => "Main group" )
            )
        );

        $returnArray = array();
        foreach ( $fieldValues as $languageCode => $languageFieldValues )
        {
            if ( !empty( $languages ) && !in_array( $languageCode, $languages ) ) continue;
            $returnArray = array_merge_recursive( $returnArray, $languageFieldValues );
        }
        return $returnArray;
    }

    /**
     *
     *
     * @param array $languages
     *
     * @return mixed
     */
    protected function getFieldsExpectedValues( array $languages = null )
    {
        // Legacy fixture content ID=4 fields
        $fields =  array(
            "eng-US" => array(
                new Field(
                    array(
                        "id" => 7,
                        "fieldDefIdentifier" => "description",
                        "value" => "Main group",
                        "languageCode" => "eng-US"
                    )
                ),
                new Field(
                    array(
                        "id" => 8,
                        "fieldDefIdentifier" => "name",
                        "value" => "Users",
                        "languageCode" => "eng-US"
                    )
                )
            )
        );

        $returnArray = array();
        foreach ( $fields as $languageCode => $languageFields )
        {
            if ( !empty( $languages ) && !in_array( $languageCode, $languages ) ) continue;
            $returnArray = array_merge( $returnArray, $languageFields );
        }
        return $returnArray;
    }

    protected function getExpectedContentType()
    {

    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function testLoadContentInfo()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfo( 4 );
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
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfo
     *
     * @param $contentInfo
     * @return void
     */
    public function testLoadContentInfoValues( $contentInfo )
    {
        $this->assertPropertiesCorrect(
            $this->getContentInfoExpectedValues(),
            $contentInfo
        );
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfo
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return void
     */
    public function testLoadContentInfoThrowsNotFoundException()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because given contentId does not exist
        $contentInfo = $contentService->loadContentInfo( PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfo
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @return void
     */
    public function testLoadContentInfoThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentInfo() is not implemented." );
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfoByRemoteId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function testLoadContentInfoByRemoteId()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfoByRemoteId( "f5c88a2209584891056f987fd965b0ba" );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo',
            $contentInfo
        );

        return $contentInfo;
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @depends testLoadContentInfoByRemoteId
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfoByRemoteId
     *
     * @param $contentInfo
     * @return void
     */
    public function testLoadContentInfoByRemoteIdValues( $contentInfo )
    {
        $this->assertPropertiesCorrect(
            $this->getContentInfoExpectedValues(),
            $contentInfo
        );
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfoByRemoteId
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return void
     */
    public function testLoadContentInfoByRemoteIdThrowsNotFoundException()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because remoteId does not exist
        $contentInfo = $contentService->loadContentInfoByRemoteId( "this-remote-id-does-not-exist" );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfoByRemoteId
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @return void
     */
    public function testLoadContentInfoByRemoteIdThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentInfoByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @depends testLoadContentInfo
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function testLoadVersionInfo()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $contentInfo = $contentService->loadContentInfo( 4 );

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
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @return void
     */
    public function testLoadVersionInfoValues( $versionInfo )
    {
        $this->assertPropertiesCorrect(
            $this->getVersionInfoExpectedValues(),
            $versionInfo
        );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     * @todo test for different version
     */
    public function testLoadVersionInfoWithSecondParameter()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $contentInfo = $contentService->loadContentInfo( 4 );

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
     * @depends testLoadVersionInfoWithSecondParameter
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @return void
     */
    public function testLoadVersionInfoWithSecondParameterValues( $versionInfo )
    {
        $this->assertPropertiesCorrect(
            $this->getVersionInfoExpectedValues(),
            $versionInfo
        );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return void
     */
    public function testLoadVersionInfoThrowsNotFoundException()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $contentInfo = $contentService->loadContentInfo( 4 );

        // Throws an exception because version with given number does not exists
        $versionInfo = $contentService->loadVersionInfo( $contentInfo, PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @return void
     */
    public function testLoadVersionInfoThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadVersionInfo() is not implemented." );
    }

    /**
     * Data provider for testLoadContent()
     *
     * @return array
     */
    public function testLoadContentArgumentsProvider()
    {
        return array(
            array( 4, null, null ),
            array( 4, array( "eng-US" ), null ),
            array( 4, null, 1 ),
            array( 4, array( "eng-US" ), 1 )
        );
    }

    /**
     * Test for the loadContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContent
     * @dataProvider testLoadContentArgumentsProvider
     *
     * @param int $contentId
     * @param array $languages
     * @param int $versionNo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testLoadContent( $contentId, array $languages = null, $versionNo = null  )
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $content = $contentService->loadContent( $contentId, $languages, $versionNo );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $content
        );

        $this->assertLoadContentValues( $content, $languages );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param array $languages
     *
     * @return void
     */
    protected function assertLoadContentValues( APIContent $content, array $languages = null )
    {
        $this->assertPropertiesCorrect(
            $this->getVersionInfoExpectedValues(),
            $content->getVersionInfo()
        );

        $this->assertPropertiesCorrect(
            $this->getContentInfoExpectedValues(),
            $content->contentInfo
        );

        $this->assertEquals(
            $this->getFieldValuesExpectedValues( $languages ),
            $content->fields
        );

        $fields = $content->getFields();
        usort( $fields, function( $a, $b ) { return strcmp( $a->id, $b->id ); } );
        $this->assertEquals(
            $this->getFieldsExpectedValues( $languages ),
            $fields
        );

        // @todo assert relations

        $this->assertEquals( $content->contentId, $content->contentInfo->contentId );
    }

    /**
     * Test for the loadContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContent() is not implemented." );
    }

    /**
     * Test for the loadContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentThrowsNotFoundExceptionContentNotFound()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because content with id PHP_INT_MAX does not exist
        $content = $contentService->loadContent( PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentThrowsNotFoundExceptionVersionNotFound()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because version number PHP_INT_MAX for content with id 4 does not exist
        $content = $contentService->loadContent( 4, null, PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentThrowsNotFoundExceptionLanguageNotFound()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because content does not exists in "eng-GB" language
        $content = $contentService->loadContent( 4, array( "eng-GB" ) );
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentThrowsNotFoundExceptionLanguageNotFoundVariation()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because content does not exists in "eng-GB" language
        $content = $contentService->loadContent( 4, array( "eng-US", "eng-GB" ) );
        /* END: Use Case */
    }

    /**
     * Test for the newContentCreateStruct() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentCreateStruct
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
     * @depends testNewContentCreateStruct
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentCreateStruct
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
     * @depends testNewContentCreateStruct
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     *
     * @return array
     */
    public function testCreateContent()
    {
        $time = time();
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->setField( "test_translatable", "and thumbs opposable", "eng-US" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        $locationCreates = array(
            new LocationCreateStruct(
                array(
                    //priority = 0
                    //hidden = false
                    "remoteId" => "db787a9143f57828dd4331573466a013",
                    //sortField = Location::SORT_FIELD_NAME
                    //sortOrder = Location::SORT_ORDER_ASC
                    "parentLocationId" => 5
                )
            ),
            new LocationCreateStruct(
                array(
                    //priority = 0
                    //hidden = false
                    "remoteId" => "a3dd7c1c9e04c89e446a70f647286e6b",
                    //sortField = Location::SORT_FIELD_NAME
                    //sortOrder = Location::SORT_ORDER_ASC
                    "parentLocationId" => 5
                )
            ),
        );

        $contentDraft = $contentService->createContent( $contentCreate, $locationCreates );
        /* END: Use Case */

        $this->assertInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content', $contentDraft );

        return array(
            "expected"     => $contentCreate,
            "actual"       => $contentDraft,
            "loadedActual" => $contentService->loadContent( $contentDraft->contentId, null, 1 ),
            "time"         => $time
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @depends testCreateContent
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     *
     * @param array $data
     */
    public function testCreateContentStructValues( array $data )
    {
        $this->assertCreateContentStructValues( $data );
    }

    /**
     * Test for the createContent() method.
     *
     * Because of the way ContentHandler::create() is implemented and tested in legacy storage it is also necessary to
     * test loaded content object, not only the one returned by ContentService::createContent
     *
     * @depends testCreateContent
     * @depends testLoadContent
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     *
     * @param array $data
     */
    public function testCreateContentStructValuesLoaded( array $data )
    {
        $data["actual"] = $data['loadedActual'];

        $this->assertCreateContentStructValues( $data );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     *
     * @param array $data
     */
    protected function assertCreateContentStructValues( array $data )
    {
        $this->assertCreateContentStructValuesContentInfo( $data );
        $this->assertCreateContentStructValuesVersionInfo( $data );
        $this->assertCreateContentStructValuesRelations( $data );
        $this->assertCreateContentStructValuesFields( $data );
        $this->assertCreateContentStructValuesNodeAssignments( $data );
    }

    /**
     * Asserts that ContentInfo is valid after Content creation
     *
     * @param array $data
     */
    protected function assertCreateContentStructValuesContentInfo( array $data )
    {
        /** @var $contentDraft \eZ\Publish\API\Repository\Values\Content\Content */
        $contentDraft = $data['actual'];
        /** @var $contentCreate \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct */
        $contentCreate = $data['expected'];

        $this->assertPropertiesCorrect(
            array(
                //"contentId"
                // @todo
                //"name"             => ,
                "sectionId"        => $contentCreate->sectionId,
                "currentVersionNo" => 1,
                "published"        => false,
                "ownerId"          => $contentCreate->ownerId,
                "modificationDate" => new \DateTime( "@0" ),
                "publishedDate"    => new \DateTime( "@0" ),
                "alwaysAvailable"  => $contentCreate->alwaysAvailable,
                "remoteId"         => $contentCreate->remoteId,
                "mainLanguageCode" => $contentCreate->mainLanguageCode,
                // @todo: should be null, InMemory skips creating node assignments and creates locations right away
                //"mainLocationId"   => null,
                // implementation properties
                "contentTypeId"    => $contentCreate->contentType->id
            ),
            $contentDraft->contentInfo
        );
        $this->assertNotNull( $contentDraft->contentId );
    }

    /**
     * Asserts that VersionInfo is valid after Content creation
     *
     * @param array $data
     */
    protected function assertCreateContentStructValuesVersionInfo( array $data )
    {
        /** @var $contentDraft \eZ\Publish\API\Repository\Values\Content\Content */
        $contentDraft = $data['actual'];
        /** @var $contentCreate \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct */
        $contentCreate = $data['expected'];
        $time = $data['time'];

        $this->assertPropertiesCorrect(
            array(
                //"id"
                "versionNo"           => 1,
                //"creationDate"
                //"modificationDate"
                "creatorId"           => $contentCreate->ownerId,
                "status"              => VersionInfo::STATUS_DRAFT,
                "initialLanguageCode" => $contentCreate->mainLanguageCode,
                //"languageCodes"
                // implementation properties
                "contentId"           => $contentDraft->contentId,
                // @todo
                //"names"                =>
            ),
            $contentDraft->versionInfo
        );
        $languageCodes = $this->getLanguageCodesFromFields( $contentCreate->fields, $contentCreate->mainLanguageCode );
        $this->assertCount( count( $languageCodes ), $contentDraft->versionInfo->languageCodes );
        foreach ( $contentDraft->versionInfo->languageCodes as $languageCode )
            $this->assertTrue( in_array( $languageCode, $languageCodes ) );
        $this->assertNotNull( $contentDraft->versionInfo->id );
        $this->assertGreaterThanOrEqual( new \DateTime( "@{$time}" ), $contentDraft->versionInfo->creationDate );
        $this->assertGreaterThanOrEqual( new \DateTime( "@{$time}" ), $contentDraft->versionInfo->modificationDate );
    }

    /**
     *
     *
     * @param array $data
     */
    protected function assertCreateContentStructValuesRelations( array $data )
    {
        // @todo: relations not implemented yet
    }

    /**
     * Asserts that fields are valid after Content creation
     *
     * @param array $data
     */
    protected function assertCreateContentStructValuesFields( array $data )
    {
        /** @var $contentDraft \eZ\Publish\API\Repository\Values\Content\Content */
        $contentDraft = $data['actual'];
        /** @var $contentCreate \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct */
        $contentCreate = $data['expected'];

        $createdFields = $contentDraft->getFields();
        $createdInLanguageCodes = $this->getLanguageCodesFromFields(
            $contentCreate->fields,
            $contentCreate->mainLanguageCode
        );

        $this->assertCount(
            count( $contentDraft->contentType->fieldDefinitions ) * count( $createdInLanguageCodes ),
            $createdFields,
            "Number of created fields does not match number of content type field definitions multiplied by number of languages the content is created in"
        );

        // Check field values
        $structFields = array();
        foreach ( $contentCreate->fields as $field )
            $structFields[$field->fieldDefIdentifier][$field->languageCode] = $field;
        foreach ( $contentDraft->contentType->fieldDefinitions as $fieldDefinition )
        {
            $this->assertArrayHasKey(
                $fieldDefinition->identifier,
                $contentDraft->fields,
                "Field values are missing for field definition '{$fieldDefinition->identifier}'"
            );

            foreach ( $createdInLanguageCodes as $languageCode )
            {
                $this->assertArrayHasKey(
                    $languageCode,
                    $contentDraft->fields[$fieldDefinition->identifier],
                    "Field value is missing for field definition '{$fieldDefinition->identifier}' in language '{$languageCode}'"
                );

                // If field is not set in create struct, it should have default value
                $valueLanguageCode = $fieldDefinition->isTranslatable ? $languageCode : $contentCreate->mainLanguageCode;
                if ( isset( $structFields[$fieldDefinition->identifier][$valueLanguageCode] ) )
                {
                    $this->assertEquals(
                        $structFields[$fieldDefinition->identifier][$valueLanguageCode]->value,
                        $contentDraft->fields[$fieldDefinition->identifier][$languageCode],
                        "Field value for field definition '{$fieldDefinition->identifier}' in language '{$languageCode}' is not equal to given struct field value"
                    );
                }
                else
                {
                    $this->assertEquals(
                        $fieldDefinition->defaultValue,
                        $contentDraft->fields[$fieldDefinition->identifier][$languageCode],
                        "Field value for field definition '{$fieldDefinition->identifier}' in language '{$languageCode}' is not equal to default value"
                    );
                }
            }
        }
    }

    /**
     * Gathers language codes from an array of fields
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $fields
     * @param string $mainLanguageCode
     *
     * @return array an array of language code strings
     */
    protected function getLanguageCodesFromFields( array $fields, $mainLanguageCode )
    {
        $languageCodes = array( $mainLanguageCode );
        foreach ( $fields as $field ) $languageCodes[] = $field->languageCode;
        return array_unique( $languageCodes );
    }

    /**
     *
     *
     * @param array $data
     */
    protected function assertCreateContentStructValuesNodeAssignments( array $data )
    {
        //@todo implement
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateContentThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateContentThrowsInvalidArgumentException()
    {
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'f5c88a2209584891056f987fd965b0ba';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because remoteId "f5c88a2209584891056f987fd965b0ba" already exists
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     *
     * @return array
     */
    public function testCreateContentThrowsContentValidationExceptionFieldDefinitionUnexisting()
    {
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->setField( "humpty_dumpty", "no such field definition" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because field definition with identifier "humpty_dumpty" does not exist
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     *
     * @return array
     */
    public function testCreateContentThrowsContentValidationExceptionUntranslatableField()
    {
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->setField( "test_untranslatable", "Bandersnatch", "eng-US" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because translation was given for a untranslatable field
        // Note that it is still permissible to set untranslatable field with main language
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     *
     * @return array
     */
    public function testCreateContentThrowsContentValidationExceptionMultipleUntranslatableField()
    {
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->setField( "test_untranslatable", "Jabberwock" );
        $contentCreate->setField( "test_untranslatable", "Bandersnatch" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because multiple fields are set in create struct for the same (untranslatable)
        // field definition
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     *
     * @return array
     */
    public function testCreateContentThrowsContentValidationExceptionMultipleTranslatableField()
    {
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->setField( "test_translatable", "Jabberwock" );
        $contentCreate->setField( "test_translatable", "Bandersnatch" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because multiple fields are set in create struct for the same (translatable)
        // field definition and language
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @todo expectedExceptionCode
     *
     * @return array
     */
    public function testCreateContentThrowsContentFieldValidationRequiredFieldDefaultValueEmpty()
    {
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_translatable", "Jabberwock" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because required field is not set and its default value is empty
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     */
    public function testCreateContentThrowsContentFieldValidationException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     */
    public function testCreateContentThrowsContentValidationException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContent() is not implemented." );
    }

    /**
     * Test for the newContentMetadataUpdateStruct() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentMetadataUpdateStruct
     */
    public function testNewContentMetadataUpdateStruct()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentMetadataUpdateStruct',
            $contentMetadataUpdateStruct
        );

        foreach ( $contentMetadataUpdateStruct as $propertyName => $propertyValue )
            $this->assertNull( $propertyValue, "Property '{$propertyName}' initial value should be null'" );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata
     * @depends testNewContentMetadataUpdateStruct
     *
     * @return array
     */
    public function testUpdateContentMetadata()
    {
        // Create one additional location for content to be set as main location
        $locationService = $this->repository->getLocationService();
        $contentInfo = $this->repository->getContentService()->loadContentInfo( 4 );
        $locationCreateStruct = $locationService->newLocationCreateStruct( 13 );
        $locationCreateStruct->remoteId = "test-location-remote-id-1234";
        $newLocation = $locationService->createLocation(
            $contentInfo,
            $locationCreateStruct
        );
        $newSectionId = $this->repository->getContentService()->loadContentInfo(
            $locationService->loadLocation( $newLocation->parentLocationId )->contentId
        )->sectionId;
        // Change content section to be different from new main location parent location content
        $sectionService = $this->repository->getSectionService();
        $sectionService->assignSection(
            $contentInfo,
            $sectionService->loadSection( $newSectionId === 1 ? $newSectionId + 1 : $newSectionId - 1 )
        );

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfo( 4 );

        $newMainLocationId = $newLocation->id;
        $time = time();
        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
        $contentMetadataUpdateStruct->ownerId          = 10;
        $contentMetadataUpdateStruct->publishedDate    = new \DateTime( "@{$time}" );
        $contentMetadataUpdateStruct->modificationDate = new \DateTime( "@{$time}" );
        $contentMetadataUpdateStruct->mainLanguageCode = "eng-GB";
        $contentMetadataUpdateStruct->alwaysAvailable  = false;
        $contentMetadataUpdateStruct->remoteId         = "the-all-new-remoteid";
        $contentMetadataUpdateStruct->mainLocationId   = $newMainLocationId;

        $content = $contentService->updateContentMetadata( $contentInfo, $contentMetadataUpdateStruct );
        /* END: Use Case */

        $this->assertInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $content );

        return array(
            "expected"     => $contentMetadataUpdateStruct,
            "actual"       => $content,
            "newSectionId" => $newSectionId
        );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata
     * @depends testUpdateContentMetadata
     *
     * @param array $data
     */
    public function testUpdateContentMetadataStructValues( array $data )
    {
        /** @var $updateStruct \eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct */
        $updateStruct = $data['expected'];
        /** @var $content \eZ\Publish\API\Repository\Values\Content\Content */
        $content = $data['actual'];

        $this->assertPropertiesCorrect(
            array(
                "ownerId"          => $updateStruct->ownerId,
                // @todo test name change after name scheme resolver is implemented
                //"name"             => $updateStruct->name,
                "publishedDate"    => $updateStruct->publishedDate,
                "modificationDate" => $updateStruct->modificationDate,
                "mainLanguageCode" => $updateStruct->mainLanguageCode,
                "alwaysAvailable"  => $updateStruct->alwaysAvailable,
                "remoteId"         => $updateStruct->remoteId,
                "mainLocationId"   => $updateStruct->mainLocationId,
                "sectionId"        => $data["newSectionId"]
            ),
            $content->contentInfo
        );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateContentMetadataThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::updateContentMetadata() is not implemented." );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends testNewContentMetadataUpdateStruct
     */
    public function testUpdateContentMetadataThrowsInvalidArgumentExceptionDuplicateRemoteId()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $contentInfo = $contentService->loadContentInfo( 4 );
        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
        $contentMetadataUpdateStruct->remoteId = "9b47a45624b023b1a76c73b74d704acf";

        // Throws an exception because remoteId "9b47a45624b023b1a76c73b74d704acf" is already in use
        $contentService->updateContentMetadata( $contentInfo, $contentMetadataUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends testNewContentMetadataUpdateStruct
     */
    public function testUpdateContentMetadataThrowsInvalidArgumentExceptionNoMetadataPropertiesSet()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $contentInfo = $contentService->loadContentInfo( 4 );
        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();

        // Throws an exception because no properties are set in $contentMetadataUpdateStruct
        $contentService->updateContentMetadata( $contentInfo, $contentMetadataUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the newContentUpdateStruct() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentUpdateStruct
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

        $this->assertPropertiesCorrect(
            array(
                "initialLanguageCode" => null,
                "fields" => array()
            ),
            $contentUpdateStruct
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * @depends testCreateContent
     * @depends testNewContentUpdateStruct
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     *
     * @return array
     */
    public function testUpdateContent()
    {
        $content = $this->createTestContent();
        $time = time();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->contentId,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "eng-US";

        $contentUpdateStruct->setField( "test_required_empty", "new value for test_required_empty", "eng-US" );
        $contentUpdateStruct->setField( "test_translatable", "new eng-US value for test_translatable" );
        $contentUpdateStruct->setField( "test_untranslatable", "new value for test_untranslatable" );

        $contentUpdateStruct->setField( "test_translatable", "new eng-GB value for test_translatable", "eng-GB" );

        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */

        $this->assertInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $updatedContent );

        return array(
            "actual"   => $updatedContent,
            "expected" => $contentUpdateStruct,
            "previous" => $content,
            "time"     => $time
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * @depends testUpdateContent
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     *
     * @param array $data
     */
    public function testUpdateContentStructValues( array $data )
    {
        /** @var $updatedContentDraft \eZ\Publish\API\Repository\Values\Content\Content */
        $updatedContentDraft = $data['actual'];
        /** @var $contentUpdate \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct */
        $contentUpdate = $data['expected'];
        /** @var $contentDraft \eZ\Publish\API\Repository\Values\Content\Content */
        $contentDraft = $data['previous'];

        $this->assertEquals(
            $contentDraft->getVersionInfo()->languageCodes,
            $updatedContentDraft->getVersionInfo()->languageCodes,
            "Language codes in updated content draft does not match with previous language codes"
        );

        // Check field values
        $structFields = array();
        foreach ( $contentUpdate->fields as $field )
            $structFields[$field->fieldDefIdentifier][$field->languageCode] = $field;
        foreach ( $updatedContentDraft->contentType->fieldDefinitions as $fieldDefinition )
        {
            $this->assertArrayHasKey(
                $fieldDefinition->identifier,
                $updatedContentDraft->fields,
                "Field values are missing for field definition '{$fieldDefinition->identifier}'"
            );

            foreach ( $updatedContentDraft->getVersionInfo()->languageCodes as $languageCode )
            {
                $this->assertArrayHasKey(
                    $languageCode,
                    $updatedContentDraft->fields[$fieldDefinition->identifier],
                    "Field value is missing for field definition '{$fieldDefinition->identifier}' in language '{$languageCode}'"
                );

                // If field is not set in update struct, it should retain its previous value
                $valueLanguageCode = $fieldDefinition->isTranslatable ? $languageCode : $contentUpdate->initialLanguageCode;
                if ( isset( $structFields[$fieldDefinition->identifier][$valueLanguageCode] ) )
                {
                    $this->assertEquals(
                        $structFields[$fieldDefinition->identifier][$valueLanguageCode]->value,
                        $updatedContentDraft->fields[$fieldDefinition->identifier][$languageCode],
                        "Field value for field definition '{$fieldDefinition->identifier}' in language '{$languageCode}' is not equal to update struct field value"
                    );
                }
                else
                {
                    $this->assertEquals(
                        $contentDraft->fields[$fieldDefinition->identifier][$languageCode],
                        $updatedContentDraft->fields[$fieldDefinition->identifier][$languageCode],
                        "Non-updated field value for field definition '{$fieldDefinition->identifier}' in language '{$languageCode}' did not retain its previous value"
                    );
                }
            }
        }

        $this->assertEquals(
            $contentUpdate->initialLanguageCode,
            $updatedContentDraft->versionInfo->initialLanguageCode
        );
        $this->assertGreaterThanOrEqual(
            $data["time"],
            $updatedContentDraft->versionInfo->modificationDate->getTimestamp()
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateContentThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::testUpdateContent() is not implemented." );
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testUpdateContentThrowsBadStateException()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById( 14 );
        $contentUpdateStruct = $contentService->newContentUpdateStruct();

        // Throws an exception because version is not a draft
        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateContentThrowsContentFieldValidationException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::testUpdateContent() is not implemented." );
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     */
    public function testUpdateContentThrowsContentValidationExceptionRequiredFieldEmpty()
    {
        $content = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->contentId,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "eng-GB";
        $contentUpdateStruct->setField( "test_required_empty", "" );

        // Throws an exception because required field is being updated with empty value
        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     */
    public function testUpdateContentThrowsContentValidationExceptionFieldInUnexistingLanguage()
    {
        $content = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->contentId,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "eng-GB";
        $contentUpdateStruct->setField( "test_translatable", "Pommes frites", "fre-FR" );

        // Throws an exception because update struct is set with field in a language that is not
        // already present in content draft
        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     */
    public function testUpdateContentThrowsContentValidationExceptionFieldDefinitionUnexisting()
    {
        $content = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->contentId,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "eng-GB";
        $contentUpdateStruct->setField( "unexisting_field_definition_identifier", "eng-GB" );

        // Throws an exception because field definition with identifier "unexisting_field_definition_identifier"
        // does not exist in content draft content type
        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     */
    public function testUpdateContentThrowsContentValidationExceptionMultipleTranslatableField()
    {
        $content = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->contentId,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "eng-GB";
        $contentUpdateStruct->setField( "test_translatable", "Jabberwock" );
        $contentUpdateStruct->setField( "test_translatable", "Bandersnatch" );

        // Throws an exception because multiple fields are set in update struct for the same (translatable)
        // field definition and language
        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     */
    public function testUpdateContentThrowsContentValidationExceptionMultipleUntranslatableField()
    {
        $content = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->contentId,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "eng-GB";
        $contentUpdateStruct->setField( "test_untranslatable", "Jabberwock" );
        $contentUpdateStruct->setField( "test_untranslatable", "Bandersnatch" );

        // Throws an exception because multiple fields are set in update struct for the same (untranslatable)
        // field definition
        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     *
     * @return array
     */
    public function testUpdateContentThrowsContentValidationExceptionUntranslatableField()
    {
        $content = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->contentId,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "eng-GB";
        $contentUpdateStruct->setField( "test_untranslatable", "Jabberwock", "eng-US" );

        // Throws an exception because translation was given for a untranslatable field
        // Note that it is still permissible to set untranslatable field with main language
        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the publishVersion() method.
     *
     * @depends testCreateContent
     * @covers \eZ\Publish\Core\Repository\ContentService::publishVersion
     */
    public function testPublishVersion()
    {
        $time = time();
        $draftContent = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $draftContent->contentId,
            $draftContent->getVersionInfo()->versionNo
        );

        $publishedContent = $contentService->publishVersion( $versionInfo );
        /* END: Use Case */

        $this->assertInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $publishedContent );
        $this->assertTrue( $publishedContent->contentInfo->published );
        $this->assertEquals( VersionInfo::STATUS_PUBLISHED, $publishedContent->versionInfo->status );
        $this->assertGreaterThanOrEqual( $time, $publishedContent->contentInfo->publishedDate->getTimestamp() );
        $this->assertGreaterThanOrEqual( $time, $publishedContent->contentInfo->modificationDate->getTimestamp() );
    }

    /**
     * Test for the publishVersion() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::publishVersion
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testPublishVersionThrowsBadStateException()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById( 4 );

        // Throws an exception because version is already published
        $publishedContent = $contentService->publishVersion( $versionInfo );
        /* END: Use Case */
    }

    /**
     * Test for the publishVersion() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::publishVersion
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testPublishVersionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::publishVersion() is not implemented." );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContentDraft
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testCreateContentDraft()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfo( 4 );

        $internalDraftContent = $contentService->createContentDraft( $contentInfo );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $internalDraftContent
        );

        return $internalDraftContent;
    }

    public function testCreateContentDraftValues()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContentDraft() is not implemented." );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContentDraft
     */
    public function testCreateContentDraftWithSecondArgument()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $content = $contentService->loadContent( 4 );

        $internalDraftContent = $contentService->createContentDraft(
            $content->contentInfo,
            $content->getVersionInfo()
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $internalDraftContent
        );
    }

    public function testCreateContentDraftWithSecondArgumentValues()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContentDraft() is not implemented." );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContentDraft
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testCreateContentDraftWithThirdArgument()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContentDraft() is not implemented." );
    }

    public function testCreateContentDraftWithThirdArgumentValues()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContentDraft() is not implemented." );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @depends testCreateContentDraft
     * @covers \eZ\Publish\Core\Repository\ContentService::createContentDraft
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $internalDraftContent
     */
    public function testCreateContentDraftThrowsBadStateException( APIContent $internalDraftContent )
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because version status is not
        // VersionInfo::STATUS_PUBLISHED nor VersionInfo::STATUS_ARCHIVED
        $internalDraftContent = $contentService->createContentDraft(
            $internalDraftContent->contentInfo,
            $internalDraftContent->getVersionInfo()
        );
        /* END: Use Case */
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContentDraft
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateContentDraftThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContentDraft() is not implemented." );
    }





























    /**
     * Test for the newTranslationInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newTranslationInfo
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

        foreach ( $translationInfo as $propertyName => $propertyValue )
            $this->assertNull( $propertyValue, "Property '{$propertyName}' initial value should be null'" );
    }

    /**
     * Test for the newTranslationValues() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newTranslationValues
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

        foreach ( $translationValues as $propertyName => $propertyValue )
            $this->assertNull( $propertyValue, "Property '{$propertyName}' initial value should be null'" );
    }

    /**
     * Creates and returns content draft used in testing
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createTestContent()
    {
        $contentService = $this->repository->getContentService();
        $testContentType = $this->createTestContentType();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "val 11" );
        $contentCreate->setField( "test_required_not_empty", "val 12" );
        $contentCreate->setField( "test_translatable", "val 13" );
        $contentCreate->setField( "test_untranslatable", "val 14" );
        $contentCreate->setField( "test_translatable", "val 23", "eng-US" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        $locationCreates = array(
            new LocationCreateStruct(
                array(
                    //priority = 0
                    //hidden = false
                    "remoteId" => "db787a9143f57828dd4331573466a013",
                    //sortField = Location::SORT_FIELD_NAME
                    //sortOrder = Location::SORT_ORDER_ASC
                    "parentLocationId" => 5
                )
            ),
            new LocationCreateStruct(
                array(
                    //priority = 0
                    //hidden = false
                    "remoteId" => "a3dd7c1c9e04c89e446a70f647286e6b",
                    //sortField = Location::SORT_FIELD_NAME
                    //sortOrder = Location::SORT_ORDER_ASC
                    "parentLocationId" => 5
                )
            ),
        );

        return $contentService->createContent( $contentCreate, $locationCreates );
    }

    /**
     * Returns ContentType used in testing
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function createTestContentType()
    {
        $contentTypeService = $this->repository->getContentTypeService();
        $typeCreateStruct = $contentTypeService->newContentTypeCreateStruct(
            "test-type"
        );
        $typeCreateStruct->names = array( "eng-US" => "Test type name" );
        $typeCreateStruct->descriptions     = array( "eng-GB" => "Test type description" );
        $typeCreateStruct->remoteId         = "test-type-remoteid";
        $typeCreateStruct->creatorId        = 23;
        $typeCreateStruct->creationDate     = new \DateTime();
        $typeCreateStruct->mainLanguageCode = "eng-GB";
        $typeCreateStruct->nameSchema       = "<name>";
        $typeCreateStruct->urlAliasSchema   = "<name>";

        $fieldCreate = $contentTypeService->newFieldDefinitionCreateStruct( "test_required_empty", "ezstring" );
        $fieldCreate->names           = array( "eng-GB" => "Test required empty" );
        $fieldCreate->descriptions    = array( "eng-GB" => "Required field with empty default value" );
        $fieldCreate->fieldGroup      = "test-field-group";
        $fieldCreate->position        = 0;
        $fieldCreate->isTranslatable  = false;
        $fieldCreate->isRequired      = true;
        $fieldCreate->isInfoCollector = false;
        $fieldCreate->isSearchable    = true;
        $fieldCreate->defaultValue    = "";
        //$fieldCreate->validators
        //$fieldCreate->fieldSettings
        $typeCreateStruct->addFieldDefinition( $fieldCreate );

        $fieldCreate = $contentTypeService->newFieldDefinitionCreateStruct( "test_required_not_empty", "ezstring" );
        $fieldCreate->names           = array( "eng-GB" => "Test required not empty" );
        $fieldCreate->descriptions    = array( "eng-GB" => "Required field with default value not empty" );
        $fieldCreate->fieldGroup      = "test-field-group";
        $fieldCreate->position        = 1;
        $fieldCreate->isTranslatable  = false;
        $fieldCreate->isRequired      = true;
        $fieldCreate->isInfoCollector = false;
        $fieldCreate->isSearchable    = true;
        $fieldCreate->defaultValue    = "dummy default data";
        //$fieldCreate->validators
        //$fieldCreate->fieldSettings
        $typeCreateStruct->addFieldDefinition( $fieldCreate );

        $fieldCreate = $contentTypeService->newFieldDefinitionCreateStruct( "test_translatable", "ezstring" );
        $fieldCreate->names           = array( "eng-GB" => "Test translatable" );
        $fieldCreate->descriptions    = array( "eng-GB" => "Translatable field" );
        $fieldCreate->fieldGroup      = "test-field-group";
        $fieldCreate->position        = 2;
        $fieldCreate->isTranslatable  = true;
        $fieldCreate->isRequired      = false;
        $fieldCreate->isInfoCollector = false;
        $fieldCreate->isSearchable    = true;
        $fieldCreate->defaultValue    = "";
        //$fieldCreate->validators
        //$fieldCreate->fieldSettings
        $typeCreateStruct->addFieldDefinition( $fieldCreate );

        $fieldCreate = $contentTypeService->newFieldDefinitionCreateStruct( "test_untranslatable", "ezstring" );
        $fieldCreate->names           = array( "eng-GB" => "Test not translatable" );
        $fieldCreate->descriptions    = array( "eng-GB" => "Untranslatable field" );
        $fieldCreate->fieldGroup      = "test-field-group";
        $fieldCreate->position        = 3;
        $fieldCreate->isTranslatable  = false;
        $fieldCreate->isRequired      = false;
        $fieldCreate->isInfoCollector = false;
        $fieldCreate->isSearchable    = true;
        $fieldCreate->defaultValue    = "";
        //$fieldCreate->validators
        //$fieldCreate->fieldSettings
        $typeCreateStruct->addFieldDefinition( $fieldCreate );

        $contentTypeDraft = $contentTypeService->createContentType(
            $typeCreateStruct,
            array( $contentTypeService->loadContentTypeGroup( 1 ) )
        );
        $contentTypeId = $contentTypeDraft->id;

        $contentTypeService->publishContentTypeDraft( $contentTypeDraft );

        return $contentTypeService->loadContentType( $contentTypeId );
    }
}
