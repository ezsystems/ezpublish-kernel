<?php
/**
 * File containing the RatingTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Factory,
    eZ\Publish\Core\Repository\FieldType\Rating\Type as Rating,
    eZ\Publish\Core\Repository\FieldType\Rating\Value as RatingValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class RatingTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test will make sure a correct mapping for the field type string has
     * been made.
     *
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Factory::build
     */
    public function testFactory()
    {
        self::assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\FieldType\\Rating\\Type",
            Factory::build( "ezsrrating" ),
            "Rating object not returned for 'ezsrrating', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedValidators
     */
    public function testRatingSupportedValidators()
    {
        $ft = new Rating();
        self::assertEmpty(
            $ft->allowedValidators(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Rating\Type::acceptValue
     * @expectedException ezp\Base\Exception\InvalidArgumentValue
     * @group fieldType
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Rating();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );
        $ratingValue = new RatingValue();
        $ratingValue->isDisabled = "Strings should not work.";
        $refMethod->invoke( $ft, $ratingValue );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Rating\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new Rating();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );

        $value = new RatingValue( false );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Rating\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $rating = false;
        $ft = new Rating();
        $fieldValue = $ft->toPersistenceValue( $fv = new RatingValue( $rating ) );

        self::assertSame( $rating, $fieldValue->data );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Rating\Value::__construct
     */
    public function testBuildFieldValueWithParamFalse()
    {
        $value = new RatingValue( false );
        self::assertSame( false, $value->isDisabled );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Rating\Value::__construct
     */
    public function testBuildFieldValueWithParamTrue()
    {
        $value = new RatingValue( true );
        self::assertSame( true, $value->isDisabled );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Rating\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new RatingValue;
        self::assertSame( false, $value->isDisabled );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Rating\Value::fromString
     */
    public function testBuildFieldValueFromStringFalse()
    {
        $rating = "0";
        $value = RatingValue::fromString( $rating );
        self::assertInstanceOf( "eZ\\Publish\\Core\\Repository\\FieldType\\Rating\\Value", $value );
        self::assertSame( false, $value->isDisabled );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Rating\Value::fromString
     */
    public function testBuildFieldValueFromStringTrue()
    {
        $rating = "1";
        $value = RatingValue::fromString( $rating );
        self::assertInstanceOf( "eZ\\Publish\\Core\\Repository\\FieldType\\Rating\\Value", $value );
        self::assertSame( true, $value->isDisabled );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Rating\Value::__toString
     */
    public function testFieldValueToStringFalse()
    {
        $rating = "0";
        $value = RatingValue::fromString( $rating );
        self::assertSame( $rating, (string)$value );

        self::assertSame(
            (bool)$rating,
            RatingValue::fromString( (string)$value )->isDisabled,
            "fromString() and __toString() must be compatible"
        );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Rating\Value::__toString
     */
    public function testFieldValueToStringTrue()
    {
        $rating = "1";
        $value = RatingValue::fromString( $rating );
        self::assertSame( $rating, (string)$value );

        self::assertSame(
            (bool)$rating,
            RatingValue::fromString( (string)$value )->isDisabled,
            "fromString() and __toString() must be compatible"
        );
    }
}
