<?php
/**
 * File containing the IntegerValueValidatorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\Integer\Value as IntegerValue;
use eZ\Publish\Core\FieldType\Validator\IntegerValueValidator;
use PHPUnit_Framework_TestCase;

/**
 * @group fieldType
 * @group validator
 */
class IntegerValueValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return int
     */
    protected function getMinIntegerValue()
    {
        return 10;
    }

    /**
     * @return int
     */
    protected function getMaxIntegerValue()
    {
        return 15;
    }

    /**
     * This test ensure an IntegerValueValidator can be created
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\FieldType\\Validator",
            new IntegerValueValidator
        );
    }

    /**
     * Tests setting and getting constraints
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::initializeWithConstraints
     * @covers \eZ\Publish\Core\FieldType\Validator::__get
     */
    public function testConstraintsInitializeGet()
    {
        $constraints = array(
            "minIntegerValue" => 0,
            "maxIntegerValue" => 100,
        );
        $validator = new IntegerValueValidator;
        $validator->initializeWithConstraints(
            $constraints
        );
        $this->assertSame( $constraints["minIntegerValue"], $validator->minIntegerValue );
        $this->assertSame( $constraints["maxIntegerValue"], $validator->maxIntegerValue );
    }

    /**
     * Test getting constraints schema
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::getConstraintsSchema
     */
    public function testGetConstraintsSchema()
    {
        $constraintsSchema = array(
            "minIntegerValue" => array(
                "type" => "int",
                "default" => 0
            ),
            "maxIntegerValue" => array(
                "type" => "int",
                "default" => false
            )
        );
        $validator = new IntegerValueValidator;
        $this->assertSame( $constraintsSchema, $validator->getConstraintsSchema() );
    }

    /**
     * Tests setting and getting constraints
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::__set
     * @covers \eZ\Publish\Core\FieldType\Validator::__get
     */
    public function testConstraintsSetGet()
    {
        $constraints = array(
            "minIntegerValue" => 0,
            "maxIntegerValue" => 100,
        );
        $validator = new IntegerValueValidator;
        $validator->minIntegerValue = $constraints["minIntegerValue"];
        $validator->maxIntegerValue = $constraints["maxIntegerValue"];
        $this->assertSame( $constraints["minIntegerValue"], $validator->minIntegerValue );
        $this->assertSame( $constraints["maxIntegerValue"], $validator->maxIntegerValue );
    }

    /**
     * Tests initializing with a wrong constraint
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::initializeWithConstraints
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testInitializeBadConstraint()
    {
        $constraints = array(
            "unexisting" => 0,
        );
        $validator = new IntegerValueValidator;
        $validator->initializeWithConstraints(
            $constraints
        );
    }

    /**
     * Tests setting a wrong constraint
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::__set
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testSetBadConstraint()
    {
        $validator = new IntegerValueValidator;
        $validator->unexisting = 0;
    }

    /**
     * Tests getting a wrong constraint
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::__get
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testGetBadConstraint()
    {
        $validator = new IntegerValueValidator;
        $null = $validator->unexisting;
    }

    /**
     * Tests validating a correct value
     *
     * @dataProvider providerForValidateOK
     * @covers \eZ\Publish\Core\FieldType\Validator\IntegerValueValidator::validate
     * @covers \eZ\Publish\Core\FieldType\Validator::getMessage
     */
    public function testValidateCorrectValues( $value )
    {
        $validator = new IntegerValueValidator;
        $validator->minIntegerValue = 10;
        $validator->maxIntegerValue = 15;
        $this->assertTrue( $validator->validate( new IntegerValue( $value ) ) );
        $this->assertSame( array(), $validator->getMessage() );
    }

    public function providerForValidateOK()
    {
        return array(
            array( 10 ),
            array( 11 ),
            array( 12 ),
            array( 12.5 ),
            array( 13 ),
            array( 14 ),
            array( 15 ),
        );
    }

    /**
     * Tests validating a wrong value
     *
     * @dataProvider providerForValidateKO
     * @covers \eZ\Publish\Core\FieldType\Validator\IntegerValueValidator::validate
     */
    public function testValidateWrongValues( $value, $message, $values )
    {
        $validator = new IntegerValueValidator;
        $validator->minIntegerValue = $this->getMinIntegerValue();
        $validator->maxIntegerValue = $this->getMaxIntegerValue();
        $this->assertFalse( $validator->validate( new IntegerValue( $value ) ) );
        $messages = $validator->getMessage();
        $this->assertCount( 1, $messages );
        $this->assertInstanceOf(
            "eZ\\Publish\\SPI\\FieldType\\ValidationError",
            $messages[0]
        );
        $this->assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Translation\\Message",
            $messages[0]->getTranslatableMessage()
        );
        $this->assertEquals(
            $message,
            $messages[0]->getTranslatableMessage()->message
        );
        $this->assertEquals(
            $values,
            $messages[0]->getTranslatableMessage()->values
        );
    }

    public function providerForValidateKO()
    {
        return array(
            array( -12, "The value can not be lower than %size%.", array( "size" => $this->getMinIntegerValue() ) ),
            array( 0, "The value can not be lower than %size%.", array( "size" => $this->getMinIntegerValue() ) ),
            array( 9, "The value can not be lower than %size%.", array( "size" => $this->getMinIntegerValue() ) ),
            array( 16, "The value can not be higher than %size%.", array( "size" => $this->getMaxIntegerValue() ) ),
        );
    }

    /**
     * Tests validation of constraints
     *
     * @dataProvider providerForValidateConstraintsOK
     * @covers \eZ\Publish\Core\FieldType\Validator\FileSizeValidator::validateConstraints
     */
    public function testValidateConstraintsCorrectValues( $constraints )
    {
        $validator = new IntegerValueValidator;

        $this->assertEmpty(
            $validator->validateConstraints( $constraints )
        );
    }

    public function providerForValidateConstraintsOK()
    {
        return array(
            array(
                array(),
                array(
                    "minIntegerValue" => 5,
                ),
                array(
                    "maxIntegerValue" => 2,
                ),
                array(
                    "minIntegerValue" => false,
                    "maxIntegerValue" => false
                ),
                array(
                    "minIntegerValue" => -5,
                    "maxIntegerValue" => false
                ),
                array(
                    "minIntegerValue" => false,
                    "maxIntegerValue" => 12
                ),
                array(
                    "minIntegerValue" => 6,
                    "maxIntegerValue" => 8
                ),
            ),
        );
    }

    /**
     * Tests validation of constraints
     *
     * @dataProvider providerForValidateConstraintsKO
     * @covers \eZ\Publish\Core\FieldType\Validator\FileSizeValidator::validateConstraints
     */
    public function testValidateConstraintsWrongValues( $constraints, $expectedMessages, $values )
    {
        $validator = new IntegerValueValidator;
        $messages = $validator->validateConstraints( $constraints );

        foreach ( $expectedMessages as $index => $expectedMessage )
        {
            $this->assertInstanceOf(
                "eZ\\Publish\\API\\Repository\\Values\\Translation\\Message",
                $messages[0]->getTranslatableMessage()
            );
            $this->assertEquals(
                $expectedMessage,
                $messages[$index]->getTranslatableMessage()->message
            );
            $this->assertEquals(
                $values[$index],
                $messages[$index]->getTranslatableMessage()->values
            );
        }
    }

    public function providerForValidateConstraintsKO()
    {
        return array(
            array(
                array(
                    "minIntegerValue" => true
                ),
                array( "Validator parameter '%parameter%' value must be of integer type" ),
                array(
                    array( "parameter" => "minIntegerValue" ),
                )
            ),
            array(
                array(
                    "minIntegerValue" => "five thousand bytes"
                ),
                array( "Validator parameter '%parameter%' value must be of integer type" ),
                array(
                    array( "parameter" => "minIntegerValue" ),
                )
            ),
            array(
                array(
                    "minIntegerValue" => "five thousand bytes",
                    "maxIntegerValue" => 1234
                ),
                array( "Validator parameter '%parameter%' value must be of integer type" ),
                array(
                    array( "parameter" => "minIntegerValue" ),
                )
            ),
            array(
                array(
                    "maxIntegerValue" => new \DateTime(),
                    "minIntegerValue" => 1234
                ),
                array( "Validator parameter '%parameter%' value must be of integer type" ),
                array(
                    array( "parameter" => "maxIntegerValue" ),
                )
            ),
            array(
                array(
                    "minIntegerValue" => true,
                    "maxIntegerValue" => 1234
                ),
                array( "Validator parameter '%parameter%' value must be of integer type" ),
                array(
                    array( "parameter" => "minIntegerValue" ),
                )
            ),
            array(
                array(
                    "minIntegerValue" => "five thousand bytes",
                    "maxIntegerValue" => "ten billion bytes"
                ),
                array(
                    "Validator parameter '%parameter%' value must be of integer type",
                    "Validator parameter '%parameter%' value must be of integer type"
                ),
                array(
                    array( "parameter" => "minIntegerValue" ),
                    array( "parameter" => "maxIntegerValue" ),
                )
            ),
            array(
                array(
                    "brljix" => 12345
                ),
                array( "Validator parameter '%parameter%' is unknown" ),
                array(
                    array( "parameter" => "brljix" ),
                )
            ),
            array(
                array(
                    "minIntegerValue" => 12345,
                    "brljix" => 12345
                ),
                array( "Validator parameter '%parameter%' is unknown" ),
                array(
                    array( "parameter" => "brljix" ),
                )
            ),
        );
    }
}
