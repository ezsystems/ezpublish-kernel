<?php
/**
 * File containing the FieldTypeTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use PHPUnit_Framework_TestCase,
    ReflectionObject,
    ReflectionProperty,
    ezp\Content\FieldType\FieldSettings,
    ezp\Content\FieldType\Value,
    ezp\Content\FieldType\TextLine\Value as TextLineValue,
    ezp\Content\FieldType\TextLine\StringLengthValidator,
    ezp\Content\Type as ContentType,
    ezp\Content\Type\FieldDefinition,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Persistence\Content\FieldValue as PersistenceFieldValue;

class FieldTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Content\FieldType
     */
    protected $stub;

    /**
     * @var \ReflectionObject
     */
    protected $refStub;

    protected $allowedValidators = array( 'WaceMindu', 'Yoda' );

    protected $allowedSettings = array(
        'Wookie' => 'Chewbacca',
        'Jedi' => 'Luke Skywalker',
        'Sith' => 'Darth Vader',
        'Slime freak' => 'Jabba the Hutt'
    );

    /**
     * Gets mock object and initialize it with Reflection
     */
    protected function setUp()
    {
        $this->stub = $this->getMockBuilder( 'ezp\\Content\\FieldType' )
                           ->getMockForAbstractClass();

        $this->refStub = new ReflectionObject( $this->stub );
        // Allowed Validators
        $refValidators = $this->refStub->getProperty( 'allowedValidators' );
        $refValidators->setAccessible( true );
        $refValidators->setValue( $this->stub, $this->allowedValidators );
        // Allowed settings
        $refSettings = $this->refStub->getProperty( 'allowedSettings' );
        $refSettings->setAccessible( true );
        $refSettings->setValue( $this->stub, $this->allowedSettings );
    }

    protected function tearDown()
    {
        unset( $this->stub, $this->refStub );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::__construct
     */
    public function testConstructor()
    {
        $refFieldSettings = $this->refStub->getProperty( 'fieldSettings' );
        $refFieldSettings->setAccessible( true );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\FieldSettings', $refFieldSettings->getValue( $this->stub ) );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::initializeSettings
     */
    public function testInitializeSettings()
    {
        $this->stub->initializeSettings( $this->allowedSettings );
        $refFieldSettings = $this->refStub->getProperty( 'fieldSettings' );
        $refFieldSettings->setAccessible( true );
        self::assertSame( $this->allowedSettings, $refFieldSettings->getValue( $this->stub )->getArrayCopy() );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::getFieldTypeSettings
     */
    public function testGetFieldTypeSettings()
    {
        $this->stub->initializeSettings( $this->allowedSettings );
        self::assertSame( $this->allowedSettings, $this->stub->getFieldTypeSettings() );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::allowedSettings
     */
    public function testAllowedSettings()
    {
        $this->stub->initializeSettings( $this->allowedSettings );
        self::assertSame( array_keys( $this->allowedSettings ), $this->stub->allowedSettings() );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::allowedValidators
     */
    public function testAllowedValidators()
    {
        self::assertSame( $this->allowedValidators, $this->stub->allowedValidators() );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::setValue
     */
    public function testSetValue()
    {
        $mockValue = $this->getMock( 'ezp\\Content\\FieldType\\Value' );
        $mockValue->bountyHunter = 'Boba Fett';
        $mockValue->jediMaster = 'Obi-Wan Kenobi';
        $this->stub->expects( $this->once() )
                   ->method( 'canParseValue' )
                   ->will( $this->returnArgument( 0 ) );

        $this->stub->setValue( $mockValue );
        self::assertEquals( $mockValue, $this->stub->getValue() );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::setValue
     * @expectedException \ezp\Base\Exception\BadFieldTypeInput
     */
    public function testSetValueInvalid()
    {
        $mockValue = $this->getMock( 'ezp\\Content\\FieldType\\Value' );
        $this->stub->expects( $this->once() )
                   ->method( 'canParseValue' )
                   ->will( $this->throwException( new BadFieldTypeInput( $mockValue ) ) );

        $this->stub->setValue( $mockValue );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::getValue
     */
    public function testGetValue()
    {
        $mockValue = $this->getMock( 'ezp\\Content\\FieldType\\Value' );
        $mockValue->bountyHunter = 'Han Solo';
        $mockValue->jediMaster = 'Yoda';
        $this->stub->expects( $this->once() )
                   ->method( 'canParseValue' )
                   ->will( $this->returnArgument( 0 ) );

        $this->stub->setValue( $mockValue );
        self::assertEquals( $mockValue, $this->stub->getValue() );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::toFieldValue
     */
    public function testToFieldValue()
    {
        $fieldValue = $this->getMock( 'ezp\\Content\\FieldType\\Value' );
        $sortingInfo = array( 'sort_key_string' => "The Force is strong" );
        $this->stub->expects( $this->once() )
                   ->method( 'canParseValue' )
                   ->will( $this->returnArgument( 0 ) );
        $this->stub->setValue( $fieldValue );
        $this->stub->expects( $this->once() )
                   ->method( 'getSortInfo' )
                   ->will( $this->returnValue( $sortingInfo ) );

        $fieldVo = $this->stub->toFieldValue();
        self::assertInstanceOf( "ezp\\Persistence\\Content\\FieldValue", $fieldVo );
        self::assertEquals( $fieldValue, $fieldVo->data );
        self::assertSame( $sortingInfo, $fieldVo->sortKey );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::fillConstraintsFromValidator
     */
    public function testFillConstraintsFromValidator()
    {
        $contentType = new ContentType;
        $contentType->identifier = 'article';
        $fields = $contentType->getFields();
        $fieldDef = new FieldDefinition( $contentType, 'ezstring' );
        $fieldDef->identifier = 'title';
        $fieldDef->setDefaultValue( new TextLineValue( 'New article' ) );
        $fieldDef->fieldTypeConstraints = array(
            'SomeValidator' => array( 'foo' => 'bar' )
        );
        $fields[] = $fieldDef;

        $validator = new StringLengthValidator();
        $validator->maxStringLength = 20;
        $fieldDef->getType()->fillConstraintsFromValidator( $fieldDef, $validator );
        $expectedConstraints = array(
            'ezp\\Content\\FieldType\\TextLine\\StringLengthValidator' => array(
                'maxStringLength' => 20,
                'minStringLength' => false
            ),
            'SomeValidator' => array( 'foo' => 'bar' )
        );
        self::assertSame( $expectedConstraints, $fieldDef->fieldTypeConstraints );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::fillConstraintsFromValidator
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     */
    public function testFillConstraintsFromUnsupportedValidator()
    {
        $contentType = new ContentType;
        $contentType->identifier = 'article';
        $fields = $contentType->getFields();
        $fieldDef = new FieldDefinition( $contentType, 'ezstring' );
        $fieldDef->identifier = 'title';
        $fieldDef->setDefaultValue( new TextLineValue( 'New article' ) );
        $fields[] = $fieldDef;

        $validator = $this->getMockForAbstractClass( 'ezp\\Content\\FieldType\\Validator' );
        $fieldDef->getType()->fillConstraintsFromValidator( $fieldDef, $validator );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::update
     */
    public function testUpdateFieldSetValueEvent()
    {
        $mockValue = $this->getMock( 'ezp\\Content\\FieldType\\Value' );
        $mockValue->foo = 'bar';
        $this->stub->expects( $this->once() )
                   ->method( 'canParseValue' )
                   ->with( $mockValue )
                   ->will( $this->returnArgument( 0 ) );

        $this->stub->update(
            $this->getMock( 'ezp\\Base\\Observable' ),
            'field/setValue',
            array( 'value' => $mockValue )
        );

        self::assertEquals( $mockValue, $this->stub->getValue() );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::update
     * @expectedException \ezp\Base\Exception\InvalidArgumentValue
     */
    public function testUpdateFieldSetValueEventWithoutValue()
    {
        $this->stub->update(
            $this->getMock( 'ezp\\Base\\Observable' ),
            'field/setValue'
        );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::update
     * @expectedException \ezp\Base\Exception\InvalidArgumentValue
     */
    public function testUpdateFieldSetValueEventWithoutValidValue()
    {
        $this->stub->update(
            $this->getMock( 'ezp\\Base\\Observable' ),
            'field/setValue',
            array( 'foo' => 'bar' )
        );
    }
}
