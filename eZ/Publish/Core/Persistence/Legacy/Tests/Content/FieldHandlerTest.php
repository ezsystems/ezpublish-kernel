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
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
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
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::createNewFields
     *
     * @return void
     */
    public function testCreateNewFields()
    {
        $fieldHandler = $this->getFieldHandler();

        $mapperMock = $this->getMapperMock();
        $contentGatewayMock = $this->getContentGatewayMock();
        $storageHandlerMock = $this->getStorageHandlerMock();

        $mapperMock->expects( $this->exactly( 2 ) )
            ->method( 'convertToStorageValue' )
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content\\Field'
                )
            )->will(
                $this->returnValue( new StorageFieldValue() )
            );

        $contentGatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'insertNewField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            )->will( $this->returnValue( 42 ) );

        $storageHandlerMock->expects( $this->exactly( 2 ) )
            ->method( 'storeFieldData' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' )
            );

        $fieldHandler->createNewFields( $this->getContentFixture() );
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

        $storageHandlerMock->expects( $this->exactly( 2 ) )
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
    public function testUpdateFieldsTranslatable()
    {
        $fieldHandler = $this->getFieldHandler();

        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->exactly( 2 ) )
            ->method( 'convertToStorageValue' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $contentGatewayMock = $this->getContentGatewayMock();
        $contentGatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'updateField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->isInstanceOf(
                    'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue'
                )
            );

        $storageHandlerMock = $this->getStorageHandlerMock();
        $storageHandlerMock->expects( $this->exactly( 2 ) )
            ->method( 'storeFieldData' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' )
            );

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
    public function testUpdateFieldsCreatesNewFields()
    {
        $partlyMockedFieldHandler = $this->getPartlyMockedFieldHandler( array( "createNewField" ) );

        $partlyMockedFieldHandler->expects( $this->exactly( 2 ) )
            ->method( 'createNewField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->equalTo( $this->getContentFixture() )
            );

        $partlyMockedFieldHandler->updateFields(
            $this->getContentFixture(),
            $this->getUpdateStructFixture( false )
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
    protected function getContentFixture()
    {
        $content = new Content;
        $content->versionInfo = new VersionInfo;
        $content->versionInfo->versionNo = 2;
        $content->versionInfo->contentInfo = new ContentInfo;
        $content->versionInfo->contentInfo->id = 42;

        $firstField = new Field();
        $firstField->type = 'some-type';
        $firstField->fieldDefinitionId = 23;
        $firstField->value = new FieldValue;

        $secondField = clone $firstField;

        $content->fields = array(
            $firstField, $secondField
        );

        return $content;
    }

    /**
     * Returns an UpdateStruct fixture
     *
     * @param boolean $setIds
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UpdateStruct
     */
    protected function getUpdateStructFixture( $setIds = true )
    {
        $struct = new UpdateStruct();

        $content = $this->getContentFixture();

        $struct->fields = $content->fields;
        if ( $setIds )
        {
            foreach ( $struct->fields as $index => $field )
            {
                $field->id = $index;
            }
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
        return new FieldHandler(
            $this->getContentGatewayMock(),
            $this->getTypeGatewayMock(),
            $this->getMapperMock(),
            $this->getStorageHandlerMock()
        );
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
        return $this->getMock(
            '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldHandler',
            $methods,
            array(
                $this->getContentGatewayMock(),
                $this->getTypeGatewayMock(),
                $this->getMapperMock(),
                $this->getStorageHandlerMock()
            )
        );
    }

    /**
     * Returns a StorageHandler mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
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
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
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
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    protected function getTypeGatewayMock()
    {
        if ( !isset( $this->typeGatewayMock ) )
        {
            $this->typeGatewayMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\Gateway'
            );
        }
        return $this->typeGatewayMock;
    }

    /**
     * Returns a mock object for the Content Gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
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
}
