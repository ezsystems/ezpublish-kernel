<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\SPI\Persistence\Content\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Version,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Mapper,
    eZ\Publish\Core\Persistence\Legacy\Content\Gateway;

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
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::createNewFields
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
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) );

        $fieldHandler->createNewFields( $this->getContentFixture() );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::loadExternalFieldData
     */
    public function testLoadExternalFieldData()
    {
        $fieldHandler = $this->getFieldHandler();

        $storageHandlerMock = $this->getStorageHandlerMock();

        $storageHandlerMock->expects( $this->exactly( 2 ) )
            ->method( 'getFieldData' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) );

        $fieldHandler->loadExternalFieldData( $this->getContentFixture() );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     */
    public function testUpdateFieldsTranslatable()
    {
        $fieldHandler = $this->getFieldHandler();

        $typeGatewayMock = $this->getTypeGatewayMock();
        $typeGatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'isFieldTranslatable' )
            ->with( $this->equalTo( 23 ), $this->equalTo( 0 ) )
            ->will( $this->returnValue( true ) );

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
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) );

        $fieldHandler->updateFields( 42, 3, $this->getUpdateStructFixture() );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     */
    public function testUpdateFieldsNonTranslatable()
    {
        $fieldHandler = $this->getFieldHandler();

        $typeGatewayMock = $this->getTypeGatewayMock();
        $typeGatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'isFieldTranslatable' )
            ->with( $this->equalTo( 23 ), $this->equalTo( 0 ) )
            ->will( $this->returnValue( false ) );

        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->exactly( 2 ) )
            ->method( 'convertToStorageValue' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $contentGatewayMock = $this->getContentGatewayMock();
        $contentGatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'updateNonTranslatableField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->isInstanceOf(
                    'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue'
                ),
                42
            );

        $storageHandlerMock = $this->getStorageHandlerMock();
        $storageHandlerMock->expects( $this->exactly( 2 ) )
            ->method( 'storeFieldData' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ) );

        $fieldHandler->updateFields( 42, 3, $this->getUpdateStructFixture() );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::deleteFields
     */
    public function testDeleteFields()
    {
        $fieldHandler = $this->getFieldHandler();

        $contentGatewayMock = $this->getContentGatewayMock();
        $contentGatewayMock->expects( $this->once() )
            ->method( 'getFieldIdsByType' )
            ->with( $this->equalTo( 42 ) )
            ->will( $this->returnValue( array( 'some-type' => array( 2, 3 ) ) ) );

        $storageHandlerMock = $this->getStorageHandlerMock();
        $storageHandlerMock->expects( $this->once() )
            ->method( 'deleteFieldData' )
            ->with(
            $this->equalTo( 'some-type' ),
            $this->equalTo( array( 2, 3 ) )
        );

        $contentGatewayMock->expects( $this->once() )
            ->method( 'deleteFields' )
            ->with( $this->equalTo( 42 ) );

        $fieldHandler->deleteFields( 42 );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::deleteFields
     */
    public function testDeleteFieldsWithSecondArgument()
    {
        $fieldHandler = $this->getFieldHandler();

        $contentGatewayMock = $this->getContentGatewayMock();
        $contentGatewayMock->expects( $this->once() )
            ->method( 'getFieldIdsByType' )
            ->with( $this->equalTo( 42 ) )
            ->will( $this->returnValue( array( 'some-type' => array( 2, 3 ) ) ) );

        $storageHandlerMock = $this->getStorageHandlerMock();
        $storageHandlerMock->expects( $this->once() )
            ->method( 'deleteFieldData' )
            ->with(
            $this->equalTo( 'some-type' ),
            $this->equalTo( array( 2, 3 ) )
        );

        $contentGatewayMock->expects( $this->once() )
            ->method( 'deleteFields' )
            ->with( $this->equalTo( 42 ) );

        $fieldHandler->deleteFields( 42, 2 );
    }

    /**
     * Returns a Content fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentFixture()
    {
        $content = new Content;
        $content->contentInfo = new ContentInfo;
        $content->contentInfo->id = 42;
        $content->versionInfo = new VersionInfo;
        $content->versionInfo->versionNo = 2;

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
     * @return \eZ\Publish\SPI\Persistence\Content\UpdateStruct
     */
    protected function getUpdateStructFixture()
    {
        $struct = new UpdateStruct();

        $content = $this->getContentFixture();

        $struct->fields = $content->fields;

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
