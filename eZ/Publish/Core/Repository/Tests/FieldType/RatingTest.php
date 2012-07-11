<?php
/**
 * File containing the RatingTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType;
use eZ\Publish\Core\FieldType\Rating\Type as Rating,
    eZ\Publish\Core\FieldType\Rating\Value as RatingValue,
    eZ\Publish\Core\Repository\Tests\FieldType,
    ReflectionObject;

class RatingTest extends FieldType
{
    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testRatingSupportedValidators()
    {
        $ft = new Rating( $this->validatorService, $this->fieldTypeTools );;
        self::assertEmpty(
            $ft->getValidatorConfigurationSchema(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Rating\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @group fieldType
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Rating( $this->validatorService, $this->fieldTypeTools );;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );
        $ratingValue = new RatingValue();
        $ratingValue->isDisabled = "Strings should not work.";
        $refMethod->invoke( $ft, $ratingValue );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Rating\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new Rating( $this->validatorService, $this->fieldTypeTools );;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );

        $value = new RatingValue( false );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Rating\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $rating = false;
        $ft = new Rating( $this->validatorService, $this->fieldTypeTools );;
        $fieldValue = $ft->toPersistenceValue( $fv = new RatingValue( $rating ) );

        self::assertSame( $rating, $fieldValue->data );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Rating\Value::__construct
     */
    public function testBuildFieldValueWithParamFalse()
    {
        $value = new RatingValue( false );
        self::assertSame( false, $value->isDisabled );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Rating\Value::__construct
     */
    public function testBuildFieldValueWithParamTrue()
    {
        $value = new RatingValue( true );
        self::assertSame( true, $value->isDisabled );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Rating\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new RatingValue;
        self::assertSame( false, $value->isDisabled );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Rating\Value::__toString
     */
    public function testFieldValueToStringFalse()
    {
        $rating = "0";
        $value = new RatingValue( $rating );
        self::assertSame( $rating, (string)$value );

        $value2 = new RatingValue( (string)$value );
        self::assertSame(
            (bool)$rating,
            $value2->isDisabled,
            "fromString() and __toString() must be compatible"
        );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\FieldType\Rating\Value::__toString
     */
    public function testFieldValueToStringTrue()
    {
        $rating = "1";
        $value = new RatingValue( $rating );
        self::assertSame( $rating, (string)$value );

        $value2 = new RatingValue( (string)$value );
        self::assertSame(
            (bool)$rating,
            $value2->isDisabled,
            "fromString() and __toString() must be compatible"
        );
    }
}
