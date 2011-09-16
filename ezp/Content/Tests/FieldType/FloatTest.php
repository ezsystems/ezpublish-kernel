<?php
/**
 * File containing the FloatTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use ezp\Content\FieldType\Factory,
    ezp\Content\FieldType\Float\Type as Float,
    ezp\Content\FieldType\Float\Value as FloatValue,
    ezp\Content\FieldType\Validator\StringLengthValidator,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Persistence\Content\FieldValue,
    ezp\Content\Type\FieldDefinition,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class FloatTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test will make sure a correct mapping for the field type string has
     * been made.
     *
     * @group fieldType
     * @covers \ezp\Content\FieldType\Factory::build
     */
    public function testFactory()
    {
        self::assertInstanceOf(
            "ezp\\Content\\FieldType\\Float\\Type",
            Factory::build( "ezfloat" ),
            "Float object not returned for 'ezfloat', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::allowedValidators
     */
    public function testFloatSupportedValidators()
    {
        $ft = new Float();
        self::assertSame( array( "FloatValueValidator" ), $ft->allowedValidators(), "The set of allowed validators does not match what is expected." );
    }

    /**
     * @covers \ezp\Content\FieldType\Float\Type::canParseValue
     * @expectedException ezp\Base\Exception\BadFieldTypeInput
     * @group fieldType
     */
    public function testCanParseValueInvalidFormat()
    {
        $ft = new Float();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new FloatValue( "Strings should not work." ) );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Float\Type::canParseValue
     */
    public function testCanParseValueValidFormat()
    {
        $ft = new Float();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );

        $value = new FloatValue( 42.42 );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Float\Type::setFieldValue
     */
    public function testSetFieldValue()
    {
        $float = 42.42;
        $ft = new Float();
        $ft->setValue( new FloatValue( $float ) );

        $fieldValue = new FieldValue();
        $ft->setFieldValue( $fieldValue );

        self::assertSame( array( "value" => $float ), $fieldValue->data );
        self::assertNull( $fieldValue->externalData );
        self::assertSame( array( "sort_key_string" => "", "sort_key_int" => 0 ), $fieldValue->sortKey );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Float\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $value = new FloatValue( 420.42 );
        self::assertSame( 420.42, $value->value );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Float\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new FloatValue;
        self::assertSame( 0.0, $value->value );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Float\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $float = 4200.42;
        $value = FloatValue::fromString( $float );
        self::assertInstanceOf( "ezp\\Content\\FieldType\\Float\\Value", $value );
        self::assertSame( $float, $value->value );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Float\Value::__toString
     */
    public function testFieldValueToString()
    {
        $float = "4200.42";
        $value = FloatValue::fromString( $float );
        self::assertSame( $float, (string)$value );

        self::assertSame(
            $float,
            FloatValue::fromString( (string)$value )->value,
            "fromString() and __toString() must be compatible"
        );
    }
}
