<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Type\ContentUpdater\Action\AddFieldTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Type\ContentUpdater\Action;
use ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater\Action\AddField,
    ezp\Persistence\Content,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue;

/**
 * Test case for Content Type Updater.
 */
class AddFieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Content gateway mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected $contentGatewayMock;

    /**
     * FieldValue converter mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter
     */
    protected $fieldValueConverterMock;

    /**
     * AddField action to test
     *
     * @var ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater\Action\AddField
     */
    protected $addFieldAction;

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater::__construct
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
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater\Action\AddField::apply
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

        $this->getContentGatewayMock()->expects( $this->once() )
            ->method( 'insertNewField' )
            ->with(
                $this->equalTo( $content ),
                $this->equalTo( $this->getFieldReference() ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldValue'
                )
            )->will( $this->returnValue( 23 ) );

        $action->apply( $content );

        $this->assertEquals(
            1,
            count( $content->version->fields ),
            'Field not added to content'
        );
        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Field',
            $content->version->fields[0]
        );
        $this->assertEquals(
            23,
            $content->version->fields[0]->id
        );
    }

    /**
     * Returns a Content fixture
     *
     * @return \ezp\Persistence\Content
     */
    protected function getContentFixture()
    {
        $content = new Content();
        $content->version = new Content\Version();
        $content->version->fields = array();
        $content->version->versionNo = 3;
        return $content;
    }

    /**
     * Returns a Content Gateway mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected function getContentGatewayMock()
    {
        if ( !isset( $this->contentGatewayMock ) )
        {
            $this->contentGatewayMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\Gateway'
            );
        }
        return $this->contentGatewayMock;
    }

    /**
     * Returns a FieldValue converter mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter
     */
    protected function getFieldValueConverterMock()
    {
        if ( !isset( $this->fieldValueConverterMock ) )
        {
            $this->fieldValueConverterMock = $this->getMockForAbstractClass(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter'
            );
        }
        return $this->fieldValueConverterMock;
    }

    /**
     * Returns a FieldDefinition fixture
     *
     * @return \ezp\Persistence\Content\Type\FieldDefinition
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
     * @return \ezp\Persistence\Content\Field
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
     * @return \ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater\Action\AddField
     */
    protected function getAddFieldAction()
    {
        if ( !isset( $this->addFieldAction ) )
        {
            $this->addFieldAction = new AddField(
                $this->getContentGatewayMock(),
                $this->getFieldDefinitionFixture(),
                $this->getFieldValueConverterMock()
            );
        }
        return $this->addFieldAction;
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
