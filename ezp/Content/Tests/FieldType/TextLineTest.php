<?php
/**
 * File containing the TextLineTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use PHPUnit_Framework_TestCase,
    ezp\Content\FieldType\Factory,
    ezp\Content\FieldType\TextLine\Type as TextLine,
    ezp\Content\FieldType\TextLine\Value as TextLineValue,
    ReflectionClass,
    ReflectionObject,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Persistence\Content\FieldValue,
    ezp\Content\Type\FieldDefinition,
    ezp\Content\FieldType\Validator\StringLengthValidator;

class TextLineTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test will make sure a correct mapping for the field type string has
     * been made.
     *
     * @group fieldType
     * @covers \ezp\Content\FieldType\Factory::build
     */
    public function testFactory()
    {
        self::assertInstanceOf(
            "ezp\\Content\\FieldType\\TextLine\\Type",
            Factory::build( "ezstring" ),
            "TextLine object not returned for 'ezstring', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::allowedValidators
     */
    public function testTextLineSupportedValidators()
    {
        $ft = new TextLine();
        self::assertSame( array( 'StringLengthValidator' ), $ft->allowedValidators(), "The set of allowed validators does not match what is expected." );
    }

    /**
     * @covers \ezp\Content\FieldType\TextLine\Type::canParseValue
     * @expectedException ezp\Base\Exception\BadFieldTypeInput
     * @group fieldType
     */
    public function testCanParseValueInvalidFormat()
    {
        $ft = new TextLine();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'canParseValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new TextLineValue( 42 ) );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\TextLine\Type::canParseValue
     */
    public function testCanParseValueValidFormat()
    {
        $ft = new TextLine();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'canParseValue' );
        $refMethod->setAccessible( true );

        $value = new TextLineValue( 'Strings works just fine.' );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\TextLine\Type::setFieldValue
     */
    public function testSetFieldValue()
    {
        $string = 'Test of FieldValue';
        $ft = new TextLine();
        $ft->setValue( new TextLineValue( $string ) );

        $fieldValue = new FieldValue();
        $ft->setFieldValue( $fieldValue );

        self::assertSame( array( 'value' => $string ), $fieldValue->data );
        self::assertNull( $fieldValue->externalData );
        self::assertSame( array( 'sort_key_string' => $string ), $fieldValue->sortKey );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\TextLine\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $text = 'According to developers, strings are good for women health.';
        $value = new TextLineValue( $text );
        self::assertSame( $text, $value->text );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\TextLine\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new TextLineValue;
        self::assertSame( '', $value->text );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\TextLine\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $string = "Most programmers don't wear strings. Most...";
        $value = TextLineValue::fromString( $string );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\TextLine\\Value', $value );
        self::assertSame( $string, $value->text );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\TextLine\Value::__toString
     */
    public function testFieldValueToString()
    {
        $string = "Believe it or not, but most geeks find strings very comfortable to work with";
        $value = TextLineValue::fromString( $string );
        self::assertSame( $string, (string)$value );

        self::assertSame(
            $string,
            TextLineValue::fromString( (string)$value )->text,
            'fromString() and __toString() must be compatible'
        );
    }
}
