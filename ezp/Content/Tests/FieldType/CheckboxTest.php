<?php
/**
 * File containing the CheckboxTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Factory,
    eZ\Publish\Core\Repository\FieldType\Checkbox\Type as Checkbox,
    eZ\Publish\Core\Repository\FieldType\Checkbox\Value as CheckboxValue,
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
     * @covers \eZ\Publish\Core\Repository\FieldType\Factory::build
     */
    public function testBuildFactory()
    {
        self::assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\FieldType\\Checkbox\\Type",
            Factory::build( "ezboolean" ),
            "Checkbox object not returned for 'ezstring', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedValidators
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
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedSettings
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
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Type::getDefaultValue
     */
    public function testDefaultValueWithDefaultSetting()
    {
        $ft = new Checkbox();
        self::assertFalse( $ft->getFieldSetting( 'defaultValue' ) );
        $ft->setFieldSetting( 'defaultValue', true );
        self::assertTrue(
            $ft->getDefaultValue()->bool,
            'defaultValue setting should be reflected in default value object'
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Type::acceptValue
     * @expectedException \ezp\Base\Exception\InvalidArgumentValue
     * @group fieldType
     * @group ezboolean
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Checkbox();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new CheckboxValue( 'I am definitely not a boolean' ) );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Type::acceptValue
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     * @group fieldType
     * @group ezboolean
     */
    public function testAcceptValueInvalidValue()
    {
        $ft = new Checkbox();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\Value' ) );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new Checkbox();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );

        $value = new CheckboxValue( true );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $ft = new Checkbox();
        $fieldValue = $ft->toPersistenceValue( new CheckboxValue( true ) );

        self::assertSame( true, $fieldValue->data );
        self::assertSame( array( 'sort_key_int' => 1 ), $fieldValue->sortKey );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Value::__construct
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
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new CheckboxValue;
        self::assertSame( false, $value->bool );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $stringTrue = "1";
        $stringTrue2 = "Any non-empty string means true";
        $stringFalse = "0";
        $stringFalse2 = "";
        $value = CheckboxValue::fromString( $stringTrue );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Repository\\FieldType\\Checkbox\\Value', $value );
        self::assertTrue( $value->bool );
        self::assertTrue( CheckboxValue::fromString( $stringTrue2 )->bool );
        self::assertFalse( CheckboxValue::fromString( $stringFalse )->bool );
        self::assertFalse( CheckboxValue::fromString( $stringFalse2 )->bool );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Value::__toString
     */
    public function testFieldValueToString()
    {
        $valueTrue = new CheckboxValue( true );
        $valueFalse = new CheckboxValue( false );
        self::assertSame( '1', (string)$valueTrue );
        self::assertSame( '0', (string)$valueFalse );
    }
}
