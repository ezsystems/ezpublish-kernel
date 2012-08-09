<?php
/**
 * File containing the CheckboxTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\Checkbox\Type as Checkbox,
    eZ\Publish\Core\FieldType\Checkbox\Value as CheckboxValue,
    ReflectionObject;

/**
 * @group fieldType
 * @group ezboolean
 */
class CheckboxTest extends FieldTypeTest
{
    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testValidatorConfigurationSchema()
    {
        $ft = new Checkbox( $this->validatorService, $this->fieldTypeTools );
        self::assertSame(
            array(),
            $ft->getValidatorConfigurationSchema(),
            "The validator configuration schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getSettingsSchema
     */
    public function testSettingsSchema()
    {
        $ft = new Checkbox( $this->validatorService, $this->fieldTypeTools );
        self::assertSame(
            array(),
            $ft->getSettingsSchema(),
            "The settings schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Checkbox\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Checkbox( $this->validatorService, $this->fieldTypeTools );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new CheckboxValue( 'I am definitely not a boolean' ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Checkbox\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidValue()
    {
        $ft = new Checkbox( $this->validatorService, $this->fieldTypeTools );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $this->getMock( 'eZ\\Publish\\Core\\FieldType\\Value' ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Checkbox\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new Checkbox( $this->validatorService, $this->fieldTypeTools );;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );

        $value = new CheckboxValue( true );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Checkbox\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $ft = new Checkbox( $this->validatorService, $this->fieldTypeTools );;
        $fieldValue = $ft->toPersistenceValue( new CheckboxValue( true ) );

        self::assertSame( true, $fieldValue->data );
        self::assertSame( 1, $fieldValue->sortKey );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Checkbox\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $bool = true;
        $value = new CheckboxValue( $bool );
        self::assertSame( $bool, $value->bool );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Checkbox\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new CheckboxValue;
        self::assertSame( false, $value->bool );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Checkbox\Value::__toString
     */
    public function testFieldValueToString()
    {
        $valueTrue = new CheckboxValue( true );
        $valueFalse = new CheckboxValue( false );
        self::assertSame( '1', (string)$valueTrue );
        self::assertSame( '0', (string)$valueFalse );
    }
}
