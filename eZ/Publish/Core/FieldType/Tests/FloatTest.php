<?php
/**
 * File containing the FloatTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\Float\Type as Float,
    eZ\Publish\Core\FieldType\Float\Value as FloatValue,
    eZ\Publish\Core\FieldType\Tests\FieldTypeTest,
    ReflectionObject;

/**
 * @group fieldType
 * @group ezfloat
 */
class FloatTest extends FieldTypeTest
{
    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testValidatorConfigurationSchema()
    {
        $ft = new Float( $this->validatorService, $this->fieldTypeTools );
        self::assertSame(
            array(
                "FloatValueValidator" => array(
                    "minFloatValue" => array(
                        "type" => "float",
                        "default" => false
                    ),
                    "maxFloatValue" => array(
                        "type" => "float",
                        "default" => false
                    )
                )
            ),
            $ft->getValidatorConfigurationSchema(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getSettingsSchema
     */
    public function testCheckboxSettingsSchema()
    {
        $ft = new Float( $this->validatorService, $this->fieldTypeTools );
        self::assertSame(
            array(),
            $ft->getSettingsSchema(),
            "The settings schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Float\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Float( $this->validatorService, $this->fieldTypeTools );;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new FloatValue( "Strings should not work." ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Float\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new Float( $this->validatorService, $this->fieldTypeTools );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );

        $value = new FloatValue( 42.42 );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Float\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $ft = new Float( $this->validatorService, $this->fieldTypeTools );
        $fieldValue = $ft->toPersistenceValue( new FloatValue( 42.42 ) );

        self::assertSame( 42.42, $fieldValue->data );
        self::assertSame( false, $fieldValue->sortKey );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Float\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $value = new FloatValue( 420.42 );
        self::assertSame( 420.42, $value->value );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Float\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new FloatValue;
        self::assertSame( 0.0, $value->value );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Float\Value::__toString
     */
    public function testFieldValueToString()
    {
        $float = "4200.42";
        $value = new FloatValue( $float );
        self::assertSame( $float, (string)$value );

        $value2 = new FloatValue( (string)$value );
        self::assertSame(
            $float,
            $value2->value,
            "fromString() and __toString() must be compatible"
        );
    }
}
