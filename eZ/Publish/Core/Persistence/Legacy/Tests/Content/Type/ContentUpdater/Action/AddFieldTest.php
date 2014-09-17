<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\ContentUpdater\Action\AddFieldTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\ContentUpdater\Action;

use eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\AddField;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content;
use PHPUnit_Framework_TestCase;

/**
 * Test case for Content Type Updater.
 */
class AddFieldTest extends PHPUnit_Framework_TestCase
{
    /**
     * Content gateway mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGatewayMock;

    /**
     * Content gateway mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected $contentStorageHandlerMock;

    /**
     * FieldValue converter mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter
     */
    protected $fieldValueConverterMock;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $contentMapperMock;

    /**
     * AddField action to test
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\AddField
     */
    protected $addFieldAction;

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater::__construct
     *
     * @return void
     */
    public function testCtor()
    {
        $action = $this->getAddFieldAction();

        $this->assertAttributeSame(
            $this->getContentGatewayMock(),
            'contentGateway',
            $action
        );
        $this->assertAttributeEquals(
            $this->getFieldDefinitionFixture(),
            'fieldDefinition',
            $action
        );
        $this->assertAttributeSame(
            $this->getFieldValueConverterMock(),
            'fieldValueConverter',
            $action
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\AddField::apply
     *
     * @return void
     */
    public function testApply()
    {
        $action = $this->getAddFieldAction();
        $contentInfo = $this->getContentInfoFixture();
        $content = $this->getContentFixture();
        $versionNumbers = array( 1 );
        $field = $this->getFieldReference( 1, "eng-GB" );

        $this->getContentGatewayMock()->expects( $this->once() )
            ->method( 'listVersionNumbers' )
            ->with( $this->equalTo( "contentId" ) )
            ->will( $this->returnValue( $versionNumbers ) );

        $this->getContentGatewayMock()->expects( $this->once() )
            ->method( 'load' )
            ->with( $contentInfo->id, $contentInfo->currentVersionNo )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()->expects( $this->once() )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content ) ) );

        $this->getFieldValueConverterMock()
            ->expects( $this->once() )
            ->method( 'toStorageValue' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\FieldValue' ),
                $this->isInstanceOf( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue" )
            );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'insertNewField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            )->will( $this->returnValue( 23 ) );

        $field->id = 23;

        $this->getContentStorageHandlerMock()
            ->expects( $this->once() )
            ->method( 'storeFieldData' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->equalTo( $field )
            )->will( $this->returnValue( false ) );

        $action->apply( $contentInfo );

        $this->assertEquals(
            2,
            count( $content->fields ),
            'Field not added to content'
        );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field', $content->fields[1] );
        $this->assertEquals( 23, $content->fields[1]->id );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\AddField::apply
     *
     * @return void
     */
    public function testApplyUpdatingStorageHandler()
    {
        $action = $this->getAddFieldAction();
        $contentInfo = $this->getContentInfoFixture();
        $content = $this->getContentFixture();
        $versionNumbers = array( 1 );
        $field = $this->getFieldReference( 1, "eng-GB" );

        $this->getContentGatewayMock()->expects( $this->once() )
            ->method( 'listVersionNumbers' )
            ->with( $this->equalTo( "contentId" ) )
            ->will( $this->returnValue( $versionNumbers ) );

        $this->getContentGatewayMock()->expects( $this->once() )
            ->method( 'load' )
            ->with( $contentInfo->id, $contentInfo->currentVersionNo )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()->expects( $this->once() )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content ) ) );

        $this->getFieldValueConverterMock()
            ->expects( $this->exactly( 2 ) )
            ->method( 'toStorageValue' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\FieldValue' ),
                $this->isInstanceOf( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue" )
            );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'insertNewField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            )->will( $this->returnValue( 23 ) );

        $field->id = 23;

        $this->getContentStorageHandlerMock()
            ->expects( $this->once() )
            ->method( 'storeFieldData' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->equalTo( $field )
            )->will( $this->returnValue( true ) );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'updateNonTranslatableField' )
            ->with(
                $this->equalTo( $field ),
                $this->isInstanceOf( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue" ),
                $this->equalTo( "contentId" )
            );

        $action->apply( $contentInfo );

        $this->assertEquals(
            2,
            count( $content->fields ),
            'Field not added to content'
        );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field', $content->fields[1] );
        $this->assertEquals( 23, $content->fields[1]->id );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\AddField::apply
     *
     * @return void
     */
    public function testApplyUpdatingStorageHandlerTranslatableField()
    {
        // Prepare action for translatable field
        $action = $this->getAddFieldAction( true );
        $contentInfo = $this->getContentInfoFixture();
        $content = $this->getContentFixture();
        $versionNumbers = array( 1 );
        $field = $this->getFieldReference( 1, "eng-GB" );

        $this->getContentGatewayMock()->expects( $this->once() )
            ->method( 'listVersionNumbers' )
            ->with( $this->equalTo( "contentId" ) )
            ->will( $this->returnValue( $versionNumbers ) );

        $this->getContentGatewayMock()->expects( $this->once() )
            ->method( 'load' )
            ->with( $contentInfo->id, $contentInfo->currentVersionNo )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()->expects( $this->once() )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content ) ) );

        $this->getFieldValueConverterMock()
            ->expects( $this->exactly( 2 ) )
            ->method( 'toStorageValue' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\FieldValue' ),
                $this->isInstanceOf( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue" )
            );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'insertNewField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            )->will( $this->returnValue( 23 ) );

        $field->id = 23;

        $this->getContentStorageHandlerMock()
            ->expects( $this->once() )
            ->method( 'storeFieldData' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->equalTo( $field )
            )->will( $this->returnValue( true ) );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'updateField' )
            ->with(
                $this->equalTo( $field ),
                $this->isInstanceOf( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue" )
            );

        $action->apply( $contentInfo );

        $this->assertEquals(
            2,
            count( $content->fields ),
            'Field not added to content'
        );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field', $content->fields[1] );
        $this->assertEquals( 23, $content->fields[1]->id );
    }

    /**
     * Returns a ContentInfo  fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    protected function getContentInfoFixture()
    {
        $contentInfo = new Content\ContentInfo();
        $contentInfo->id = "contentId";
        $contentInfo->currentVersionNo = "versionNo";

        return $contentInfo;
    }

    /**
     * Returns a Content fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentFixture()
    {
        $contentInfo = new Content\ContentInfo();
        $contentInfo->id = "contentId";
        $versionInfo = new Content\VersionInfo();
        $versionInfo->contentInfo = $contentInfo;

        $content = new Content();
        $content->versionInfo = $versionInfo;
        $content->versionInfo->versionNo = 3;
        $content->fields = array( new Field( array( "languageCode" => "eng-GB" ) ) );

        return $content;
    }

    /**
     * Returns a Content Gateway mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected function getContentGatewayMock()
    {
        if ( !isset( $this->contentGatewayMock ) )
        {
            $this->contentGatewayMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Gateway'
            );
        }
        return $this->contentGatewayMock;
    }

    /**
     * Returns a FieldValue converter mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter
     */
    protected function getFieldValueConverterMock()
    {
        if ( !isset( $this->fieldValueConverterMock ) )
        {
            $this->fieldValueConverterMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter'
            );
        }
        return $this->fieldValueConverterMock;
    }

    /**
     * Returns a Content StorageHandler mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected function getContentStorageHandlerMock()
    {
        if ( !isset( $this->contentStorageHandlerMock ) )
        {
            $this->contentStorageHandlerMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageHandler',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->contentStorageHandlerMock;
    }

    /**
     * Returns a Content mapper mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected function getContentMapperMock()
    {
        if ( !isset( $this->contentMapperMock ) )
        {
            $this->contentMapperMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Mapper',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->contentMapperMock;
    }

    /**
     * Returns a FieldDefinition fixture
     *
     * @param bool $isTranslatable
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    protected function getFieldDefinitionFixture( $isTranslatable = false )
    {
        $fieldDef = new Content\Type\FieldDefinition();
        $fieldDef->id = 42;
        $fieldDef->isTranslatable = $isTranslatable;
        $fieldDef->fieldType = 'ezstring';
        $fieldDef->defaultValue = new Content\FieldValue();
        return $fieldDef;
    }

    /**
     * Returns a reference Field
     *
     * @param int $versionNo
     * @param string $languageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Field
     */
    public function getFieldReference( $versionNo, $languageCode )
    {
        $field = new Field();

        $field->fieldDefinitionId = 42;
        $field->type = 'ezstring';
        $field->value = new Content\FieldValue();
        $field->versionNo = $versionNo;
        $field->languageCode = $languageCode;

        return $field;
    }

    /**
     * Returns the AddField action to test
     *
     * @param bool $isTranslatable
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\AddField
     */
    protected function getAddFieldAction( $isTranslatable = false )
    {
        if ( !isset( $this->addFieldAction ) )
        {
            $this->addFieldAction = new AddField(
                $this->getContentGatewayMock(),
                $this->getFieldDefinitionFixture( $isTranslatable ),
                $this->getFieldValueConverterMock(),
                $this->getContentStorageHandlerMock(),
                $this->getContentMapperMock()
            );
        }
        return $this->addFieldAction;
    }
}
