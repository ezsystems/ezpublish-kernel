<?php
/**
 * File containing the CountryTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use ezp\Content\FieldType\Factory,
    ezp\Content\FieldType\Country\Type as Country,
    ezp\Content\FieldType\Country\Value as CountryValue,
    ezp\Base\Exception\BadFieldTypeInput,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class CountryTest extends PHPUnit_Framework_TestCase
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
            "ezp\\Content\\FieldType\\Country\\Type",
            Factory::build( "ezcountry" ),
            "Country object not returned for 'ezcountry', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::allowedValidators
     */
    public function testCountrySupportedValidators()
    {
        $ft = new Country();
        self::assertSame( array(), $ft->allowedValidators(), "The set of allowed validators does not match what is expected." );
    }

    /**
     * @covers \ezp\Content\FieldType\Country\Type::canParseValue
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessage must be an array
     * @group fieldType
     */
    public function testCanParseValueInvalidFormat()
    {
        $ft = new Country();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new CountryValue( 42 ) );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Country\Type::canParseValue
     */
    public function testCanParseValueValidFormat()
    {
        $ft = new Country();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );

        $value = new CountryValue( array( "Belgium", "Norway" ) );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Country\Type::toFieldValue
     */
    public function testToFieldValue()
    {
        $countries = array( "Belgium", "Norway" );
        $ft = new Country();
        $ft->setValue( $fv = new CountryValue( $countries ) );

        $fieldValue = $ft->toFieldValue();

        self::assertSame( $fv, $fieldValue->data );
        self::assertNull( $fieldValue->externalData );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Country\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $countries = array( "Belgium", "Norway" );
        $value = new CountryValue( $countries );
        self::assertSame( $countries, $value->values );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Country\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $country = "Belgium";
        $value = CountryValue::fromString( $country );
        self::assertInstanceOf( "ezp\\Content\\FieldType\\Country\\Value", $value );
        self::assertSame( array( $country ), $value->values );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Country\Value::__toString
     */
    public function testFieldValueToString()
    {
        $country = "Belgium";
        $value = CountryValue::fromString( $country );
        self::assertSame( $country, (string)$value );

        self::assertSame(
            array( $country ),
            CountryValue::fromString( (string)$value )->values,
            "fromString() and __toString() must be compatible"
        );
    }
}
