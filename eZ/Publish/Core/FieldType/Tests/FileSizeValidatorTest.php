<?php
/**
 * File containing the FileSizeValidatorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\BinaryFile\Value as BinaryFileValue;
use eZ\Publish\Core\FieldType\Validator\FileSizeValidator;
use eZ\Publish\API\Repository\Values\IO\BinaryFile;

/**
 * @group fieldType
 * @group validator
 */
class FileSizeValidatorTest extends FieldTypeTest
{
    /**
     * @return int
     */
    protected function getMaxFileSize()
    {
        return 4096;
    }

    /**
     * This test ensure an FileSizeValidator can be created
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\FieldType\\Validator",
            new FileSizeValidator
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
            "maxFileSize" => 4096,
        );
        $validator = new FileSizeValidator;
        $validator->initializeWithConstraints(
            $constraints
        );
        $this->assertSame( $constraints["maxFileSize"], $validator->maxFileSize );
    }

    /**
     * Test getting constraints schema
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::getConstraintsSchema
     */
    public function testGetConstraintsSchema()
    {
        $constraintsSchema = array(
            "maxFileSize" => array(
                "type" => "int",
                "default" => false
            )
        );
        $validator = new FileSizeValidator;
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
            "maxFileSize" => 4096,
        );
        $validator = new FileSizeValidator;
        $validator->maxFileSize = $constraints["maxFileSize"];
        $this->assertSame( $constraints["maxFileSize"], $validator->maxFileSize );
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
        $validator = new FileSizeValidator;
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
        $validator = new FileSizeValidator;
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
        $validator = new FileSizeValidator;
        $null = $validator->unexisting;
    }

    /**
     * Tests validating a correct value
     *
     * @param int $size
     *
     * @dataProvider providerForValidateOK
     * @covers \eZ\Publish\Core\FieldType\Validator\FileSizeValidator::validate
     * @covers \eZ\Publish\Core\FieldType\Validator::getMessage
     */
    public function testValidateCorrectValues( $size )
    {
        $this->markTestIncomplete( 'BinaryFile field type does not use this validator anymore.' );
        $validator = new FileSizeValidator;
        $validator->maxFileSize = 4096;
        $this->assertTrue( $validator->validate( $this->getBinaryFileValue( $size ) ) );
        $this->assertSame( array(), $validator->getMessage() );
    }

    /**
     * @param int $size
     *
     * @return \eZ\Publish\Core\FieldType\BinaryFile\Value
     */
    protected function getBinaryFileValue( $size )
    {
        $this->markTestIncomplete( 'BinaryFile field type does not use this validator anymore.' );
        $value = new BinaryFileValue( $this->getMock( 'eZ\\Publish\\API\\Repository\\IOService' ) );
        $value->file = new BinaryFile( array( "size" => $size ) );

        return $value;
    }

    public function providerForValidateOK()
    {
        return array(
            array( 0 ),
            array( 512 ),
            array( 4096 ),
        );
    }

    /**
     * Tests validating a wrong value
     *
     * @dataProvider providerForValidateKO
     * @covers \eZ\Publish\Core\FieldType\Validator\FileSizeValidator::validate
     */
    public function testValidateWrongValues( $size, $message, $values )
    {
        $this->markTestIncomplete( 'BinaryFile field type does not use this validator anymore.' );
        $validator = new FileSizeValidator;
        $validator->maxFileSize = $this->getMaxFileSize();
        $this->assertFalse( $validator->validate( $this->getBinaryFileValue( $size ) ) );
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
            $message[0],
            $messages[0]->getTranslatableMessage()->singular
        );
        $this->assertEquals(
            $message[1],
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
                8192,
                array(
                    "The file size cannot exceed %size% byte.",
                    "The file size cannot exceed %size% bytes."
                ),
                array( "size" => $this->getMaxFileSize() )
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
        $validator = new FileSizeValidator;

        $this->assertEmpty(
            $validator->validateConstraints( $constraints )
        );
    }

    public function providerForValidateConstraintsOK()
    {
        return array(
            array(
                array(),
                array( "maxFileSize" => false ),
                array( "maxFileSize" => 0 ),
                array( "maxFileSize" => -5 ),
                array( "maxFileSize" => 4096 ),
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
        $validator = new FileSizeValidator;
        $messages = $validator->validateConstraints( $constraints );

        $this->assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Translation\\Message",
            $messages[0]->getTranslatableMessage()
        );
        $this->assertEquals(
            $expectedMessages[0],
            $messages[0]->getTranslatableMessage()->message
        );
        $this->assertEquals(
            $values,
            $messages[0]->getTranslatableMessage()->values
        );
    }

    public function providerForValidateConstraintsKO()
    {
        return array(
            array(
                array( "maxFileSize" => true ),
                array( "Validator parameter '%parameter%' value must be of integer type" ),
                array( "parameter" => "maxFileSize" )
            ),
            array(
                array( "maxFileSize" => "five thousand bytes" ),
                array( "Validator parameter '%parameter%' value must be of integer type" ),
                array( "parameter" => "maxFileSize" )
            ),
            array(
                array( "maxFileSize" => new \DateTime() ),
                array( "Validator parameter '%parameter%' value must be of integer type" ),
                array( "parameter" => "maxFileSize" )
            ),
            array(
                array( "brljix" => 12345 ),
                array( "Validator parameter '%parameter%' is unknown" ),
                array( "parameter" => "brljix" )
            ),
        );
    }
}
