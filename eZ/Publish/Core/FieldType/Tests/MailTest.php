<?php
/**
 * File containing the MailTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\Mail\Type as Mail,
    eZ\Publish\Core\FieldType\Mail\Value as MailValue,
    ReflectionObject;

/**
 * @group fieldType
 * @group ezemail
 */
class MailTest extends FieldTypeTest
{
    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testMailValidatorConfigurationSchema()
    {
        $ft = new Mail( $this->validatorService, $this->fieldTypeTools );;
        self::assertSame(
             array(
               "EMailAddressValidator" => array(
                    "Extent" => array(
                        "type" => "string",
                        "default" => "regex"
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
        $ft = new Mail( $this->validatorService, $this->fieldTypeTools );;
        self::assertEmpty(
            $ft->getSettingsSchema(),
            "The set of allowed settings does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Mail\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Mail( $this->validatorService, $this->fieldTypeTools );;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new MailValue( 123 ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Mail\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new Mail( $this->validatorService, $this->fieldTypeTools );;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );

        $value = new MailValue( 'A simple string works just fine.' );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Mail\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $string = 'info@ez.no';
        $ft = new Mail( $this->validatorService, $this->fieldTypeTools );;
        $fieldValue = $ft->toPersistenceValue( new MailValue( $string ) );

        self::assertSame( $string, $fieldValue->data );
        self::assertSame( $string, $fieldValue->sortKey );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Mail\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $text = 'info@ez.no';
        $value = new MailValue( $text );
        self::assertSame( $text, $value->email );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Mail\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new MailValue;
        self::assertSame( '', $value->email );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Mail\Value::__toString
     */
    public function testFieldValueToString()
    {
        $string = "info@ez.no";
        $value = new MailValue( $string );
        self::assertSame( $string, (string)$value );

        $value2 = new MailValue( (string)$value );
        self::assertSame(
            $string,
            $value2->email,
            'fromString() and __toString() must be compatible'
        );
    }
}
