<?php
/**
 * File containing the CheckboxTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use ezp\Content\FieldType\Factory,
    ezp\Content\FieldType\Checkbox\Type as Checkbox,
    ezp\Content\FieldType\Checkbox\Value as CheckboxValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class CheckboxTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test will make sure a correct mapping for the field type string has
     * been made.
     *
     * @group fieldType
     * @group ezboolean
     * @covers \ezp\Content\FieldType\Factory::build
     */
    public function testBuildFactory()
    {
        self::assertInstanceOf(
            "ezp\\Content\\FieldType\\Checkbox\\Type",
            Factory::build( "ezboolean" ),
            "Checkbox object not returned for 'ezstring', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \ezp\Content\FieldType::allowedValidators
     */
    public function testCheckboxSupportedValidators()
    {
        $ft = new Checkbox();
        self::assertSame(
            array(),
            $ft->allowedValidators(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \ezp\Content\FieldType::allowedSettings
     */
    public function testCheckboxAllowedSettings()
    {
        $ft = new Checkbox();
        self::assertSame(
            array( 'defaultValue' ),
            $ft->allowedSettings(),
            "The set of allowed settings does not match what is expected."
        );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \ezp\Content\FieldType\Checkbox\Type::getDefaultValue
     */
    public function testDefaultValueWithDefaultSetting()
    {
        $ft = new Checkbox();
        self::assertFalse( $ft->getFieldSetting( 'defaultValue' ) );
        $ft->setFieldSetting( 'defaultValue', true );
        self::assertTrue(
            $ft->getValue()->bool,
            'defaultValue setting should be reflected in default value object'
        );
    }

    /**
     * @covers \ezp\Content\FieldType\Checkbox\Type::canParseValue
     * @expectedException \ezp\Base\Exception\BadFieldTypeInput
     * @group fieldType
     * @group ezboolean
     */
    public function testCanParseValueInvalidFormat()
    {
        $ft = new Checkbox();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'canParseValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new CheckboxValue( 'I am definitely not a boolean' ) );
    }

    /**
     * @covers \ezp\Content\FieldType\Checkbox\Type::canParseValue
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     * @group fieldType
     * @group ezboolean
     */
    public function testCanParseValueInvalidValue()
    {
        $ft = new Checkbox();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'canParseValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $this->getMock( 'ezp\\Content\\FieldType\\Value' ) );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \ezp\Content\FieldType\Checkbox\Type::canParseValue
     */
    public function testCanParseValueValidFormat()
    {
        $ft = new Checkbox();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'canParseValue' );
        $refMethod->setAccessible( true );

        $value = new CheckboxValue( true );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \ezp\Content\FieldType\Checkbox\Type::toFieldValue
     */
    public function testToFieldValue()
    {
        $ft = new Checkbox();
        $ft->setValue( $fv = new CheckboxValue( true ) );
        $fieldValue = $ft->toFieldValue();

        self::assertSame( $fv, $fieldValue->data );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\FieldSettings', $fieldValue->fieldSettings );
        self::assertSame( array( 'sort_key_int' => 1 ), $fieldValue->sortKey );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \ezp\Content\FieldType\Checkbox\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $bool = true;
        $value = new CheckboxValue( $bool );
        self::assertSame( $bool, $value->bool );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \ezp\Content\FieldType\Checkbox\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new CheckboxValue;
        self::assertSame( false, $value->bool );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \ezp\Content\FieldType\Checkbox\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $stringTrue = "1";
        $stringTrue2 = "Any non-empty string means true";
        $stringFalse = "0";
        $stringFalse2 = "";
        $value = CheckboxValue::fromString( $stringTrue );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\Checkbox\\Value', $value );
        self::assertTrue( $value->bool );
        self::assertTrue( CheckboxValue::fromString( $stringTrue2 )->bool );
        self::assertFalse( CheckboxValue::fromString( $stringFalse )->bool );
        self::assertFalse( CheckboxValue::fromString( $stringFalse2 )->bool );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \ezp\Content\FieldType\Checkbox\Value::__toString
     */
    public function testFieldValueToString()
    {
        $valueTrue = new CheckboxValue( true );
        $valueFalse = new CheckboxValue( false );
        self::assertSame( '1', (string)$valueTrue );
        self::assertSame( '0', (string)$valueFalse );
    }
}
