<?php
/**
 * File containing the SelectionTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\Selection\Type as Selection,
    eZ\Publish\Core\FieldType\Selection\Value as SelectionValue,
    eZ\Publish\Core\FieldType\Tests\FieldTypeTest,
    ReflectionObject;

class SelectionTest extends FieldTypeTest
{
    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testSelectionSupportedValidators()
    {
        $ft = new Selection( $this->validatorService, $this->fieldTypeTools );;
        self::assertEmpty(
            $ft->getValidatorConfigurationSchema(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\FieldType\Selection\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Selection( $this->validatorService, $this->fieldTypeTools );;
        $invalidValue = new SelectionValue;
        $invalidValue->selection = "This should be an array instead!";
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $invalidValue );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\FieldType\Selection\Type::acceptValue
     */
    public function testAcceptValueValidStringFormat()
    {
        $ft = new Selection( $this->validatorService, $this->fieldTypeTools );;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );

        $value = new SelectionValue( "Choice1" );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\FieldType\Selection\Type::acceptValue
     */
    public function testAcceptValueValidArrayFormat()
    {
        $ft = new Selection( $this->validatorService, $this->fieldTypeTools );;
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );

        $value = new SelectionValue( array( "Choice1", "Choice2" ) );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\FieldType\Selection\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $string = "Choice1";
        $ft = new Selection( $this->validatorService, $this->fieldTypeTools );;
        $fieldValue = $ft->toPersistenceValue( new SelectionValue( (array)$string ) );

        self::assertSame( array( $string ), $fieldValue->data );
        self::assertSame( array( "sort_key_string" => $string ), $fieldValue->sortKey );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\FieldType\Selection\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $selection = array( "Choice1", "Choice2", "Choice3" );
        $value = new SelectionValue( $selection );
        self::assertSame( $selection, $value->selection );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\FieldType\Selection\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new SelectionValue;
        self::assertSame( array(), $value->selection );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\FieldType\Selection\Value::__toString
     */
    public function testFieldValueToString()
    {
        $string = "Choice3";
        $value = new SelectionValue( $string );
        self::assertSame( $string, (string)$value );

        $value2 = new SelectionValue( (string)$value );
        self::assertSame(
            (array)$string,
            $value2->selection,
            "fromString() and __toString() must be compatible"
        );
    }
}
