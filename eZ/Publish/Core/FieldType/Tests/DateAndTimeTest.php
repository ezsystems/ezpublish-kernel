<?php
/**
 * File containing the DateAndTimeTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\DateAndTime\Type as DateAndTime,
    eZ\Publish\Core\FieldType\DateAndTime\Value as DateAndTimeValue,
    eZ\Publish\Core\FieldType\Tests\FieldTypeTest,
    ReflectionObject,
    DateTime;

/**
 * @group fieldType
 * @group ezdatetime
 */
class DateAndTimeTest extends FieldTypeTest
{
    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testValidatorConfigurationSchema()
    {
        $ft = new DateAndTime( $this->validatorService, $this->fieldTypeTools );
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
        $ft = new DateAndTime( $this->validatorService, $this->fieldTypeTools );
        self::assertSame(
            array(
                "useSeconds" => array(
                    "type" => "bool",
                    "default" => false
                ),
                "defaultType" => array(
                    "type" => "choice",
                    "default" => DateAndTime::DEFAULT_EMPTY
                ),
                "dateInterval" => array(
                    "type" => "date",
                    "default" => null
                )
            ),
            $ft->getSettingsSchema(),
            "The settings schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\DateAndTime\Type::getEmptyValue
     */
    public function testDefaultValue()
    {
        $ft = new DateAndTime( $this->validatorService, $this->fieldTypeTools );
        $value = $ft->getEmptyValue();
        self::assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\DateAndTime\\Value',
            $value
        );
        self::assertInstanceOf( 'DateTime', $value->value );
        self::assertLessThanOrEqual( 1, time() - $value->value->getTimestamp() );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\DateAndTime\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptInvalidValue()
    {
        $ft = new DateAndTime( $this->validatorService, $this->fieldTypeTools );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $this->getMock( 'eZ\\Publish\\Core\\FieldType\\Value' ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\DateAndTime\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new DateAndTime( $this->validatorService, $this->fieldTypeTools );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $invalidValue = new DateAndTimeValue;
        $invalidValue->value = 'This is not a DateTime object';
        $refMethod->invoke( $ft, $invalidValue );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\DateAndTime\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new DateAndTime( $this->validatorService, $this->fieldTypeTools );
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );

        $value = new DateAndTimeValue( new DateTime( '@1048633200' ) );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\DateAndTime\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $ft = new DateAndTime( $this->validatorService, $this->fieldTypeTools );
        $ts = 1048633200;
        $fieldValue = $ft->toPersistenceValue( new DateAndTimeValue( new DateTime( "@$ts" ) ) );

        self::assertSame(
            array(
                'timestamp' => 1048633200,
                'rfc850'    => 'Tuesday, 25-Mar-03 23:00:00 GMT+0000',
            ),
            $fieldValue->data
        );
        self::assertSame( $ts, $fieldValue->sortKey );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\DateAndTime\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $date = new DateTime( '@1048633200' );
        $value = new DateAndTimeValue( $date );
        self::assertSame( $date, $value->value );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\DateAndTime\Value::__construct
     */
    public function testBuildFieldValueWithStringParam()
    {
        $dateString = "@1048633200";
        $value = new DateAndTimeValue( $dateString );
        self::assertEquals( new DateTime( $dateString ), $value->value );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\DateAndTime\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new DateAndTimeValue;
        self::assertInstanceOf( 'DateTime', $value->value );
        self::assertLessThanOrEqual( 1, time() - $value->value->getTimestamp() );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\DateAndTime\Value::__toString
     */
    public function testFieldValueToString()
    {
        $timestamp = 1048633200;
        $fv = new DateAndTimeValue( "@$timestamp" );
        $fv->stringFormat = 'U';
        self::assertEquals( $timestamp, (string)$fv );
    }
}
