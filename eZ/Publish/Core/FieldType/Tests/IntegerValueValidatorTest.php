<?php
/**
 * File containing the IntegerValueValidatorTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\Integer\Value as IntegerValue,
    eZ\Publish\Core\FieldType\Validator\IntegerValueValidator,
    eZ\Publish\Core\Repository\Tests\FieldType;

class IntegerValueValidatorTest extends FieldTypeTest
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
     *
     * @group fieldType
     * @group validator
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
     * @group fieldType
     * @group validator
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
     * Tests setting and getting constraints
     *
     * @group fieldType
     * @group validator
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
     * @group fieldType
     * @group validator
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
     * @group fieldType
     * @group validator
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
     * @group fieldType
     * @group validator
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
     * @group fieldType
     * @group validator
     * @dataProvider providerForValidateOK
     * @covers \eZ\Publish\Core\FieldType\Integer\IntegerValueValidator::validate
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
     * @group fieldType
     * @group validator
     * @dataProvider providerForValidateKO
     * @covers \eZ\Publish\Core\FieldType\Integer\IntegerValueValidator::validate
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
}
