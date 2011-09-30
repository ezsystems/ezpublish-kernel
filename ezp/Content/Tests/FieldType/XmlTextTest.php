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
    ezp\Content\FieldType\XmlText\Type as XmlTextType,
    ezp\Content\FieldType\XmlText\Value as XmlTextValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class XmlTextTest extends PHPUnit_Framework_TestCase
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
            "ezp\\Content\\FieldType\\XmlText\\Type",
            Factory::build( "ezxmltext" ),
            "Country object not returned for 'ezxmltext', incorrect mapping? "
        );
    }

    /**
     * @covers \ezp\Content\FieldType\XmlText\Type::canParseValue
     * @expectedException ezp\Base\Exception\BadFieldTypeInput
     * @group fieldType
     */
    public function testCanParseValueInvalidFormat()
    {
        $ft = new XmlTextType();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );

        $value = new XmlTextValue( '<a href="http://www.google.com/">bar</foo>' );
        $refMethod->invoke( $ft, $value );
    }

    /**
     * @covers \ezp\Content\FieldType\XmlText\Type::canParseValue
     * @group fieldType
     */
    public function testCanParseValueValidFormat()
    {
        $ft = new XmlTextType();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );

        $value = new XmlTextValue( '<strong>This is a piece of text</strong>' );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @covers \ezp\Content\FieldType\XmlText\Type::toFieldValue
     * @group fieldType
     */
    public function testToFieldValue()
    {
        self::markTestIncomplete();
        /*$countries = array( "Belgium", "Norway" );
        $ft = new Country();
        $ft->setValue( $fv = new CountryValue( $countries ) );

        $fieldValue = $ft->toFieldValue();

        self::assertSame( $fv, $fieldValue->data );
        self::assertNull( $fieldValue->externalData );*/
    }

    /**
     * @covers \ezp\Content\FieldType\XmlText\Value::__construct
     * @group fieldType
     */
    public function testBuildFieldValueWithParam()
    {
        self::markTestIncomplete();
        /*$countries = array( "Belgium", "Norway" );
        $value = new CountryValue( $countries );
        self::assertSame( $countries, $value->values );*/
    }

    /**
     * @covers \ezp\Content\FieldType\XmlText\Value::fromString
     * @group fieldType
     */
    public function testBuildFieldValueFromString()
    {
        self::markTestIncomplete();
        /*$country = "Belgium";
        $value = CountryValue::fromString( $country );
        self::assertInstanceOf( "ezp\\Content\\FieldType\\Country\\Value", $value );
        self::assertSame( array( $country ), $value->values );*/
    }

    /**
     * @covers \ezp\Content\XmlText\Country\Value::__toString
     * @group fieldType
     */
    public function testFieldValueToString()
    {
        self::markTestIncomplete();
        /*$country = "Belgium";
        $value = CountryValue::fromString( $country );
        self::assertSame( $country, (string)$value );

        self::assertSame(
            array( $country ),
            CountryValue::fromString( (string)$value )->values,
            "fromString() and __toString() must be compatible"
        );*/
    }

    public function testGetSortInfo()
    {
        $type = new XmlTextType();
        $ref = new ReflectionObject( $type );
        $refMethod = $ref->getMethod( "getSortInfo" );
        $refMethod->setAccessible( true );

        self::assertFalse( $refMethod->invoke( $type ) );
    }
}
