<?php
/**
 * File containing the IntegerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\Integer\Type as Integer,
    eZ\Publish\Core\FieldType\Integer\Value as IntegerValue,
    eZ\Publish\Core\FieldType\Tests\FieldTypeTest,
    ReflectionObject;

/**
 * @group fieldType
 * @group ezinteger
 */
class IntegerTest extends FieldTypeTest
{
    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testValidatorConfigurationSchema()
    {
        $ft = new Integer( $this->validatorService, $this->fieldTypeTools );
        self::assertSame(
            array(
                "IntegerValueValidator" => array(
                    "minIntegerValue" => array(
                        "type" => "int",
                        "default" => 0
                    ),
                    "maxIntegerValue" => array(
                        "type" => "int",
                        "default" => false
                    )
                )
            ),
            $ft->getValidatorConfigurationSchema(),
            "The validator configuration schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getSettingsSchema
     */
    public function testSettingsSchema()
    {
        $ft = new Integer( $this->validatorService, $this->fieldTypeTools );
        self::assertEmpty(
            $ft->getSettingsSchema(),
            "The settings schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Integer\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Integer( $this->validatorService, $this->fieldTypeTools );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new IntegerValue( "Strings should not work." ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Integer\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new Integer( $this->validatorService, $this->fieldTypeTools );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );

        $value = new IntegerValue( 42 );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Integer\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $integer = 42;
        $ft = new Integer( $this->validatorService, $this->fieldTypeTools );
        $fieldValue = $ft->toPersistenceValue( new IntegerValue( $integer ) );

        self::assertSame( $integer, $fieldValue->data );
        self::assertSame( $integer, $fieldValue->sortKey );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Integer\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $value = new IntegerValue( 420 );
        self::assertSame( 420, $value->value );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Integer\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new IntegerValue;
        self::assertSame( 0, $value->value );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Integer\Value::__toString
     */
    public function testFieldValueToString()
    {
        $integer = "4200";
        $value = new IntegerValue( $integer );
        self::assertSame( $integer, (string)$value );

        $value2 = new IntegerValue( (string)$value );
        self::assertSame(
            $integer,
            $value2->value,
            "fromString() and __toString() must be compatible"
        );
    }
}
