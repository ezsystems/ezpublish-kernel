<?php
/**
 * File containing the StringLengthValidatorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;
use eZ\Publish\Core\FieldType\Validator\StringLengthValidator;

/**
 * @group fieldType
 * @group validator
 */
class StringLengthValidatorTest extends FieldTypeTest
{
    /**
     * @return int
     */
    protected function getMinStringLength()
    {
        return 5;
    }

    /**
     * @return int
     */
    protected function getMaxStringLength()
    {
        return 10;
    }

    /**
     * This test ensure an StringLengthValidator can be created
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\FieldType\\Validator",
            new StringLengthValidator
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
            "minStringLength" => 5,
            "maxStringLength" => 10,
        );
        $validator = new StringLengthValidator;
        $validator->initializeWithConstraints(
            $constraints
        );
        $this->assertSame( $constraints["minStringLength"], $validator->minStringLength );
        $this->assertSame( $constraints["maxStringLength"], $validator->maxStringLength );
    }

    /**
     * Test getting constraints schema
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::getConstraintsSchema
     */
    public function testGetConstraintsSchema()
    {
        $constraintsSchema = array(
            "minStringLength" => array(
                "type" => "int",
                "default" => 0
            ),
            "maxStringLength" => array(
                "type" => "int",
                "default" => null
            )
        );
        $validator = new StringLengthValidator;
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
            "minStringLength" => 5,
            "maxStringLength" => 10,
        );
        $validator = new StringLengthValidator;
        $validator->minStringLength = $constraints["minStringLength"];
        $validator->maxStringLength = $constraints["maxStringLength"];
        $this->assertSame( $constraints["minStringLength"], $validator->minStringLength );
        $this->assertSame( $constraints["maxStringLength"], $validator->maxStringLength );
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
        $validator = new StringLengthValidator;
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
        $validator = new StringLengthValidator;
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
        $validator = new StringLengthValidator;
        $null = $validator->unexisting;
    }

    /**
     * Tests validating a correct value
     *
     * @dataProvider providerForValidateOK
     * @covers \eZ\Publish\Core\FieldType\Validator\StringLengthValidator::validate
     * @covers \eZ\Publish\Core\FieldType\Validator::getMessage
     */
    public function testValidateCorrectValues( $value )
    {
        $validator = new StringLengthValidator;
        $validator->minStringLength = 5;
        $validator->maxStringLength = 10;
        $this->assertTrue( $validator->validate( new TextLineValue( $value ) ) );
        $this->assertSame( array(), $validator->getMessage() );
    }

    public function providerForValidateOK()
    {
        return array(
            array( "hello" ),
            array( "hello!" ),
            array( "0123456789" ),
        );
    }

    /**
     * Tests validating a wrong value
     *
     * @dataProvider providerForValidateKO
     * @covers \eZ\Publish\Core\FieldType\Validator\StringLengthValidator::validate
     */
    public function testValidateWrongValues( $value, $messageSingular, $messagePlural, $values )
    {
        $validator = new StringLengthValidator;
        $validator->minStringLength = $this->getMinStringLength();
        $validator->maxStringLength = $this->getMaxStringLength();
        $this->assertFalse( $validator->validate( new TextLineValue( $value ) ) );
        $messages = $validator->getMessage();
        $this->assertCount( 1, $messages );
        $this->assertInstanceOf(
            "eZ\\Publish\\SPI\\FieldType\\ValidationError",
            $messages[0]
        );
        $this->assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Translation\\Plural",
            $messages[0]->getTranslatableMessage()
        );
        $this->assertEquals(
            $messageSingular,
            $messages[0]->getTranslatableMessage()->singular
        );
        $this->assertEquals(
            $messagePlural,
            $messages[0]->getTranslatableMessage()->plural
        );
        $this->assertEquals(
            $values,
            $messages[0]->getTranslatableMessage()->values
        );
    }

    public function providerForValidateKO()
    {
        return array(
            array(
                "",
                "The string can not be shorter than %size% character.",
                "The string can not be shorter than %size% characters.",
                array( "size" => $this->getMinStringLength() )
            ),
            array(
                "Hi!",
                "The string can not be shorter than %size% character.",
                "The string can not be shorter than %size% characters.",
                array( "size" => $this->getMinStringLength() )
            ),
            array(
                "0123456789!",
                "The string can not exceed %size% character.",
                "The string can not exceed %size% characters.",
                array( "size" => $this->getMaxStringLength() )
            ),
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
        $validator = new StringLengthValidator;

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
                    "minStringLength" => 5,
                ),
                array(
                    "maxStringLength" => 2,
                ),
                array(
                    "minStringLength" => false,
                    "maxStringLength" => false
                ),
                array(
                    "minStringLength" => -5,
                    "maxStringLength" => false
                ),
                array(
                    "minStringLength" => false,
                    "maxStringLength" => 12
                ),
                array(
                    "minStringLength" => 6,
                    "maxStringLength" => 8
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
        $validator = new StringLengthValidator;
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
                    "minStringLength" => true
                ),
                array( "Validator parameter '%parameter%' value must be of integer type" ),
                array(
                    array( "parameter" => "minStringLength" ),
                )
            ),
            array(
                array(
                    "minStringLength" => "five thousand characters"
                ),
                array( "Validator parameter '%parameter%' value must be of integer type" ),
                array(
                    array( "parameter" => "minStringLength" ),
                )
            ),
            array(
                array(
                    "minStringLength" => "five thousand characters",
                    "maxStringLength" => 1234
                ),
                array( "Validator parameter '%parameter%' value must be of integer type" ),
                array(
                    array( "parameter" => "minStringLength" ),
                )
            ),
            array(
                array(
                    "maxStringLength" => new \DateTime(),
                    "minStringLength" => 1234
                ),
                array( "Validator parameter '%parameter%' value must be of integer type" ),
                array(
                    array( "parameter" => "maxStringLength" ),
                )
            ),
            array(
                array(
                    "minStringLength" => true,
                    "maxStringLength" => 1234
                ),
                array( "Validator parameter '%parameter%' value must be of integer type" ),
                array(
                    array( "parameter" => "minStringLength" ),
                )
            ),
            array(
                array(
                    "minStringLength" => "five thousand characters",
                    "maxStringLength" => "ten billion characters"
                ),
                array(
                    "Validator parameter '%parameter%' value must be of integer type",
                    "Validator parameter '%parameter%' value must be of integer type"
                ),
                array(
                    array( "parameter" => "minStringLength" ),
                    array( "parameter" => "maxStringLength" ),
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
                    "minStringLength" => 12345,
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
