<?php
/**
 * File containing the StringLengthValidatorTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\TextLine\Value as TextLineValue,
    eZ\Publish\Core\Repository\FieldType\TextLine\StringLengthValidator,
    PHPUnit_Framework_TestCase;

class StringLengthValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test ensure an StringLengthValidator can be created
     *
     * @group fieldType
     * @group validator
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\FieldType\\Validator",
            new StringLengthValidator
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
            "minStringLength" => 5,
            "maxStringLength" => 10,
        );
        $validator = new StringLengthValidator;
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
        $validator = new StringLengthValidator;
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
        $validator = new StringLengthValidator;
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
        $validator = new StringLengthValidator;
        $null = $validator->unexisting;
    }

    /**
     * Tests validating a correct value
     *
     * @group fieldType
     * @group validator
     * @dataProvider providerForValidateOK
     * @covers \eZ\Publish\Core\Repository\FieldType\TextLine\StringLengthValidator::validate
     * @covers \eZ\Publish\Core\Repository\FieldType\Validator::getMessage
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
     * @group fieldType
     * @group validator
     * @dataProvider providerForValidateKO
     * @covers \eZ\Publish\Core\Repository\FieldType\TextLine\StringLengthValidator::validate
     */
    public function testValidateWrongValues( $value, $message )
    {
        $validator = new StringLengthValidator;
        $validator->minStringLength = 5;
        $validator->maxStringLength = 10;
        $this->assertFalse( $validator->validate( new TextLineValue( $value ) ) );
        $messages = $validator->getMessage();
        $this->assertStringStartsWith( $message, $messages[0] );
    }

    public function providerForValidateKO()
    {
        return array(
            array( "", "The string can not be shorter than" ),
            array( "Hi!", "The string can not be shorter than" ),
            array( "0123456789!", "The string can not exceed" ),
        );
    }
}
