<?php
/**
 * File containing the DateAndTimeTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Factory,
    eZ\Publish\Core\Repository\FieldType\DateAndTime\Type as DateAndTime,
    eZ\Publish\Core\Repository\FieldType\DateAndTime\Value as DateAndTimeValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject,
    DateTime;

class DateAndTimeTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test will make sure a correct mapping for the field type string has
     * been made.
     *
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Repository\FieldType\Factory::build
     */
    public function testBuildFactory()
    {
        self::assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\FieldType\\DateAndTime\\Type",
            Factory::build( "ezdatetime" ),
            "DateAndTime object not returned for 'ezstring', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedValidators
     */
    public function testDateAndTimeSupportedValidators()
    {
        $ft = new DateAndTime();
        self::assertSame(
            array(),
            $ft->allowedValidators(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedSettings
     */
    public function testDateAndTimeAllowedSettings()
    {
        $ft = new DateAndTime();
        self::assertSame(
            array( 'useSeconds', 'defaultType', 'dateInterval' ),
            $ft->allowedSettings(),
            "The set of allowed settings does not match what is expected."
        );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Repository\FieldType\DateAndTime\Type::getDefaultValue
     */
    public function testDefaultValue()
    {
        $ft = new DateAndTime();
        $value = $ft->getDefaultValue();
        self::assertInstanceOf(
            'eZ\\Publish\\Core\\Repository\\FieldType\\DateAndTime\\Value',
            $value
        );
        self::assertInstanceOf( 'DateTime', $value->value );
        self::assertLessThanOrEqual( 1, time() - $value->value->getTimestamp() );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\DateAndTime\Type::acceptValue
     * @expectedException ezp\Base\Exception\InvalidArgumentType
     * @group fieldType
     * @group dateTime
     */
    public function testAcceptInvalidValue()
    {
        $ft = new DateAndTime();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\Value' ) );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\DateAndTime\Type::acceptValue
     * @expectedException ezp\Base\Exception\InvalidArgumentValue
     * @group fieldType
     * @group dateTime
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new DateAndTime();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $invalidValue = new DateAndTimeValue;
        $invalidValue->value = 'This is not a DateTime object';
        $refMethod->invoke( $ft, $invalidValue );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Repository\FieldType\DateAndTime\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new DateAndTime();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );

        $value = new DateAndTimeValue( new DateTime( '@1048633200' ) );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Repository\FieldType\DateAndTime\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $ft = new DateAndTime();
        $ts = 1048633200;
        $fieldValue = $ft->toPersistenceValue( new DateAndTimeValue( new DateTime( "@$ts" ) ) );

        self::assertSame( $ts, $fieldValue->data );
        self::assertSame( array( 'sort_key_int' => $ts ), $fieldValue->sortKey );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Repository\FieldType\DateAndTime\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $date = new DateTime( '@1048633200' );
        $value = new DateAndTimeValue( $date );
        self::assertSame( $date, $value->value );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Repository\FieldType\DateAndTime\Value::__construct
     */
    public function testBuildFieldValueWithStringParam()
    {
        $dateString = "@1048633200";
        $value = new DateAndTimeValue( $dateString );
        self::assertEquals( new DateTime( $dateString ), $value->value );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Repository\FieldType\DateAndTime\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new DateAndTimeValue;
        self::assertInstanceOf( 'DateTime', $value->value );
        self::assertLessThanOrEqual( 1, time() - $value->value->getTimestamp() );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Repository\FieldType\DateAndTime\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $timestamp = 1048633200;
        $fv = DateAndTimeValue::fromString( "@$timestamp" );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Repository\\FieldType\\DateAndTime\\Value', $fv );
        self::assertSame( $timestamp, $fv->value->getTimestamp() );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @expectedException \ezp\Base\Exception\InvalidArgumentValue
     * @covers \eZ\Publish\Core\Repository\FieldType\DateAndTime\Value::fromString
     */
    public function testBuildFieldValueFromInvalidString()
    {
        $fv = DateAndTimeValue::fromString( "This is not a valid date string" );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Repository\FieldType\DateAndTime\Value::__toString
     */
    public function testFieldValueToString()
    {
        $timestamp = 1048633200;
        $fv = DateAndTimeValue::fromString( "@$timestamp" );
        $fv->stringFormat = 'U';
        self::assertEquals( $timestamp, (string)$fv );
    }
}
