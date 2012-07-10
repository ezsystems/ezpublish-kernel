<?php
/**
 * File containing the FloatTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType;
use eZ\Publish\Core\FieldType\Float\Type as Float,
    eZ\Publish\Core\FieldType\Float\Value as FloatValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class FloatTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\FieldType::allowedValidators
     */
    public function testFloatSupportedValidators()
    {
        $ft = new Float();
        self::assertSame(
            array( "FloatValueValidator" ),
            $ft->allowedValidators(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Float\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @group fieldType
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Float();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new FloatValue( "Strings should not work." ) );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Float\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new Float();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );

        $value = new FloatValue( 42.42 );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Float\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $ft = new Float();
        $fieldValue = $ft->toPersistenceValue( new FloatValue( 42.42 ) );

        self::assertSame( 42.42, $fieldValue->data );
        self::assertSame( array( "sort_key_string" => "", "sort_key_int" => 0 ), $fieldValue->sortKey );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Float\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $value = new FloatValue( 420.42 );
        self::assertSame( 420.42, $value->value );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Float\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new FloatValue;
        self::assertSame( 0.0, $value->value );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Float\Value::__toString
     */
    public function testFieldValueToString()
    {
        $float = "4200.42";
        $value = new FloatValue( $float );
        self::assertSame( $float, (string)$value );

        $value2 = new FloatValue( (string)$value );
        self::assertSame(
            $float,
            $value2->value,
            "fromString() and __toString() must be compatible"
        );
    }
}
