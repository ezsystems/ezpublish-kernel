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
use ReflectionObject;

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
        $action = new AddField(
            $this->getContentGatewayMock(),
            $this->getFieldDefinitionFixture(),
            $this->getFieldValueConverterMock(),
            $this->getContentStorageHandlerMock(),
            $this->getContentMapperMock()
        );

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

    public function testApplySingleVersionSingleTranslation()
    {
        $contentId = 42;
        $versionNumbers = array( 1 );
        $content = $this->getContentFixture( 1, array( "cro-HR" ) );
        $action = $this->getMockedAction( array( "insertField" ) );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'listVersionNumbers' )
            ->with( $this->equalTo( $contentId ) )
            ->will( $this->returnValue( $versionNumbers ) );

        $this->getContentGatewayMock()
            ->expects( $this->at( 1 ) )
            ->method( 'load' )
            ->with( $contentId, 1 )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()
            ->expects( $this->once() )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content ) ) );

        $action
            ->expects( $this->once() )
            ->method( "insertField" )
            ->with( $content, $this->getFieldReference( null, 1, "cro-HR" ) )
            ->will( $this->returnValue( "fieldId1" ) );

        $action->apply( $contentId );
    }

    public function testApplySingleVersionMultipleTranslations()
    {
        $contentId = 42;
        $versionNumbers = array( 1 );
        $content = $this->getContentFixture( 1, array( "eng-GB", "ger-DE" ) );
        $action = $this->getMockedAction( array( "insertField" ) );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'listVersionNumbers' )
            ->with( $this->equalTo( $contentId ) )
            ->will( $this->returnValue( $versionNumbers ) );

        $this->getContentGatewayMock()
            ->expects( $this->at( 1 ) )
            ->method( 'load' )
            ->with( $contentId, 1 )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()
            ->expects( $this->once() )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content ) ) );

        $action
            ->expects( $this->at( 0 ) )
            ->method( "insertField" )
            ->with( $content, $this->getFieldReference( null, 1, "eng-GB" ) )
            ->will( $this->returnValue( "fieldId1" ) );

        $action
            ->expects( $this->at( 1 ) )
            ->method( "insertField" )
            ->with( $content, $this->getFieldReference( null, 1, "ger-DE" ) )
            ->will( $this->returnValue( "fieldId2" ) );

        $action->apply( $contentId );
    }

    public function testApplyMultipleVersionsSingleTranslation()
    {
        $contentId = 42;
        $versionNumbers = array( 1, 2 );
        $content1 = $this->getContentFixture( 1, array( "eng-GB" ) );
        $content2 = $this->getContentFixture( 2, array( "eng-GB" ) );
        $action = $this->getMockedAction( array( "insertField" ) );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'listVersionNumbers' )
            ->with( $this->equalTo( $contentId ) )
            ->will( $this->returnValue( $versionNumbers ) );

        $this->getContentGatewayMock()
            ->expects( $this->at( 1 ) )
            ->method( 'load' )
            ->with( $contentId, 1 )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()
            ->expects( $this->at( 0 ) )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content1 ) ) );

        $this->getContentGatewayMock()
            ->expects( $this->at( 2 ) )
            ->method( 'load' )
            ->with( $contentId, 2 )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()
            ->expects( $this->at( 1 ) )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content2 ) ) );

        $action
            ->expects( $this->at( 0 ) )
            ->method( "insertField" )
            ->with( $content1, $this->getFieldReference( null, 1, "eng-GB" ) )
            ->will( $this->returnValue( "fieldId1" ) );

        $action
            ->expects( $this->at( 1 ) )
            ->method( "insertField" )
            ->with( $content2, $this->getFieldReference( "fieldId1", 2, "eng-GB" ) )
            ->will( $this->returnValue( "fieldId1" ) );

        $action->apply( $contentId );
    }

    public function testApplyMultipleVersionsMultipleTranslations()
    {
        $contentId = 42;
        $versionNumbers = array( 1, 2 );
        $content1 = $this->getContentFixture( 1, array( "eng-GB", "ger-DE" ) );
        $content2 = $this->getContentFixture( 2, array( "eng-GB", "ger-DE" ) );
        $action = $this->getMockedAction( array( "insertField" ) );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'listVersionNumbers' )
            ->with( $this->equalTo( $contentId ) )
            ->will( $this->returnValue( $versionNumbers ) );

        $this->getContentGatewayMock()
            ->expects( $this->at( 1 ) )
            ->method( 'load' )
            ->with( $contentId, 1 )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()
            ->expects( $this->at( 0 ) )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content1 ) ) );

        $this->getContentGatewayMock()
            ->expects( $this->at( 2 ) )
            ->method( 'load' )
            ->with( $contentId, 2 )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()
            ->expects( $this->at( 1 ) )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content2 ) ) );

        $action
            ->expects( $this->at( 0 ) )
            ->method( "insertField" )
            ->with( $content1, $this->getFieldReference( null, 1, "eng-GB" ) )
            ->will( $this->returnValue( "fieldId1" ) );

        $action
            ->expects( $this->at( 1 ) )
            ->method( "insertField" )
            ->with( $content1, $this->getFieldReference( null, 1, "ger-DE" ) )
            ->will( $this->returnValue( "fieldId2" ) );

        $action
            ->expects( $this->at( 2 ) )
            ->method( "insertField" )
            ->with( $content2, $this->getFieldReference( "fieldId1", 2, "eng-GB" ) )
            ->will( $this->returnValue( "fieldId1" ) );

        $action
            ->expects( $this->at( 3 ) )
            ->method( "insertField" )
            ->with( $content2, $this->getFieldReference( "fieldId2", 2, "ger-DE" ) )
            ->will( $this->returnValue( "fieldId2" ) );

        $action->apply( $contentId );
    }

    public function testInsertNewField()
    {
        $versionInfo = new Content\VersionInfo();
        $content = new Content();
        $content->versionInfo = $versionInfo;

        $value = new Content\FieldValue();

        $field = new Field();
        $field->id = null;
        $field->value = $value;

        $this->getFieldValueConverterMock()
            ->expects( $this->once() )
            ->method( "toStorageValue" )
            ->with(
                $value,
                $this->isInstanceOf( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue" )
            );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'insertNewField' )
            ->with(
                $content,
                $field,
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            )
            ->will( $this->returnValue( 23 ) );

        $this->getContentStorageHandlerMock()
            ->expects( $this->once() )
            ->method( "storeFieldData" )
            ->with( $versionInfo, $field )
            ->will( $this->returnValue( false ) );

        $this->getContentGatewayMock()->expects( $this->never() )->method( "updateField" );

        $action = $this->getMockedAction();

        $refAction = new ReflectionObject( $action );
        $refMethod = $refAction->getMethod( "insertField" );
        $refMethod->setAccessible( true );
        $fieldId = $refMethod->invoke( $action, $content, $field );

        $this->assertEquals( 23, $fieldId );
        $this->assertEquals( 23, $field->id );
    }

    public function testInsertNewFieldUpdating()
    {
        $versionInfo = new Content\VersionInfo();
        $content = new Content();
        $content->versionInfo = $versionInfo;

        $value = new Content\FieldValue();

        $field = new Field();
        $field->id = null;
        $field->value = $value;

        $this->getFieldValueConverterMock()
            ->expects( $this->exactly( 2 ) )
            ->method( "toStorageValue" )
            ->with(
                $value,
                $this->isInstanceOf( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue" )
            );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'insertNewField' )
            ->with(
                $content,
                $field,
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            )
            ->will( $this->returnValue( 23 ) );

        $this->getContentStorageHandlerMock()
            ->expects( $this->once() )
            ->method( "storeFieldData" )
            ->with( $versionInfo, $field )
            ->will( $this->returnValue( true ) );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( "updateField" )
            ->with(
                $field,
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            );

        $action = $this->getMockedAction();

        $refAction = new ReflectionObject( $action );
        $refMethod = $refAction->getMethod( "insertField" );
        $refMethod->setAccessible( true );
        $fieldId = $refMethod->invoke( $action, $content, $field );

        $this->assertEquals( 23, $fieldId );
        $this->assertEquals( 23, $field->id );
    }

    public function testInsertExistingField()
    {
        $versionInfo = new Content\VersionInfo();
        $content = new Content();
        $content->versionInfo = $versionInfo;

        $value = new Content\FieldValue();

        $field = new Field();
        $field->id = 32;
        $field->value = $value;

        $this->getFieldValueConverterMock()
            ->expects( $this->once() )
            ->method( "toStorageValue" )
            ->with(
                $value,
                $this->isInstanceOf( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue" )
            );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'insertExistingField' )
            ->with(
                $content,
                $field,
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            );

        $this->getContentStorageHandlerMock()
            ->expects( $this->once() )
            ->method( "storeFieldData" )
            ->with( $versionInfo, $field )
            ->will( $this->returnValue( false ) );

        $this->getContentGatewayMock()->expects( $this->never() )->method( "updateField" );

        $action = $this->getMockedAction();

        $refAction = new ReflectionObject( $action );
        $refMethod = $refAction->getMethod( "insertField" );
        $refMethod->setAccessible( true );
        $fieldId = $refMethod->invoke( $action, $content, $field );

        $this->assertEquals( 32, $fieldId );
        $this->assertEquals( 32, $field->id );
    }

    public function testInsertExistingFieldUpdating()
    {
        $versionInfo = new Content\VersionInfo();
        $content = new Content();
        $content->versionInfo = $versionInfo;

        $value = new Content\FieldValue();

        $field = new Field();
        $field->id = 32;
        $field->value = $value;

        $this->getFieldValueConverterMock()
            ->expects( $this->exactly( 2 ) )
            ->method( "toStorageValue" )
            ->with(
                $value,
                $this->isInstanceOf( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue" )
            );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'insertExistingField' )
            ->with(
                $content,
                $field,
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            );

        $this->getContentStorageHandlerMock()
            ->expects( $this->once() )
            ->method( "storeFieldData" )
            ->with( $versionInfo, $field )
            ->will( $this->returnValue( true ) );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( "updateField" )
            ->with(
                $field,
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue' )
            );

        $action = $this->getMockedAction();

        $refAction = new ReflectionObject( $action );
        $refMethod = $refAction->getMethod( "insertField" );
        $refMethod->setAccessible( true );
        $fieldId = $refMethod->invoke( $action, $content, $field );

        $this->assertEquals( 32, $fieldId );
        $this->assertEquals( 32, $field->id );
    }

    /**
     * Returns a Content fixture
     *
     * @param int $versionNo
     * @param array $languageCodes
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentFixture( $versionNo, array $languageCodes )
    {
        $contentInfo = new Content\ContentInfo();
        $contentInfo->id = "contentId";
        $versionInfo = new Content\VersionInfo();
        $versionInfo->contentInfo = $contentInfo;

        $content = new Content();
        $content->versionInfo = $versionInfo;
        $content->versionInfo->versionNo = $versionNo;

        $fields = array();
        foreach ( $languageCodes as $languageCode )
        {
            $fields[] = new Field( array( "languageCode" => $languageCode ) );
        }

        $content->fields = $fields;

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
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    protected function getFieldDefinitionFixture()
    {
        $fieldDef = new Content\Type\FieldDefinition();
        $fieldDef->id = 42;
        $fieldDef->isTranslatable = true;
        $fieldDef->fieldType = 'ezstring';
        $fieldDef->defaultValue = new Content\FieldValue();
        return $fieldDef;
    }

    /**
     * Returns a reference Field
     *
     * @param int $id
     * @param int $versionNo
     * @param string $languageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Field
     */
    public function getFieldReference( $id, $versionNo, $languageCode )
    {
        $field = new Field();

        $field->id = $id;
        $field->fieldDefinitionId = 42;
        $field->type = 'ezstring';
        $field->value = new Content\FieldValue();
        $field->versionNo = $versionNo;
        $field->languageCode = $languageCode;

        return $field;
    }

    /**
     * @param $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\AddField
     */
    protected function getMockedAction( $methods = array() )
    {
        return $this
            ->getMockBuilder( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\ContentUpdater\\Action\\AddField" )
            ->setMethods( (array)$methods )
            ->setConstructorArgs(
                array(
                    $this->getContentGatewayMock(),
                    $this->getFieldDefinitionFixture(),
                    $this->getFieldValueConverterMock(),
                    $this->getContentStorageHandlerMock(),
                    $this->getContentMapperMock()
                )
            )
            ->getMock();
    }
}
