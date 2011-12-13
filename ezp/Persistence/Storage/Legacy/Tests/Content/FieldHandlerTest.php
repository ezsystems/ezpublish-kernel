<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\FieldHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Content,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\Version,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Storage\Legacy\Content\FieldHandler,
    ezp\Persistence\Storage\Legacy\Content\Mapper,
    ezp\Persistence\Storage\Legacy\Content\Gateway;

/**
 * Test case for Content Handler
 */
class FieldHandlerTest extends TestCase
{
    /**
     * Gateway mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected $contentGatewayMock;

    /**
     * Type gateway mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     */
    protected $typeGatewayMock;

    /**
     * Mapper mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Mapper
     */
    protected $mapperMock;

    /**
     * Storage handler mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\StorageHandler
     */
    protected $storageHandlerMock;

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\FieldHandler::createNewFields
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
                    'ezp\\Persistence\\Content\\Field'
                )
            )->will(
                $this->returnValue( new StorageFieldValue() )
            );

        $contentGatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'insertNewField' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content' ),
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldValue' )
            )->will( $this->returnValue( 42 ) );

        $storageHandlerMock->expects( $this->exactly( 2 ) )
            ->method( 'storeFieldData' )
            ->with( $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ) );

        $fieldHandler->createNewFields( $this->getContentFixture() );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\FieldHandler::loadExternalFieldData
     */
    public function testLoadExternalFieldData()
    {
        $fieldHandler = $this->getFieldHandler();

        $storageHandlerMock = $this->getStorageHandlerMock();

        $storageHandlerMock->expects( $this->exactly( 2 ) )
            ->method( 'getFieldData' )
            ->with( $this->isInstanceOf( 'ezp\Persistence\Content\Field' ) );

        $fieldHandler->loadExternalFieldData( $this->getContentFixture() );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\FieldHandler::updateFields
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
            ->with( $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ) )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $contentGatewayMock = $this->getContentGatewayMock();
        $contentGatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'updateField' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldValue'
                )
            );

        $storageHandlerMock = $this->getStorageHandlerMock();
        $storageHandlerMock->expects( $this->exactly( 2 ) )
            ->method( 'storeFieldData' )
            ->with( $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ) );

        $fieldHandler->updateFields( $this->getUpdateStructFixture() );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\FieldHandler::updateFields
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
            ->with( $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ) )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $contentGatewayMock = $this->getContentGatewayMock();
        $contentGatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'updateNonTranslatableField' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldValue'
                ),
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\UpdateStruct' )
            );

        $storageHandlerMock = $this->getStorageHandlerMock();
        $storageHandlerMock->expects( $this->exactly( 2 ) )
            ->method( 'storeFieldData' )
            ->with( $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ) );

        $fieldHandler->updateFields( $this->getUpdateStructFixture() );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\FieldHandler::deleteFields
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
     * Returns a Content fixture
     *
     * @return \ezp\Persistence\Content
     */
    protected function getContentFixture()
    {
        $content = new Content();
        $content->id = 42;
        $content->version = new Version();
        $content->version->versionNo = 2;

        $firstField = new Field();
        $firstField->type = 'some-type';
        $firstField->fieldDefinitionId = 23;
        $firstField->value = new FieldValue;
        $firstField->value->data = $this->getMock( 'ezp\\Content\\FieldType\\Value' );

        $secondField = clone $firstField;

        $content->version->fields = array(
            $firstField, $secondField
        );

        return $content;
    }

    /**
     * Returns an UpdateStruct fixture
     *
     * @return \ezp\Persistence\Content\UpdateStruct
     */
    protected function getUpdateStructFixture()
    {
        $struct = new UpdateStruct();

        $content = $this->getContentFixture();

        $struct->versionNo = 3;
        $struct->fields = $content->version->fields;

        return $struct;
    }

    /**
     * Returns a FieldHandler to test
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\FieldHandler
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
     * @return \ezp\Persistence\Storage\Legacy\Content\StorageHandler
     */
    protected function getStorageHandlerMock()
    {
        if ( !isset( $this->storageHandlerMock ) )
        {
            $this->storageHandlerMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageHandler',
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
     * @return \ezp\Persistence\Storage\Legacy\Content\Mapper
     */
    protected function getMapperMock()
    {
        if ( !isset( $this->mapperMock ) )
        {
            $this->mapperMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\Mapper',
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
     * @return \ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     */
    protected function getTypeGatewayMock()
    {
        if ( !isset( $this->typeGatewayMock ) )
        {
            $this->typeGatewayMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Gateway'
            );
        }
        return $this->typeGatewayMock;
    }

    /**
     * Returns a mock object for the Content Gateway.
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected function getContentGatewayMock()
    {
        if ( !isset( $this->contentGatewayMock ) )
        {
            $this->contentGatewayMock = $this->getMockForAbstractClass(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\Gateway'
            );
        }
        return $this->contentGatewayMock;
    }
}
