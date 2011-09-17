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
     * @covers \ezp\Content\FieldType::getValue
     */
    public function testGetValueIsStillDefault()
    {
        $mockDefaultValue = $this->getMock( 'ezp\\Content\\FieldType\\Value' );
        $mockDefaultValue->bountyHunter = 'Yet another space wimp';
        $mockDefaultValue->jediMaster = 'Jedi order is supposed to have been erased from galaxy';

        $refDefault = $this->refStub->getProperty( 'defaultValue' );
        $refDefault->setAccessible( true );
        $refDefault->setValue( $this->stub, $mockDefaultValue );
        self::assertEquals( $mockDefaultValue, $this->stub->getValue() );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::setFieldValue
     */
    public function testSetFieldValue()
    {
        $data = array( 'value' => "The Force is strong with him -- Darth Vader" );
        $this->stub->expects( $this->once() )
                   ->method( 'getValueData' )
                   ->will( $this->returnValue( $data ) );

        $sortingInfo = array( 'sort_key_string' => "The Force is strong" );
        $this->stub->expects( $this->once() )
                   ->method( 'getSortInfo' )
                   ->will( $this->returnValue( $sortingInfo ) );

        $fieldVo = new PersistenceFieldValue;
        self::assertSame( $fieldVo, $this->stub->setFieldValue( $fieldVo ) );
        self::assertSame( $data, $fieldVo->data );
        self::assertSame( $sortingInfo, $fieldVo->sortKey );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::fillConstraintsFromValidator
     */
    public function testFillConstraintsFromValidator()
    {
        $this->markTestIncomplete();
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
