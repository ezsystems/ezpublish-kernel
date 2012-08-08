<?php
/**
 * File containing the TextLineTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\TextLine\Type as TextLine,
    eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue,
    eZ\Publish\Core\FieldType\Tests\FieldTypeTest,
    ReflectionObject;

/**
 * @group fieldType
 * @group ezstring
 */
class TextLineTest extends FieldTypeTest
{
    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testTextLineValidatorConfigurationSchema()
    {
        $ft = new TextLine( $this->validatorService, $this->fieldTypeTools );;
        self::assertSame(
            array(
                "StringLengthValidator" => array(
                    "minStringLength" => array(
                        "type" => "int",
                        "default" => 0
                    ),
                    "maxStringLength" => array(
                        "type" => "int",
                        "default" => null
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
    public function testTextLineAllowedSettings()
    {
        $ft = new TextLine( $this->validatorService, $this->fieldTypeTools );;
        self::assertEmpty(
            $ft->getSettingsSchema(),
            "The set of allowed settings does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\TextLine\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new TextLine( $this->validatorService, $this->fieldTypeTools );;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new TextLineValue( 42 ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\TextLine\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new TextLine( $this->validatorService, $this->fieldTypeTools );;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );

        $value = new TextLineValue( 'Strings work just fine.' );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\TextLine\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $string = 'Test of FieldValue';
        $ft = new TextLine( $this->validatorService, $this->fieldTypeTools );;
        $fieldValue = $ft->toPersistenceValue( new TextLineValue( $string ) );

        self::assertSame( $string, $fieldValue->data );
        self::assertSame( $string, $fieldValue->sortKey );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\TextLine\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $text = 'According to developers, strings are good for women health.';
        $value = new TextLineValue( $text );
        self::assertSame( $text, $value->text );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\TextLine\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new TextLineValue;
        self::assertSame( '', $value->text );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\TextLine\Value::__toString
     */
    public function testFieldValueToString()
    {
        $string = "Believe it or not, but most geeks find strings very comfortable to work with";
        $value = new TextLineValue( $string );
        self::assertSame( $string, (string)$value );

        $value2 = new TextLineValue( (string)$value );
        self::assertSame(
            $string,
            $value2->text,
            'fromString() and __toString() must be compatible'
        );
    }
}
