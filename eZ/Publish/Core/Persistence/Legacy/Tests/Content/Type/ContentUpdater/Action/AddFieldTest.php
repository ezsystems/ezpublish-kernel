<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\ContentUpdater\Action\AddFieldTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\ContentUpdater\Action;

use eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\AddField;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;

/**
 * Test case for Content Type Updater.
 */
class AddFieldTest extends \PHPUnit_Framework_TestCase
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
        $content = $this->getContentFixture();

        $this->getFieldValueConverterMock()
            ->expects( $this->once() )
            ->method( 'toStorageValue' )
            ->with( $this->equalTo( $this->getFieldReference()->value ) )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $this->getContentGatewayMock()->expects( $this->any() )// "any" is workaround for failure, should be once
            ->method( 'insertNewField' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ),
                $this->equalTo( $this->getFieldReference() ),
                $this->isInstanceOf(
                    'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue'
                )
            )->will( $this->returnValue( 23 ) );

        $this->getContentStorageHandlerMock()->expects( $this->once() )
            ->method( 'storeFieldData' );

        $action->apply( $content );

        $this->assertEquals(
            1,
            count( $content->fields ),
            'Field not added to content'
        );
        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Field',
            $content->fields[0]
        );
        $this->assertEquals(
            23,
            $content->fields[0]->id
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\AddField::apply
     *
     * @return void
     */
    public function testApplyUnTranslatableField()
    {
        self::markTestIncomplete( "@todo Test Action/AddField with untranslatable field" );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\AddField::apply
     *
     * @return void
     */
    public function testApplyUpdatingStorageHandler()
    {
        self::markTestIncomplete( "@todo Test Action/AddField with Storage Handler that returns true" );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\AddField::apply
     *
     * @return void
     */
    public function testApplyUpdatingStorageHandlerUnTranslatableField()
    {
        self::markTestIncomplete( "@todo Test Action/AddField with Storage Handler that returns true and untranslatable field" );
    }

    /**
     * Returns a Content fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentFixture()
    {
        $content = new Content();
        $content->versionInfo = new Content\VersionInfo();
        $content->fields = array();
        $content->versionInfo->versionNo = 3;
        return $content;
    }

    /**
     * Returns a Content Gateway mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
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
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter
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
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
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
     * Returns a FieldDefinition fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    protected function getFieldDefinitionFixture()
    {
        $fieldDef = new Content\Type\FieldDefinition();
        $fieldDef->id = 42;
        $fieldDef->fieldType = 'ezstring';
        $fieldDef->defaultValue = new Content\FieldValue();
        return $fieldDef;
    }

    /**
     * Returns a reference Field
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Field
     */
    public function getFieldReference()
    {
        $field = new Content\Field();
        $field->fieldDefinitionId = 42;
        $field->type = 'ezstring';
        $field->value = new Content\FieldValue();
        $field->versionNo = 3;
        return $field;
    }

    /**
     * Returns the AddField action to test
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\AddField
     */
    protected function getAddFieldAction()
    {
        if ( !isset( $this->addFieldAction ) )
        {
            $this->addFieldAction = new AddField(
                $this->getContentGatewayMock(),
                $this->getFieldDefinitionFixture(),
                $this->getFieldValueConverterMock(),
                $this->getContentStorageHandlerMock()
            );
        }
        return $this->addFieldAction;
    }
}
