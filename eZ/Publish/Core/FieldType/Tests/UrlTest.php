<?php
/**
 * File containing the UrlTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\Url\Type as Url,
    eZ\Publish\Core\FieldType\Url\Value as UrlValue,
    eZ\Publish\Core\FieldType\Tests\FieldTypeTest,
    ReflectionObject;

/**
 * @group fieldType
 * @group ezurl
 */
class UrlTest extends FieldTypeTest
{
    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testValidatorConfigurationSchema()
    {
        $ft = new Url( $this->validatorService, $this->fieldTypeTools );
        self::assertEmpty(
            $ft->getValidatorConfigurationSchema(),
            "The validator configuration schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getSettingsSchema
     */
    public function testSettingsSchema()
    {
        $ft = new Url( $this->validatorService, $this->fieldTypeTools );
        self::assertEmpty(
            $ft->getSettingsSchema(),
            "The settings schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Url\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Url( $this->validatorService, $this->fieldTypeTools );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new UrlValue( 42 ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Url\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new Url( $this->validatorService, $this->fieldTypeTools );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );

        $value = new UrlValue( "http://ez.no/" );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Url\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $link = "http://ez.no/";
        $ft = new Url( $this->validatorService, $this->fieldTypeTools );
        $fieldValue = $ft->toPersistenceValue( new UrlValue( $link ) );

        self::assertSame( array( "urlId" => null, "text" => null ), $fieldValue->data );
        self::assertSame( $link, $fieldValue->externalData );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Url\Type::fromPersistenceValue
     */
    public function testFromPersistenceValue()
    {
        $this->markTestIncomplete( "Test for \\eZ\\Publish\\Core\\FieldType\\Url\\Type::fromPersistenceValue() is not implemented." );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Url\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $link = "http://ez.no/";
        $value = new UrlValue( $link );
        self::assertSame( $link, $value->link );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Url\Value::__toString
     */
    public function testFieldValueToString()
    {
        $link = "http://ez.no/";
        $value = new UrlValue( $link );
        self::assertSame( $link, (string)$value );

        $value2 = new UrlValue( (string)$value );
        self::assertSame(
            $link,
            $value2->link,
            "fromString() and __toString() must be compatible"
        );
    }
}
