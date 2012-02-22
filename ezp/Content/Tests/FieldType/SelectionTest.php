<?php
/**
 * File containing the SelectionTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Factory,
    eZ\Publish\Core\Repository\FieldType\Selection\Type as Selection,
    eZ\Publish\Core\Repository\FieldType\Selection\Value as SelectionValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class SelectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test will make sure a correct mapping for the field type string has
     * been made.
     *
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Repository\FieldType\Factory::build
     */
    public function testBuildFactory()
    {
        self::assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\FieldType\\Selection\\Type",
            Factory::build( "ezselection" ),
            "Selection object not returned for 'ezstring', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedValidators
     */
    public function testSelectionSupportedValidators()
    {
        $ft = new Selection();
        self::assertEmpty(
            $ft->allowedValidators(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Repository\FieldType\Selection\Type::acceptValue
     * @expectedException ezp\Base\Exception\InvalidArgumentValue
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Selection();
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
     * @covers \eZ\Publish\Core\Repository\FieldType\Selection\Type::acceptValue
     */
    public function testAcceptValueValidStringFormat()
    {
        $ft = new Selection();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );

        $value = new SelectionValue( "Choice1" );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Repository\FieldType\Selection\Type::acceptValue
     */
    public function testAcceptValueValidArrayFormat()
    {
        $ft = new Selection();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );

        $value = new SelectionValue( array( "Choice1", "Choice2" ) );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Repository\FieldType\Selection\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $string = "Choice1";
        $ft = new Selection();
        $fieldValue = $ft->toPersistenceValue( new SelectionValue( (array)$string ) );

        self::assertSame( array( $string ), $fieldValue->data );
        self::assertSame( array( "sort_key_string" => $string ), $fieldValue->sortKey );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Repository\FieldType\Selection\Value::__construct
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
     * @covers \eZ\Publish\Core\Repository\FieldType\Selection\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new SelectionValue;
        self::assertSame( array(), $value->selection );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Repository\FieldType\Selection\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $string = "Choice2";
        $value = SelectionValue::fromString( $string );
        self::assertInstanceOf( "eZ\\Publish\\Core\\Repository\\FieldType\\Selection\\Value", $value );
        self::assertSame( (array)$string, $value->selection );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Repository\FieldType\Selection\Value::__toString
     */
    public function testFieldValueToString()
    {
        $string = "Choice3";
        $value = SelectionValue::fromString( $string );
        self::assertSame( $string, (string)$value );

        self::assertSame(
            (array)$string,
            SelectionValue::fromString( (string)$value )->selection,
            "fromString() and __toString() must be compatible"
        );
    }
}
