<?php
/**
 * File containing the CountryValueValidatorTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use ezp\Content\FieldType\Country\Value as CountryValue,
    ezp\Content\FieldType\Country\CountryValueValidator,
    PHPUnit_Framework_TestCase;

class CountryValueValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test ensure an CountryValueValidator can be created
     *
     * @group fieldType
     * @group validator
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(
            "ezp\\Content\\FieldType\\Validator",
            new CountryValueValidator
        );
    }

    /**
     * Tests initializing with a wrong constraint
     *
     * @group fieldType
     * @group validator
     * @covers \ezp\Content\FieldType\Validator::initializeWithConstraints
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testInitializeBadConstraint()
    {
        $constraints = array(
            "unexisting" => 0,
        );
        $validator = new CountryValueValidator;
        $validator->initializeWithConstraints(
            $constraints
        );
    }

    /**
     * Tests setting a wrong constraint
     *
     * @group fieldType
     * @group validator
     * @covers \ezp\Content\FieldType\Validator::__set
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testSetBadConstraint()
    {
        $validator = new CountryValueValidator;
        $validator->unexisting = 0;
    }

    /**
     * Tests getting a wrong constraint
     *
     * @group fieldType
     * @group validator
     * @covers \ezp\Content\FieldType\Validator::__get
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testGetBadConstraint()
    {
        $validator = new CountryValueValidator;
        $null = $validator->unexisting;
    }

    /**
     * Tests validating a correct value
     *
     * @group fieldType
     * @group validator
     * @dataProvider providerForValidateOK
     * @covers \ezp\Content\FieldType\Country\CountryValueValidator::validate
     * @covers \ezp\Content\FieldType\Validator::getMessage
     */
    public function testValidateCorrectValues( $value )
    {
        $validator = new CountryValueValidator;
        $this->assertTrue( $validator->validate( new CountryValue( $value ) ) );
        $this->assertSame( array(), $validator->getMessage() );
    }

    public function providerForValidateOK()
    {
        return array(
            array( null ),
            array( "Belgium" ),
            array( array( "Belgium", "Norway", "France" ) ),
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
     * @group validator
     * @dataProvider providerForValidateKO
     * @covers \ezp\Content\FieldType\Country\CountryValueValidator::validate
     */
    public function testValidateWrongValues( $value )
    {
        $validator = new CountryValueValidator;
        $this->assertFalse( $validator->validate( new CountryValue( $value ) ) );
        $messages = $validator->getMessage();
        $this->assertStringEndsWith( "is not a valid country name.", $messages[0] );
    }

    public function providerForValidateKO()
    {
        return array(
            array( "Korea" ),
            array( array( "Belgium", "Norway", "France", "Korea" ) ),
        );
    }
}
