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
    eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;

/**
 * Test case for Content service
 */
abstract class ContentBase extends BaseServiceTest
{
    protected $testContentType;

    public function setUp()
    {
        parent::setUp();

        if ( empty( $this->testContentType) ) $this->testContentType = $this->createTestContentType();
    }

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
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfo()
     *
     * @param $contentInfo
     * @return void
     */
    public function testLoadContentInfoValues( $contentInfo )
    {
        // Legacy fixture content ID=4 values
        $expectedValues = array(
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

        $contentInfo = $contentService->loadContentInfoByRemoteId( "f5c88a2209584891056f987fd965b0ba" );
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
        // Legacy fixture content 4 values
        $expectedValues = array(
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
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo()
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @return void
     */
    public function testLoadVersionInfoValues( $versionInfo )
    {
        // Legacy fixture content 4 current version (1) values
        $expectedValues = array(
            "id"                  => 4,
            "versionNo"           => 1,
            "modificationDate"    => new \DateTime( "@0" ),
            "creatorId"           => 14,
            "creationDate"        => new \DateTime( "@0" ),
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
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo()
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
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo()
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @return void
     */
    public function testLoadVersionInfoWithSecondParameterValues( $versionInfo )
    {
        // Legacy fixture content 4 values
        $expectedValues = array(
            "id"                  => 4,
            "versionNo"           => 1,
            "modificationDate"    => new \DateTime( "@0" ),
            "creatorId"           => 14,
            "creationDate"        => new \DateTime( "@0" ),
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
     * @depends testNewContentCreateStruct
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
     *
     * @return array
     */
    public function testCreateContent()
    {
        $time = time();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $this->testContentType, 'eng-GB' );
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
        $loadedContentDraft = $contentService->loadContent( $contentDraft->contentInfo->contentId, null, 1 );
        /* END: Use Case */

        $this->assertInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content', $contentDraft );

        return array(
            "expected"     => $contentCreate,
            "actual"       => $contentDraft,
            "loadedActual" => $loadedContentDraft,
            "time"         => $time
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @depends testCreateContent
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
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
     * @todo make this dependant on test for ContentService::loadContent()
     *
     * @depends testCreateContent
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
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
     * @depends testCreateContent
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
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
                // @todo: should be null, InMemory skips creating node ssignments and creates locations right away
                //"mainLocationId"   => null,
                // implementation properties
                "contentTypeId"    => $contentCreate->contentType->id
            ),
            $contentDraft->contentInfo
        );
        $this->assertNotNull( $contentDraft->contentInfo->contentId );
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
                "contentId"           => $contentDraft->contentInfo->contentId,
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
            count( $this->testContentType->fieldDefinitions ) * count( $createdInLanguageCodes ),
            $createdFields,
            "Number of created fields does not match number of content type field definitions multiplied by number of languages the content is created in"
        );

        // Check field values
        $structFields = array();
        foreach ( $contentCreate->fields as $field )
            $structFields[$field->fieldDefIdentifier][$field->languageCode] = $field;
        foreach ( $this->testContentType->fieldDefinitions as $fieldDefinition )
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
        $this->markTestIncomplete( "Test for ContentTypeService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
     */
    public function testCreateContentThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateContentThrowsInvalidArgumentException()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $this->testContentType, 'eng-GB' );
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
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     *
     * @return array
     */
    public function testCreateContentThrowsContentValidationExceptionFieldDefinitionUnexistingField()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $this->testContentType, 'eng-GB' );
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
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     *
     * @return array
     */
    public function testCreateContentThrowsContentValidationExceptionUntranslatableField()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $this->testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->setField( "test_untranslatable", "Jabberwock" );
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
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     *
     * @return array
     */
    public function testCreateContentThrowsContentValidationExceptionUntranslatableFieldVariation()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $this->testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->setField( "test_untranslatable", "Bandersnatch", "eng-US" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because translation was given for a untranslatable field
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     *
     * @return array
     */
    public function testCreateContentThrowsContentValidationExceptionMultipleUntranslatableField()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $this->testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->setField( "test_untranslatable", "Jabberwock" );
        $contentCreate->setField( "test_untranslatable", "Bandersnatch" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because multiple fields are set in create struct for the same (untranslatable)
        // field definition and language
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @todo expectedExceptionCode
     *
     * @return array
     */
    public function testCreateContentThrowsContentValidationExceptionMultipleTranslatableField()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $this->testContentType, 'eng-GB' );
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
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @todo expectedExceptionCode
     *
     * @return array
     */
    public function testCreateContentThrowsContentFieldValidationRequiredFieldDefaultValueEmpty()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $this->testContentType, 'eng-GB' );
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
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
     */
    public function testCreateContentThrowsContentFieldValidationException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent()
     */
    public function testCreateContentThrowsContentValidationException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContent() is not implemented." );
    }

    /**
     * Test for the newContentMetadataUpdateStruct() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentMetadataUpdateStruct()
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
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata()
     * @depends testNewContentMetadataUpdateStruct
     *
     * @return array
     */
    public function testUpdateContentMetadata()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfo( 4 );

        $time = time();
        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
        $contentMetadataUpdateStruct->ownerId          = 10;
        //@todo name property is missing in API ContentMetaDataUpdateStruct
        //$contentMetadataUpdateStruct->name             = 0;
        $contentMetadataUpdateStruct->publishedDate    = new \DateTime( "@{$time}" );
        $contentMetadataUpdateStruct->modificationDate = new \DateTime( "@{$time}" );
        $contentMetadataUpdateStruct->mainLanguageCode = "eng-GB";
        $contentMetadataUpdateStruct->alwaysAvailable  = false;
        $contentMetadataUpdateStruct->remoteId         = "the-all-new-remoteid";
        //@todo mainLocationId property is missing in SPI ContentMetaDataUpdateStruct
        //$contentMetadataUpdateStruct->mainLocationId = 0;

        $content = $contentService->updateContentMetadata( $contentInfo, $contentMetadataUpdateStruct );
        /* END: Use Case */

        $this->assertInstanceOf( "\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $content );

        return array(
            "expected" => $contentMetadataUpdateStruct,
            "actual" => $content
        );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata()
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
                // @todo name property is missing in API ContentMetaDataUpdateStruct
                //"name"             => $updateStruct->name,
                "publishedDate"    => $updateStruct->publishedDate,
                "modificationDate" => $updateStruct->modificationDate,
                "mainLanguageCode" => $updateStruct->mainLanguageCode,
                "alwaysAvailable"  => $updateStruct->alwaysAvailable,
                "remoteId"         => $updateStruct->remoteId,
                //@todo mainLocationId property is missing in SPI ContentMetaDataUpdateStruct
                //"mainLocationId"   => $updateStruct->mainLocationId
            ),
            $content->contentInfo
        );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateContentMetadataThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::updateContentMetadata() is not implemented." );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata()
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
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata()
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
     * @depends testNewContentMetadataUpdateStruct
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentUpdateStruct()
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
     * Test for the newTranslationInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newTranslationInfo()
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
     * @covers \eZ\Publish\Core\Repository\ContentService::newTranslationValues()
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
