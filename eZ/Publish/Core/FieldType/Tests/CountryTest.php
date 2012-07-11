<?php
/**
 * File containing the CountryTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\Country\Type as Country,
    eZ\Publish\Core\FieldType\Country\Value as CountryValue,
    eZ\Publish\Core\FieldType\Tests\FieldTypeTest,
    ReflectionObject;

class CountryTest extends FieldTypeTest
{
    /**
     * @var \eZ\Publish\Core\FieldType\Country\Type
     */
    protected $ft;

    public function setUp()
    {
        parent::setUp();
        $this->ft = new Country(
            $this->validatorService,
            $this->fieldTypeTools,
            array(
                "BE" => array(
                    "Name" => "Belgium",
                    "Alpha2" => "BE",
                    "Alpha3" => "BEL",
                    "IDC" => 32,
                ),
                "FR" => array(
                    "Name" => "France",
                    "Alpha2" => "FR",
                    "Alpha3" => "FRA",
                    "IDC" => 33,
                ),
                "NO" => array(
                    "Name" => "Norway",
                    "Alpha2" => "NO",
                    "Alpha3" => "NOR",
                    "IDC" => 47,
                ),
                "KP" => array(
                    "Name" => "Korea, Democratic People's Republic of",
                    "Alpha2" => "KP",
                    "Alpha3" => "PRK",
                    "IDC" => 850,
                ),
                "TF" => array(
                    "Name" => "French Southern Territories",
                    "Alpha2" => "TF",
                    "Alpha3" => "ATF",
                    "IDC" => 0,
                ),
                "CF" => array(
                    "Name" => "Central African Republic",
                    "Alpha2" => "CF",
                    "Alpha3" => "CAF",
                    "IDC" => 236,
                ),
            )
        );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testCountrySupportedValidators()
    {
        self::assertSame( array(), $this->ft->getValidatorConfigurationSchema(), "The set of allowed validators does not match what is expected." );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Country\Type::acceptValue
     */
    public function testAcceptValueValidFormatSingle()
    {
        $ref = new ReflectionObject( $this->ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );

        $value = new CountryValue( array( "Belgium" ) );
        self::assertSame( $value, $refMethod->invoke( $this->ft, $value ) );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Country\Type::acceptValue
     */
    public function testAcceptValueValidFormatMultiple()
    {
        $ref = new ReflectionObject( $this->ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );

        $value = new CountryValue( array( "Belgium", "Norway" ) );
        self::assertSame( $value, $refMethod->invoke( $this->ft, $value ) );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Country\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $countries = array( "Belgium", "Norway" );
        $fieldValue = $this->ft->toPersistenceValue( new CountryValue( $countries ) );

        self::assertSame( $countries, $fieldValue->data );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Country\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $countries = array( "Belgium", "Norway" );
        $countriesData = array(
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
        );
        $value = new CountryValue( $countries, $countriesData );
        self::assertSame(
            $countries,
            $value->values
        );
        self::assertSame(
            $countriesData,
            $value->data
        );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Country\Value::__toString
     */
    public function testFieldValueToString()
    {
        $country = "Belgium";
        $value = new CountryValue( (array)$country );
        self::assertSame( $country, (string)$value );

        $value2 = new CountryValue( (array)((string)$value) );
        self::assertSame(
            array( "Belgium" ),
            $value2->values,
            "fromString() and __toString() must be compatible"
        );
    }

    /**
     * Tests creating countries
     *
     * @group fieldType
     * @dataProvider providerForConstructorOK
     * @covers \eZ\Publish\Core\FieldType\Country\Type::buildValue
     */
    public function testConstructorCorrectValues( $value )
    {
        $this->assertInstanceOf( "eZ\\Publish\\Core\\FieldType\\Country\\Value", $this->ft->buildValue( $value ) );
    }

    public function providerForConstructorOK()
    {
        return array(
            array( null ),
            array( array() ),
            array( "Belgium" ),
            array( array( "Belgium" ) ),
            array( array( "BE" ) ),
            array( array( "BEL" ) ),
            array( array( "Belgium", "Norway", "France" ) ),
            array( array( "BE", "NO", "FR" ) ),
            array( array( "BEL", "NOR", "FRA" ) ),
            array(
                array(
                    "Korea, Democratic People's Republic of",
                    "French Southern Territories",
                    "Central African Republic",
                )
            ),
        );
    }

    /**
     * Tests validating a wrong value
     *
     * @group fieldType
     * @dataProvider providerForConstructorKO
     * @expectedException \eZ\Publish\Core\FieldType\Country\Exception\InvalidValue
     * @expectedExceptionMessage is not a valid value country identifier
     * @covers \eZ\Publish\Core\FieldType\Country\Type::buildValue
     */
    public function testConstructorWrongValues( $value )
    {
        $this->assertInstanceOf( "eZ\\Publish\\Core\\FieldType\\Country\\Value", $this->ft->buildValue( $value ) );
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
