<?php
/**
 * File containing the FloatValueValidatorTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Float\Value as FloatValue,
    eZ\Publish\Core\Repository\FieldType\Float\FloatValueValidator,
    PHPUnit_Framework_TestCase;

class FloatValueValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test ensure an FloatValueValidator can be created
     *
     * @group fieldType
     * @group validator
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\FieldType\\Validator",
            new FloatValueValidator
        );
    }

    /**
     * Tests setting and getting constraints
     *
     * @group fieldType
     * @group validator
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::initializeWithConstraints
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::getValidatorConstraints
     */
    public function testInitializeConstraints()
    {
        $constraints = array(
            "minFloatValue" => 0.5,
            "maxFloatValue" => 22/7,
        );
        $validator = new FloatValueValidator;
        $validator->initializeWithConstraints(
            $constraints
        );
        $this->assertEquals( $constraints, $validator->getValidatorConstraints() );
    }

    /**
     * Tests setting and getting constraints
     *
     * @group fieldType
     * @group validator
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::initializeWithConstraints
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::__get
     */
    public function testConstraintsInitializeGet()
    {
        $constraints = array(
            "minFloatValue" => 0.5,
            "maxFloatValue" => 22/7,
        );
        $validator = new FloatValueValidator;
        $validator->initializeWithConstraints(
            $constraints
        );
        $this->assertSame( $constraints["minFloatValue"], $validator->minFloatValue );
        $this->assertSame( $constraints["maxFloatValue"], $validator->maxFloatValue );
    }

    /**
     * Tests setting and getting constraints
     *
     * @group fieldType
     * @group validator
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::__set
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::__get
     */
    public function testConstraintsSetGet()
    {
        $constraints = array(
            "minFloatValue" => 0.5,
            "maxFloatValue" => 22/7,
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
     * @group fieldType
     * @group validator
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::initializeWithConstraints
     * @expectedException \ezp\Base\Exception\PropertyNotFound
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
     * @group fieldType
     * @group validator
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::__set
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testSetBadConstraint()
    {
        $validator = new FloatValueValidator;
        $validator->unexisting = 0;
    }

    /**
     * Tests getting a wrong constraint
     *
     * @group fieldType
     * @group validator
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::__get
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testGetBadConstraint()
    {
        $validator = new FloatValueValidator;
        $null = $validator->unexisting;
    }

    /**
     * Tests validating a correct value
     *
     * @group fieldType
     * @group validator
     * @dataProvider providerForValidateOK
     * @covers \eZ\Publish\Core\Repository\FieldType\Float\FloatValueValidator::validate
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::getMessage
     */
    public function testValidateCorrectValues( $value )
    {
        $validator = new FloatValueValidator;
        $validator->minFloatValue = 10/7;
        $validator->maxFloatValue = 11/7;
        $this->assertTrue( $validator->validate( new FloatValue( $value ) ) );
        $this->assertSame( array(), $validator->getMessage() );
    }

    public function providerForValidateOK()
    {
        return array(
            array( 100/70 ),
            array( 101/70 ),
            array( 105/70 ),
            array( 109/70 ),
            array( 110/70 ),
        );
    }

    /**
     * Tests validating a wrong value
     *
     * @group fieldType
     * @group validator
     * @dataProvider providerForValidateKO
     * @covers \eZ\Publish\Core\Repository\FieldType\Float\FloatValueValidator::validate
     */
    public function testValidateWrongValues( $value, $message )
    {
        $validator = new FloatValueValidator;
        $validator->minFloatValue = 10/7;
        $validator->maxFloatValue = 11/7;
        $this->assertFalse( $validator->validate( new FloatValue( $value ) ) );
        $messages = $validator->getMessage();
        $this->assertStringStartsWith( $message, $messages[0] );
    }

    public function providerForValidateKO()
    {
        return array(
            array( -10/7, "The value can not be lower than" ),
            array( 0, "The value can not be lower than" ),
            array( 99/70, "The value can not be lower than" ),
            array( 111/70, "The value can not be higher than" ),
        );
    }
}
