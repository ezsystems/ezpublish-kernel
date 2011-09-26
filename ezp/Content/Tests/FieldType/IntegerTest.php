<?php
/**
 * File containing the IntegerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use ezp\Content\FieldType\Factory,
    ezp\Content\FieldType\Integer\Type as Integer,
    ezp\Content\FieldType\Integer\Value as IntegerValue,
    ezp\Base\Exception\BadFieldTypeInput,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class IntegerTest extends PHPUnit_Framework_TestCase
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
            "ezp\\Content\\FieldType\\Integer\\Type",
            Factory::build( "ezinteger" ),
            "Integer object not returned for 'ezinteger', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::allowedValidators
     */
    public function testIntegerSupportedValidators()
    {
        $ft = new Integer();
        self::assertSame(
            array( "ezp\\Content\\FieldType\\Integer\\IntegerValueValidator" ),
            $ft->allowedValidators(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @covers \ezp\Content\FieldType\Integer\Type::canParseValue
     * @expectedException ezp\Base\Exception\BadFieldTypeInput
     * @group fieldType
     */
    public function testCanParseValueInvalidFormat()
    {
        $ft = new Integer();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new IntegerValue( "Strings should not work." ) );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Integer\Type::canParseValue
     */
    public function testCanParseValueValidFormat()
    {
        $ft = new Integer();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );

        $value = new IntegerValue( 42 );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Integer\Type::toFieldValue
     */
    public function testToFieldValue()
    {
        $integer = 42;
        $ft = new Integer();
        $ft->setValue( $fv = new IntegerValue( $integer ) );

        $fieldValue = $ft->toFieldValue();

        self::assertSame( $fv, $fieldValue->data );
        self::assertNull( $fieldValue->externalData );
        self::assertSame( array( "sort_key_int" => $integer ), $fieldValue->sortKey );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Integer\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $value = new IntegerValue( 420 );
        self::assertSame( 420, $value->value );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Integer\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new IntegerValue;
        self::assertSame( 0, $value->value );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Integer\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $integer = 4200;
        $value = IntegerValue::fromString( $integer );
        self::assertInstanceOf( "ezp\\Content\\FieldType\\Integer\\Value", $value );
        self::assertSame( $integer, $value->value );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Integer\Value::__toString
     */
    public function testFieldValueToString()
    {
        $integer = "4200";
        $value = IntegerValue::fromString( $integer );
        self::assertSame( $integer, (string)$value );

        self::assertSame(
            (int)$integer,
            IntegerValue::fromString( (string)$value )->value,
            "fromString() and __toString() must be compatible"
        );
    }
}
