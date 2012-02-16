<?php
/**
 * File containing the FieldTypeTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use eZ\Publish\Core\Repository\FieldType\TextLine\Value as TextLineValue,
    eZ\Publish\Core\Repository\FieldType\TextLine\StringLengthValidator,
    ezp\Content\Type\Concrete as ConcreteType,
    ezp\Content\Type\FieldDefinition,
    ezp\Base\Exception\BadFieldTypeInput,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class FieldTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Repository\FieldType
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
        $this->stub = $this->getMockBuilder(
            'eZ\\Publish\\Core\\Repository\\FieldType'
        )->getMockForAbstractClass();

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
     * @covers \eZ\Publish\Core\Repository\FieldType::__construct
     */
    public function testConstructor()
    {
        $refFieldSettings = $this->refStub->getProperty( 'fieldSettings' );
        $refFieldSettings->setAccessible( true );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Repository\\FieldType\\FieldSettings', $refFieldSettings->getValue( $this->stub ) );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType::initializeSettings
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
     * @covers \eZ\Publish\Core\Repository\FieldType::getFieldTypeSettings
     */
    public function testGetFieldTypeSettings()
    {
        $this->stub->initializeSettings( $this->allowedSettings );
        $fieldTypeSettings = $this->stub->getFieldTypeSettings();
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Repository\\FieldType\\FieldSettings', $fieldTypeSettings );
        self::assertSame( $this->allowedSettings, $fieldTypeSettings->getArrayCopy() );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedSettings
     */
    public function testAllowedSettings()
    {
        $this->stub->initializeSettings( $this->allowedSettings );
        self::assertSame( array_keys( $this->allowedSettings ), $this->stub->allowedSettings() );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedValidators
     */
    public function testAllowedValidators()
    {
        self::assertSame( $this->allowedValidators, $this->stub->allowedValidators() );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType::setValue
     */
    public function testSetValue()
    {
        $mockValue = $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\Value' );
        $mockValue->bountyHunter = 'Boba Fett';
        $mockValue->jediMaster = 'Obi-Wan Kenobi';
        $this->stub
            ->expects( $this->once() )
            ->method( 'canParseValue' )
            ->will( $this->returnArgument( 0 ) );

        $this->stub->setValue( $mockValue );
        self::assertEquals( $mockValue, $this->stub->getValue() );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType::setValue
     * @expectedException \ezp\Base\Exception\BadFieldTypeInput
     */
    public function testSetValueInvalid()
    {
        $mockValue = $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\Value' );
        $this->stub
            ->expects( $this->once() )
            ->method( 'canParseValue' )
            ->will( $this->throwException( new BadFieldTypeInput( $mockValue ) ) );

        $this->stub->setValue( $mockValue );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType::getValue
     */
    public function testGetValue()
    {
        $mockValue = $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\Value' );
        $mockValue->bountyHunter = 'Han Solo';
        $mockValue->jediMaster = 'Yoda';
        $this->stub
            ->expects( $this->once() )
            ->method( 'canParseValue' )
            ->will( $this->returnArgument( 0 ) );

        $this->stub->setValue( $mockValue );
        self::assertEquals( $mockValue, $this->stub->getValue() );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType::fillConstraintsFromValidator
     */
    public function testFillConstraintsFromValidator()
    {
        $contentType = new ConcreteType;
        $contentType->identifier = 'article';
        $fieldDef = new FieldDefinition( $contentType, 'ezstring' );
        $fieldDef->identifier = 'title';
        $fieldDef->setDefaultValue( new TextLineValue( 'New article' ) );
        $fieldDef->fieldTypeConstraints->validators = array(
            'SomeValidator' => array( 'foo' => 'bar' )
        );

        $validator = new StringLengthValidator();
        $validator->maxStringLength = 20;
        $fieldDef->getType()->fillConstraintsFromValidator( $fieldDef->fieldTypeConstraints, $validator );
        $expectedValidatorConstraints = array(
            'eZ\\Publish\\Core\\Repository\\FieldType\\TextLine\\StringLengthValidator' => array(
                'maxStringLength' => 20,
                'minStringLength' => false
            ),
            'SomeValidator' => array( 'foo' => 'bar' )
        );
        self::assertSame( $expectedValidatorConstraints, $fieldDef->fieldTypeConstraints->validators );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType::fillConstraintsFromValidator
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     */
    public function testFillConstraintsFromUnsupportedValidator()
    {
        $contentType = new ConcreteType;
        $contentType->identifier = 'article';
        $fieldDef = new FieldDefinition( $contentType, 'ezstring' );
        $fieldDef->identifier = 'title';
        $fieldDef->setDefaultValue( new TextLineValue( 'New article' ) );

        $validator = $this->getMockForAbstractClass( 'eZ\\Publish\\Core\\Repository\\FieldType\\Validator' );
        $fieldDef->getType()->fillConstraintsFromValidator( $fieldDef->fieldTypeConstraints, $validator );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType::update
     */
    public function testUpdateFieldSetValueEvent()
    {
        $mockValue = $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\Value' );
        $mockValue->foo = 'bar';
        $this->stub
            ->expects( $this->once() )
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
     * @covers \eZ\Publish\Core\Repository\FieldType::update
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
     * @covers \eZ\Publish\Core\Repository\FieldType::update
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
