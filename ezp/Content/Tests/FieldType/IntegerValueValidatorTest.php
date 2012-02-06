<?php
/**
 * File containing the IntegerValueValidatorTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Integer\Value as IntegerValue,
    eZ\Publish\Core\Repository\FieldType\Integer\IntegerValueValidator,
    PHPUnit_Framework_TestCase;

class IntegerValueValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test ensure an IntegerValueValidator can be created
     *
     * @group fieldType
     * @group validator
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\FieldType\\Validator",
            new IntegerValueValidator
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
            "minIntegerValue" => 0,
            "maxIntegerValue" => 100,
        );
        $validator = new IntegerValueValidator;
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
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::__set
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::__get
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
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::initializeWithConstraints
     * @expectedException \ezp\Base\Exception\PropertyNotFound
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
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::__set
     * @expectedException \ezp\Base\Exception\PropertyNotFound
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
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::__get
     * @expectedException \ezp\Base\Exception\PropertyNotFound
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
     * @covers \eZ\Publish\Core\Repository\FieldType\Integer\IntegerValueValidator::validate
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::getMessage
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
     * @covers \eZ\Publish\Core\Repository\FieldType\Integer\IntegerValueValidator::validate
     */
    public function testValidateWrongValues( $value, $message )
    {
        $validator = new IntegerValueValidator;
        $validator->minIntegerValue = 10;
        $validator->maxIntegerValue = 15;
        $this->assertFalse( $validator->validate( new IntegerValue( $value ) ) );
        $this->assertSame( array( $message ), $validator->getMessage() );
    }

    public function providerForValidateKO()
    {
        return array(
            array( -12, "The value can not be lower than 10." ),
            array( 0, "The value can not be lower than 10." ),
            array( 9, "The value can not be lower than 10." ),
            array( 16, "The value can not be higher than 15." ),
        );
    }
}
