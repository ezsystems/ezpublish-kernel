<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
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
class FieldHandlerTest extends TestCase
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
     * Storage handler mock
     *
     * @var \eZ\Publish\SPI\FieldType\FieldType
     */
    protected $fieldTypeMock;

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::createNewFields
     *
     * @return void
     */
    public function testCreateNewFields()
    {
        $fieldHandler = $this->getFieldHandler();

        $mapperMock = $this->getMapperMock();
        $typeHandlerMock = $this->getTypeHandlerMock();
        $contentGatewayMock = $this->getContentGatewayMock();
        $storageHandlerMock = $this->getStorageHandlerMock();
        $fieldTypeMock = $this->getFieldTypeMock();

        $typeHandlerMock->expects( $this->once() )
            ->method( "load" )
            ->with( $this->equalTo( 1 ) )
            ->will( $this->returnValue( $this->getContentTypeFixture() ) );

        $fieldTypeMock->expects( $this->exactly( 3 ) )
            ->method( "getEmptyValue" )
            ->will( $this->returnValue( 42 ) );

        $fieldTypeMock->expects( $this->exactly( 3 ) )
            ->method( "toPersistenceValue" )
            ->with( $this->equalTo( 42 ) )
            ->will( $this->returnValue( new FieldValue() ) );

        $callNo = 0;
        $fieldValue = new FieldValue();
        foreach ( array( 1, 2, 3 ) as $fieldDefinitionId )
        {
            foreach ( array( "eng-GB", "eng-US" ) as $languageCode )
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
                $mapperMock->expects( $this->at( $callNo++ ) )
                    ->method( 'convertToStorageValue' )
                    // TODO: commented out because of PHPUnit bug
                    //->with( $this->equalTo( $field ) )
                    ->will( $this->returnValue( new StorageFieldValue() ) );
            }
        }

        $contentGatewayMock->expects( $this->exactly( 6 ) )
            ->method( 'insertNewField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            )->will( $this->returnValue( 42 ) );

        $storageHandlerMock->expects( $this->exactly( 6 ) )
            ->method( 'storeFieldData' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' )
            );

        $fieldHandler->createNewFields( $this->getContentPartialFieldsFixture() );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::createNewFields
     *
     * @return void
     */
    public function testCreateNewFieldsUpdatingStorageHandler()
    {
        self::markTestIncomplete( "@todo Test createNewField() with updating storage handler and 1 untranslatable field" );
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
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     *
     * @return void
     */
    public function testUpdateFieldsWithNewLanguage()
    {
        $fieldHandler = $this->getFieldHandler();
        $mapperMock = $this->getMapperMock();
        $typeHandlerMock = $this->getTypeHandlerMock();
        $contentGatewayMock = $this->getContentGatewayMock();
        $storageHandlerMock = $this->getStorageHandlerMock();
        $fieldTypeMock = $this->getFieldTypeMock();

        $typeHandlerMock->expects( $this->once() )
            ->method( "load" )
            ->with( $this->equalTo( 1 ) )
            ->will( $this->returnValue( $this->getContentTypeFixture( true ) ) );

        $fieldTypeMock->expects( $this->exactly( 1 ) )
            ->method( "getEmptyValue" )
            ->will( $this->returnValue( 42 ) );

        $fieldTypeMock->expects( $this->exactly( 1 ) )
            ->method( "toPersistenceValue" )
            ->with( $this->equalTo( 42 ) )
            ->will( $this->returnValue( new FieldValue() ) );

        $mapperMock->expects( $this->exactly( 3 ) )
            ->method( 'convertToStorageValue' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $contentGatewayMock->expects( $this->exactly( 3 ) )
            ->method( 'insertNewField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            );

        $storageHandlerMock->expects( $this->exactly( 3 ) )
            ->method( 'storeFieldData' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' )
            );

        $fieldHandler->updateFields(
            $this->getContentFixture(),
            new UpdateStruct(
                array(
                    "fields" => array(
                        new Field(
                            array(
                                "type" => "some-type",
                                "value" => new FieldValue,
                                "fieldDefinitionId" => 2,
                                "languageCode" => "ger-DE",
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     *
     * @return void
     */
    public function testUpdateFieldsUpdatingStorageHandlerNonTranslatable()
    {
        self::markTestIncomplete( "@todo Test updateFields() with updating storage handler and untranslatable fields" );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     *
     * @return void
     */
    public function testUpdateFieldsUpdatingStorageHandlerTranslatable()
    {
        self::markTestIncomplete( "@todo Test updateFields() with updating storage handler and translatable fields" );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     *
     * @return void
     */
    public function testUpdateFieldsExistingLanguages()
    {
        $partlyMockedFieldHandler = $this->getPartlyMockedFieldHandler( array( "updateField" ) );
        $typeHandlerMock = $this->getTypeHandlerMock();

        $typeHandlerMock->expects( $this->once() )
            ->method( "load" )
            ->with( $this->equalTo( 1 ) )
            ->will( $this->returnValue( $this->getContentTypeFixture( true ) ) );

        $partlyMockedFieldHandler->expects( $this->exactly( 6 ) )
            ->method( 'updateField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->equalTo( $this->getContentFixture() )
            );

        $partlyMockedFieldHandler->updateFields(
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
     * @param bool $forUpdate
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    protected function getContentTypeFixture( $forUpdate = false )
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
                "isTranslatable" => $forUpdate,
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

        $struct->fields = $content->fields;
        foreach ( $struct->fields as $index => $field )
        {
            $field->id = $index;
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
            array(
                "some-type" => $this->getFieldTypeMock()
            )
        );
        $mock->typeHandler = $this->getTypeHandlerMock();

        return $mock;
    }

    /**
     * Returns the handler to test with $methods mocked
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected function getPartlyMockedFieldHandler( array $methods )
    {
        $mock = $this->getMock(
            '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldHandler',
            $methods,
            array(
                $this->getContentGatewayMock(),
                $this->getMapperMock(),
                $this->getStorageHandlerMock(),
                array(
                    "some-type" => $this->getFieldTypeMock()
                )
            )
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
