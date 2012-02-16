<?php
/**
 * File containing the CountryTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Factory,
    eZ\Publish\Core\Repository\FieldType\Country\Type as Country,
    eZ\Publish\Core\Repository\FieldType\Country\Value as CountryValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class CountryTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test will make sure a correct mapping for the field type string has
     * been made.
     *
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Factory::build
     */
    public function testFactory()
    {
        self::assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\FieldType\\Country\\Type",
            Factory::build( "ezcountry" ),
            "Country object not returned for 'ezcountry', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedValidators
     */
    public function testCountrySupportedValidators()
    {
        $ft = new Country();
        self::assertSame( array(), $ft->allowedValidators(), "The set of allowed validators does not match what is expected." );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Country\Type::canParseValue
     */
    public function testCanParseValueValidFormatSingle()
    {
        $ft = new Country();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );

        $value = new CountryValue( "Belgium" );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Country\Type::canParseValue
     */
    public function testCanParseValueValidFormatMultiple()
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
     * @covers \eZ\Publish\Core\Repository\FieldType\Country\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $countries = array( "Belgium", "Norway" );
        $ft = new Country();
        $fieldValue = $ft->toPersistenceValue( new CountryValue( $countries ) );

        self::assertSame( $countries, $fieldValue->data );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Country\Value::__construct
     * @covers \eZ\Publish\Core\Repository\FieldType\Country\Value::getCountriesInfo
     */
    public function testBuildFieldValueWithParam()
    {
        $countries = array( "Belgium", "Norway" );
        $value = new CountryValue( $countries );
        self::assertSame(
            array(
                "BE" => array(
                    "Name" => "Belgium",
                    "Alpha2" => "BE",
                    "Alpha3" => "BEL",
                    "IDC" => 32,
                ),
                "NO" => array(
                    "Name" => "Norway",
                    "Alpha2" => "NO",
                    "Alpha3" => "NOR",
                    "IDC" => 47,
                ),
            ),
            $value->getCountriesInfo()
        );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Country\Value::fromString
     * @covers \eZ\Publish\Core\Repository\FieldType\Country\Value::getCountriesInfo
     */
    public function testBuildFieldValueFromString()
    {
        $country = "Belgium";
        $value = CountryValue::fromString( $country );
        self::assertInstanceOf( "eZ\\Publish\\Core\\Repository\\FieldType\\Country\\Value", $value );
        self::assertSame(
            array(
                "BE" => array(
                    "Name" => "Belgium",
                    "Alpha2" => "BE",
                    "Alpha3" => "BEL",
                    "IDC" => 32,
                ),
            ),
            $value->getCountriesInfo()
        );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Country\Value::__toString
     */
    public function testFieldValueToString()
    {
        $country = "Belgium";
        $value = CountryValue::fromString( $country );
        self::assertSame( $country, (string)$value );

        self::assertSame(
            array(
                "BE" => array(
                    "Name" => "Belgium",
                    "Alpha2" => "BE",
                    "Alpha3" => "BEL",
                    "IDC" => 32,
                ),
            ),
            CountryValue::fromString( (string)$value )->getCountriesInfo(),
            "fromString() and __toString() must be compatible"
        );
    }

    /**
     * Tests creating countries
     *
     * @group fieldType
     * @dataProvider providerForConstructorOK
     * @covers \eZ\Publish\Core\Repository\FieldType\Country\Value::__construct
     */
    public function testConstructorCorrectValues( $value )
    {
        $this->assertInstanceOf( "eZ\\Publish\\Core\\Repository\\FieldType\\Country\\Value", new CountryValue( $value ) );
    }

    public function providerForConstructorOK()
    {
        return array(
            array( null ),
            array( "Belgium" ),
            array( "BE" ),
            array( "BEL" ),
            array( array( "Belgium", "Norway", "France" ) ),
            array( array( "BE", "NO", "FR" ) ),
            array( array( "BEL", "NOR", "FRA" ) ),
            array(
                array(
                    "Korea, Democratic People's Republic of",
                    "French Southern Territories",
                    "Central African Republic",
                    "Heard Island and McDonald Islands",
                    "South Georgia and The South Sandwich Islands",
                )
            ),
        );
    }

    /**
     * Tests validating a wrong value
     *
     * @group fieldType
     * @dataProvider providerForConstructorKO
     * @expectedException \eZ\Publish\Core\Repository\FieldType\Country\Exception\InvalidValue
     * @expectedExceptionMessage is not a valid value country identifier.
     * @covers \eZ\Publish\Core\Repository\FieldType\Country\Value::__construct
     */
    public function testConstructorWrongValues( $value )
    {
        $this->assertInstanceOf( "eZ\\Publish\\Core\\Repository\\FieldType\\Country\\Value", new CountryValue( $value ) );
    }

    public function providerForConstructorKO()
    {
        return array(
            array( "LegoLand" ),
            array( array( "Norway", "France", "LegoLand" ) ),
            array( array( "FR", "BE", "LE" ) ),
            array( array( "FRE", "BEL", "LEG" ) ),
        );
    }
}
