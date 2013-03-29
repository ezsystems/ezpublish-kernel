<?php
/**
 * File containing the FloatValueValidatorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\Float\Value as FloatValue;
use eZ\Publish\Core\FieldType\Validator\FloatValueValidator;
use PHPUnit_Framework_TestCase;

/**
 * @group fieldType
 * @group validator
 */
class FloatValueValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return float
     */
    protected function getMinFloatValue()
    {
        return 10 / 7;
    }

    /**
     * @return float
     */
    protected function getMaxFloatValue()
    {
        return 11 / 7;
    }

    /**
     * This test ensure an FloatValueValidator can be created
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\FieldType\\Validator",
            new FloatValueValidator
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
            "minFloatValue" => 0.5,
            "maxFloatValue" => 22 / 7,
        );
        $validator = new FloatValueValidator;
        $validator->initializeWithConstraints(
            $constraints
        );
        $this->assertSame( $constraints["minFloatValue"], $validator->minFloatValue );
        $this->assertSame( $constraints["maxFloatValue"], $validator->maxFloatValue );
    }

    /**
     * Test getting constraints schema
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::getConstraintsSchema
     */
    public function testGetConstraintsSchema()
    {
        $constraintsSchema = array(
            "minFloatValue" => array(
                "type" => "float",
                "default" => false
            ),
            "maxFloatValue" => array(
                "type" => "float",
                "default" => false
            )
        );
        $validator = new FloatValueValidator;
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
            "minFloatValue" => 0.5,
            "maxFloatValue" => 22 / 7,
        );
        $validator = new FloatValueValidator;
        $validator->minFloatValue = $constraints["minFloatValue"];
        $validator->maxFloatValue = $constraints["maxFloatValue"];
        $this->assertSame( $constraints["minFloatValue"], $validator->minFloatValue );
        $this->assertSame( $constraints["maxFloatValue"], $validator->maxFloatValue );
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
        $validator = new FloatValueValidator;
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
        $validator = new FloatValueValidator;
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
        $validator = new FloatValueValidator;
        $null = $validator->unexisting;
    }

    /**
     * Tests validating a correct value
     *
     * @dataProvider providerForValidateOK
     * @covers \eZ\Publish\Core\FieldType\Validator\FloatValueValidator::validate
     * @covers \eZ\Publish\Core\FieldType\Validator::getMessage
     */
    public function testValidateCorrectValues( $value )
    {
        $validator = new FloatValueValidator;
        $validator->minFloatValue = 10 / 7;
        $validator->maxFloatValue = 11 / 7;
        $this->assertTrue( $validator->validate( new FloatValue( $value ) ) );
        $this->assertSame( array(), $validator->getMessage() );
    }

    public function providerForValidateOK()
    {
        return array(
            array( 100 / 70 ),
            array( 101 / 70 ),
            array( 105 / 70 ),
            array( 109 / 70 ),
            array( 110 / 70 ),
        );
    }

    /**
     * Tests validating a wrong value
     *
     * @dataProvider providerForValidateKO
     * @covers \eZ\Publish\Core\FieldType\Validator\FloatValueValidator::validate
     */
    public function testValidateWrongValues( $value, $message, $values )
    {
        $validator = new FloatValueValidator;
        $validator->minFloatValue = $this->getMinFloatValue();
        $validator->maxFloatValue = $this->getMaxFloatValue();
        $this->assertFalse( $validator->validate( new FloatValue( $value ) ) );
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
            array( -10 / 7, "The value can not be lower than %size%.", array( "size" => $this->getMinFloatValue() ) ),
            array( 0, "The value can not be lower than %size%.", array( "size" => $this->getMinFloatValue() ) ),
            array( 99 / 70, "The value can not be lower than %size%.", array( "size" => $this->getMinFloatValue() ) ),
            array( 111 / 70, "The value can not be higher than %size%.", array( "size" => $this->getMaxFloatValue() ) ),
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
        $validator = new FloatValueValidator;

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
                    "minFloatValue" => 5,
                ),
                array(
                    "maxFloatValue" => 2.2,
                ),
                array(
                    "minFloatValue" => false,
                    "maxFloatValue" => false
                ),
                array(
                    "minFloatValue" => -5,
                    "maxFloatValue" => false
                ),
                array(
                    "minFloatValue" => false,
                    "maxFloatValue" => 12.7
                ),
                array(
                    "minFloatValue" => 6,
                    "maxFloatValue" => 8.3
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
        $validator = new FloatValueValidator;
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
                    "minFloatValue" => true
                ),
                array( "Validator parameter '%parameter%' value must be of numeric type" ),
                array(
                    array( "parameter" => "minFloatValue" ),
                )
            ),
            array(
                array(
                    "minFloatValue" => "five thousand bytes"
                ),
                array( "Validator parameter '%parameter%' value must be of numeric type" ),
                array(
                    array( "parameter" => "minFloatValue" ),
                )
            ),
            array(
                array(
                    "minFloatValue" => "five thousand bytes",
                    "maxFloatValue" => 1234
                ),
                array( "Validator parameter '%parameter%' value must be of numeric type" ),
                array(
                    array( "parameter" => "minFloatValue" ),
                )
            ),
            array(
                array(
                    "maxFloatValue" => new \DateTime(),
                    "minFloatValue" => 1234
                ),
                array( "Validator parameter '%parameter%' value must be of numeric type" ),
                array(
                    array( "parameter" => "maxFloatValue" ),
                )
            ),
            array(
                array(
                    "minFloatValue" => true,
                    "maxFloatValue" => 1234
                ),
                array( "Validator parameter '%parameter%' value must be of numeric type" ),
                array(
                    array( "parameter" => "minFloatValue" ),
                )
            ),
            array(
                array(
                    "minFloatValue" => "five thousand bytes",
                    "maxFloatValue" => "ten billion bytes"
                ),
                array(
                    "Validator parameter '%parameter%' value must be of numeric type",
                    "Validator parameter '%parameter%' value must be of numeric type"
                ),
                array(
                    array( "parameter" => "minFloatValue" ),
                    array( "parameter" => "maxFloatValue" ),
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
                    "minFloatValue" => 12345,
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
