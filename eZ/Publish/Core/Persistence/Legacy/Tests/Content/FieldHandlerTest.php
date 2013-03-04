<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldHandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler;

/**
 * Test case for Content Handler
 */
class FieldHandlerTest extends LanguageAwareTestCase
{
    /**
     * Gateway mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGatewayMock;

    /**
     * Type gateway mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    protected $typeGatewayMock;

    /**
     * Mapper mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $mapperMock;

    /**
     * Storage handler mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected $storageHandlerMock;

    /**
     * Field type registry mock
     *
     * @var \eZ\Publish\Core\Persistence\FieldTypeRegistry
     */
    protected $fieldTypeRegistryMock;

    /**
     * Field type mock
     *
     * @var \eZ\Publish\SPI\FieldType\FieldType
     */
    protected $fieldTypeMock;

    /**
     * @param bool $storageHandlerUpdatesFields
     *
     * @return void
     */
    protected function assertCreateNewFields( $storageHandlerUpdatesFields = false )
    {
        $typeHandlerMock = $this->getTypeHandlerMock();
        $contentGatewayMock = $this->getContentGatewayMock();
        $fieldTypeMock = $this->getFieldTypeMock();
        $storageHandlerMock = $this->getStorageHandlerMock();

        $typeHandlerMock->expects( $this->once() )
            ->method( "load" )
            ->with( $this->equalTo( 1 ) )
            ->will( $this->returnValue( $this->getContentTypeFixture() ) );

        $fieldTypeMock->expects( $this->exactly( 3 ) )
            ->method( "getEmptyValue" )
            ->will( $this->returnValue( new FieldValue() ) );

        $contentGatewayMock->expects( $this->exactly( 6 ) )
            ->method( 'insertNewField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            )->will( $this->returnValue( 42 ) );

        $callNo = 0;
        $fieldValue = new FieldValue();
        foreach ( array( 1, 2, 3 ) as $fieldDefinitionId )
        {
            foreach ( array( "eng-US", "eng-GB" ) as $languageCode )
            {
                $field = new Field(
                    array(
                        "id" => 42,
                        "fieldDefinitionId" => $fieldDefinitionId,
                        "type" => "some-type",
                        "versionNo" => 1,
                        "value" => $fieldValue,
                        "languageCode" => $languageCode
                    )
                );
                // This field is copied from main language
                if ( $fieldDefinitionId == 2 && $languageCode == "eng-US" )
                {
                    $copyField = clone $field;
                    $originalField = clone $field;
                    $originalField->languageCode = "eng-GB";
                    continue;
                }
                $storageHandlerMock->expects( $this->at( $callNo++ ) )
                    ->method( 'storeFieldData' )
                    ->with(
                        $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                        $this->equalTo( $field )
                    )->will( $this->returnValue( $storageHandlerUpdatesFields ) );
            }
        }

        $storageHandlerMock->expects( $this->at( $callNo ) )
            ->method( 'copyFieldData' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->equalTo( $copyField ),
                $this->equalTo( $originalField )
            )->will( $this->returnValue( $storageHandlerUpdatesFields ) );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::createNewFields
     *
     * @return void
     */
    public function testCreateNewFields()
    {
        $fieldHandler = $this->getFieldHandler();
        $mapperMock = $this->getMapperMock();

        $this->assertCreateNewFields( false );

        $mapperMock->expects( $this->exactly( 6 ) )
            ->method( 'convertToStorageValue' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $fieldHandler->createNewFields( $this->getContentPartialFieldsFixture() );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::createNewFields
     *
     * @return void
     */
    public function testCreateNewFieldsUpdatingStorageHandler()
    {
        $fieldHandler = $this->getFieldHandler();
        $contentGatewayMock = $this->getContentGatewayMock();
        $mapperMock = $this->getMapperMock();

        $this->assertCreateNewFields( true );

        $mapperMock->expects( $this->exactly( 12 ) )
            ->method( 'convertToStorageValue' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $contentGatewayMock->expects( $this->exactly( 6 ) )
            ->method( "updateField" )
            ->with(
                $this->isInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\Field" ),
                $this->isInstanceOf( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue" )
            );

        $fieldHandler->createNewFields( $this->getContentPartialFieldsFixture() );
    }

    /**
     * @param bool $storageHandlerUpdatesFields
     *
     * @return void
     */
    protected function assertCreateExistingFieldsInNewVersion( $storageHandlerUpdatesFields = false )
    {
        $typeHandlerMock = $this->getTypeHandlerMock();
        $contentGatewayMock = $this->getContentGatewayMock();
        $storageHandlerMock = $this->getStorageHandlerMock();

        $typeHandlerMock->expects( $this->once() )
            ->method( "load" )
            ->with( $this->equalTo( 1 ) )
            ->will( $this->returnValue( $this->getContentTypeFixture() ) );

        $contentGatewayMock->expects( $this->exactly( 6 ) )
            ->method( 'insertExistingField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            )->will( $this->returnValue( 42 ) );

        $callNo = 0;
        $fieldValue = new FieldValue();
        foreach ( array( 1, 2, 3 ) as $fieldDefinitionId )
        {
            foreach ( array( "eng-US", "eng-GB" ) as $languageCode )
            {
                $field = new Field(
                    array(
                        "fieldDefinitionId" => $fieldDefinitionId,
                        "type" => "some-type",
                        "value" => $fieldValue,
                        "languageCode" => $languageCode
                    )
                );
                $originalField = clone $field;
                $field->versionNo = 1;
                // These fields are copied from main language
                if ( ( $fieldDefinitionId == 2 || $fieldDefinitionId == 3 ) && $languageCode == "eng-US" )
                {
                    $originalField->languageCode = "eng-GB";
                }
                $storageHandlerMock->expects( $this->at( $callNo++ ) )
                    ->method( 'copyFieldData' )
                    ->with(
                        $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                        $this->equalTo( $field ),
                        $this->equalTo( $originalField )
                    )->will( $this->returnValue( $storageHandlerUpdatesFields ) );
            }
        }
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::createExistingFieldsInNewVersion
     *
     * @return void
     */
    public function testCreateExistingFieldsInNewVersion()
    {
        $fieldHandler = $this->getFieldHandler();
        $mapperMock = $this->getMapperMock();

        $this->assertCreateExistingFieldsInNewVersion( false );

        $mapperMock->expects( $this->exactly( 6 ) )
            ->method( 'convertToStorageValue' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $fieldHandler->createExistingFieldsInNewVersion( $this->getContentFixture() );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::createExistingFieldsInNewVersion
     *
     * @return void
     */
    public function testCreateExistingFieldsInNewVersionUpdatingStorageHandler()
    {
        $fieldHandler = $this->getFieldHandler();
        $contentGatewayMock = $this->getContentGatewayMock();
        $mapperMock = $this->getMapperMock();

        $this->assertCreateExistingFieldsInNewVersion( true );

        $mapperMock->expects( $this->exactly( 12 ) )
            ->method( 'convertToStorageValue' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $contentGatewayMock->expects( $this->exactly( 6 ) )
            ->method( "updateField" )
            ->with(
                $this->isInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\Field" ),
                $this->isInstanceOf( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue" )
            );

        $fieldHandler->createExistingFieldsInNewVersion( $this->getContentFixture() );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::loadExternalFieldData
     *
     * @return void
     */
    public function testLoadExternalFieldData()
    {
        $fieldHandler = $this->getFieldHandler();

        $storageHandlerMock = $this->getStorageHandlerMock();

        $storageHandlerMock->expects( $this->exactly( 6 ) )
            ->method( 'getFieldData' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' )
            );

        $fieldHandler->loadExternalFieldData( $this->getContentFixture() );
    }

    /**
     * @param bool $storageHandlerUpdatesFields
     *
     * @return void
     */
    public function assertUpdateFieldsWithNewLanguage( $storageHandlerUpdatesFields = false )
    {
        $typeHandlerMock = $this->getTypeHandlerMock();
        $contentGatewayMock = $this->getContentGatewayMock();
        $fieldTypeMock = $this->getFieldTypeMock();
        $storageHandlerMock = $this->getStorageHandlerMock();

        $typeHandlerMock->expects( $this->once() )
            ->method( "load" )
            ->with( $this->equalTo( 1 ) )
            ->will( $this->returnValue( $this->getContentTypeFixture( true ) ) );

        $fieldTypeMock->expects( $this->exactly( 1 ) )
            ->method( "getEmptyValue" )
            ->will( $this->returnValue( new FieldValue() ) );

        $contentGatewayMock->expects( $this->exactly( 3 ) )
            ->method( 'insertNewField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            );

        $callNo = 0;
        $fieldValue = new FieldValue();
        foreach ( array( 1, 2, 3 ) as $fieldDefinitionId )
        {
            $field = new Field(
                array(
                    "fieldDefinitionId" => $fieldDefinitionId,
                    "type" => "some-type",
                    "versionNo" => 1,
                    "value" => $fieldValue,
                    "languageCode" => "ger-DE"
                )
            );
            // This field is copied from main language
            if ( $fieldDefinitionId == 3 )
            {
                $copyField = clone $field;
                $originalField = clone $field;
                $originalField->languageCode = "eng-GB";
                continue;
            }
            $storageHandlerMock->expects( $this->at( $callNo++ ) )
                ->method( 'storeFieldData' )
                ->with(
                    $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                    $this->equalTo( $field )
                )->will( $this->returnValue( $storageHandlerUpdatesFields ) );
        }

        $storageHandlerMock->expects( $this->at( $callNo ) )
            ->method( 'copyFieldData' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->equalTo( $copyField ),
                $this->equalTo( $originalField )
            )->will( $this->returnValue( $storageHandlerUpdatesFields ) );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     *
     * @return void
     */
    public function testUpdateFieldsWithNewLanguage()
    {
        $mapperMock = $this->getMapperMock();
        $fieldHandler = $this->getFieldHandler();

        $this->assertUpdateFieldsWithNewLanguage( false );

        $mapperMock->expects( $this->exactly( 3 ) )
            ->method( 'convertToStorageValue' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $field = new Field(
            array(
                "type" => "some-type",
                "value" => new FieldValue,
                "fieldDefinitionId" => 2,
                "languageCode" => "ger-DE",
            )
        );
        $fieldHandler->updateFields(
            $this->getContentFixture(),
            new UpdateStruct( array( "fields" => array( $field ) ) )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     *
     * @return void
     */
    public function testUpdateFieldsWithNewLanguageUpdatingStorageHandler()
    {
        $fieldHandler = $this->getFieldHandler();
        $mapperMock = $this->getMapperMock();
        $contentGatewayMock = $this->getContentGatewayMock();

        $this->assertUpdateFieldsWithNewLanguage( true );

        $mapperMock->expects( $this->exactly( 6 ) )
            ->method( 'convertToStorageValue' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $contentGatewayMock->expects( $this->exactly( 3 ) )
            ->method( "updateField" )
            ->with(
                $this->isInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\Field" ),
                $this->isInstanceOf( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue" )
            );

        $field = new Field(
            array(
                "type" => "some-type",
                "value" => new FieldValue,
                "fieldDefinitionId" => 2,
                "languageCode" => "ger-DE",
            )
        );
        $fieldHandler->updateFields(
            $this->getContentFixture(),
            new UpdateStruct( array( "fields" => array( $field ) ) )
        );
    }

    /**
     * @param boolean $storageHandlerUpdatesFields
     *
     * @return void
     */
    public function assertUpdateFieldsExistingLanguages( $storageHandlerUpdatesFields = false )
    {
        $storageHandlerMock = $this->getStorageHandlerMock();
        $typeHandlerMock = $this->getTypeHandlerMock();

        $typeHandlerMock->expects( $this->once() )
            ->method( "load" )
            ->with( $this->equalTo( 1 ) )
            ->will( $this->returnValue( $this->getContentTypeFixture() ) );

        $callNo = 0;
        $fieldValue = new FieldValue();
        $fieldsToCopy = array();
        foreach ( array( 1, 2, 3 ) as $fieldDefinitionId )
        {
            foreach ( array( "eng-US", "eng-GB" ) as $languageCode )
            {
                $field = new Field(
                    array(
                        "fieldDefinitionId" => $fieldDefinitionId,
                        "type" => "some-type",
                        "versionNo" => 1,
                        "value" => $fieldValue,
                        "languageCode" => $languageCode
                    )
                );
                // These fields are copied from main language
                if ( ( $fieldDefinitionId == 2 || $fieldDefinitionId == 3 ) && $languageCode != "eng-GB" )
                {
                    $originalField = clone $field;
                    $originalField->languageCode = "eng-GB";
                    $fieldsToCopy[] = array(
                        "copy" => clone $field,
                        "original" => $originalField
                    );
                    continue;
                }
                $storageHandlerMock->expects( $this->at( $callNo++ ) )
                    ->method( 'storeFieldData' )
                    ->with(
                        $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                        $this->equalTo( $field )
                    )->will( $this->returnValue( $storageHandlerUpdatesFields ) );
            }
        }

        foreach ( $fieldsToCopy as $fieldToCopy )
        {
            $storageHandlerMock->expects( $this->at( $callNo++ ) )
                ->method( 'copyFieldData' )
                ->with(
                    $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                    $this->equalTo( $fieldToCopy["copy"] ),
                    $this->equalTo( $fieldToCopy["original"] )
                )->will( $this->returnValue( $storageHandlerUpdatesFields ) );
        }
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     *
     * @return void
     */
    public function testUpdateFieldsExistingLanguages()
    {
        $fieldHandler = $this->getFieldHandler();
        $mapperMock = $this->getMapperMock();

        $this->assertUpdateFieldsExistingLanguages( false );

        $mapperMock->expects( $this->exactly( 6 ) )
            ->method( 'convertToStorageValue' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $fieldHandler->updateFields(
            $this->getContentFixture(),
            $this->getUpdateStructFixture()
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     *
     * @return void
     */
    public function testUpdateFieldsExistingLanguagesUpdatingStorageHandler()
    {
        $fieldHandler = $this->getFieldHandler();
        $mapperMock = $this->getMapperMock();
        $contentGatewayMock = $this->getContentGatewayMock();

        $this->assertUpdateFieldsExistingLanguages( true );

        $mapperMock->expects( $this->exactly( 12 ) )
            ->method( 'convertToStorageValue' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $contentGatewayMock->expects( $this->exactly( 6 ) )
            ->method( 'updateField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            );

        $fieldHandler->updateFields(
            $this->getContentFixture(),
            $this->getUpdateStructFixture()
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::deleteFields
     *
     * @return void
     */
    public function testDeleteFields()
    {
        $fieldHandler = $this->getFieldHandler();

        $contentGatewayMock = $this->getContentGatewayMock();
        $contentGatewayMock->expects( $this->once() )
            ->method( 'getFieldIdsByType' )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( 2 )
            )->will( $this->returnValue( array( 'some-type' => array( 2, 3 ) ) ) );

        $storageHandlerMock = $this->getStorageHandlerMock();
        $storageHandlerMock->expects( $this->once() )
            ->method( 'deleteFieldData' )
            ->with(
                $this->equalTo( 'some-type' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->equalTo( array( 2, 3 ) )
            );

        $contentGatewayMock->expects( $this->once() )
            ->method( 'deleteFields' )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( 2 )
            );

        $fieldHandler->deleteFields( 42, new VersionInfo( array( 'versionNo' => 2 ) ) );
    }

    /**
     * Returns a Content fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentPartialFieldsFixture()
    {
        $content = new Content;
        $content->versionInfo = new VersionInfo;
        $content->versionInfo->versionNo = 1;
        $content->versionInfo->languageIds = array( 2, 4 );
        $content->versionInfo->contentInfo = new ContentInfo;
        $content->versionInfo->contentInfo->id = 42;
        $content->versionInfo->contentInfo->contentTypeId = 1;
        $content->versionInfo->contentInfo->mainLanguageCode = "eng-GB";

        $field = new Field();
        $field->type = 'some-type';
        $field->value = new FieldValue;

        $firstFieldUs = clone $field;
        $firstFieldUs->fieldDefinitionId = 1;
        $firstFieldUs->languageCode = "eng-US";

        $secondFieldGb = clone $field;
        $secondFieldGb->fieldDefinitionId = 2;
        $secondFieldGb->languageCode = "eng-GB";

        $content->fields = array(
            $firstFieldUs,
            $secondFieldGb
        );

        return $content;
    }

    /**
     * Returns a Content fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentFixture()
    {
        $content = $this->getContentPartialFieldsFixture();

        $field = new Field();
        $field->type = 'some-type';
        $field->value = new FieldValue;

        $firstFieldGb = clone $field;
        $firstFieldGb->fieldDefinitionId = 1;
        $firstFieldGb->languageCode = "eng-GB";

        $secondFieldUs = clone $field;
        $secondFieldUs->fieldDefinitionId = 2;
        $secondFieldUs->languageCode = "eng-US";

        $thirdFieldGb = clone $field;
        $thirdFieldGb->fieldDefinitionId = 3;
        $thirdFieldGb->languageCode = "eng-GB";

        $thirdFieldUs = clone $field;
        $thirdFieldUs->fieldDefinitionId = 3;
        $thirdFieldUs->languageCode = "eng-US";

        $content->fields = array_merge(
            $content->fields,
            array(
                $firstFieldGb,
                $secondFieldUs,
                $thirdFieldGb,
                $thirdFieldUs
            )
        );

        return $content;
    }

    /**
     * Returns a ContentType fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    protected function getContentTypeFixture()
    {
        $contentType = new Type();
        $firstFieldDefinition = new FieldDefinition(
            array(
                "id" => 1,
                "fieldType" => "some-type",
                "isTranslatable" => true,
            )
        );
        $secondFieldDefinition = new FieldDefinition(
            array(
                "id" => 2,
                "fieldType" => "some-type",
                "isTranslatable" => false,
            )
        );
        $thirdFieldDefinition = new FieldDefinition(
            array(
                "id" => 3,
                "fieldType" => "some-type",
                "isTranslatable" => false,
            )
        );
        $contentType->fieldDefinitions = array(
            $firstFieldDefinition,
            $secondFieldDefinition,
            $thirdFieldDefinition,
        );

        return $contentType;
    }

    /**
     * Returns an UpdateStruct fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UpdateStruct
     */
    protected function getUpdateStructFixture()
    {
        $struct = new UpdateStruct();

        $content = $this->getContentFixture();

        foreach ( $content->fields as $field )
        {
            // Skip untranslatable fields not in main language
            if ( ( $field->fieldDefinitionId == 2 || $field->fieldDefinitionId == 3 ) && $field->languageCode != "eng-GB" )
            {
                continue;
            }
            $struct->fields[] = $field;
        }

        return $struct;
    }

    /**
     * Returns a FieldHandler to test
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected function getFieldHandler()
    {
        $mock = new FieldHandler(
            $this->getContentGatewayMock(),
            $this->getMapperMock(),
            $this->getStorageHandlerMock(),
            $this->getLanguageHandler(),
            $this->getFieldTypeRegistryMock()
        );
        $mock->typeHandler = $this->getTypeHandlerMock();

        return $mock;
    }

    /**
     * Returns a StorageHandler mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStorageHandlerMock()
    {
        if ( !isset( $this->storageHandlerMock ) )
        {
            $this->storageHandlerMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageHandler',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->storageHandlerMock;
    }

    /**
     * Returns a Mapper mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMapperMock()
    {
        if ( !isset( $this->mapperMock ) )
        {
            $this->mapperMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Mapper',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->mapperMock;
    }

    /**
     * Returns a Content Type gateway mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTypeHandlerMock()
    {
        if ( !isset( $this->typeGatewayMock ) )
        {
            $this->typeGatewayMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\Handler',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->typeGatewayMock;
    }

    /**
     * Returns a mock object for the Content Gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Gateway|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContentGatewayMock()
    {
        if ( !isset( $this->contentGatewayMock ) )
        {
            $this->contentGatewayMock = $this->getMockForAbstractClass(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Gateway'
            );
        }
        return $this->contentGatewayMock;
    }

    /**
     * @return \eZ\Publish\Core\Persistence\FieldTypeRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFieldTypeRegistryMock()
    {
        if ( !isset( $this->fieldTypeRegistryMock ) )
        {
            $this->fieldTypeRegistryMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\FieldTypeRegistry',
                array(),
                array(),
                '',
                false
            );

            $this->fieldTypeRegistryMock->expects(
                $this->any()
            )->method(
                "getFieldType"
            )->with(
                $this->isType( "string" )
            )->will(
                $this->returnValue( $this->getFieldTypeMock() )
            );
        }
        return $this->fieldTypeRegistryMock;
    }

    /**
     * @return \eZ\Publish\SPI\FieldType\FieldType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFieldTypeMock()
    {
        if ( !isset( $this->fieldTypeMock ) )
        {
            $this->fieldTypeMock = $this->getMock(
                'eZ\\Publish\\SPI\\FieldType\\FieldType'
            );
        }
        return $this->fieldTypeMock;
    }
}
