<?php
/**
 * File containing the UrlTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Factory,
    eZ\Publish\Core\Repository\FieldType\Url\Type as Url,
    eZ\Publish\Core\Repository\FieldType\Url\Value as UrlValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class UrlTest extends PHPUnit_Framework_TestCase
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
            "eZ\\Publish\\Core\\Repository\\FieldType\\Url\\Type",
            Factory::build( "ezurl" ),
            "Url object not returned for 'ezurl', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedValidators
     */
    public function testUrlSupportedValidators()
    {
        $ft = new Url();
        self::assertSame( array(), $ft->allowedValidators(), "The set of allowed validators does not match what is expected." );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Url\Type::acceptValue
     * @expectedException ezp\Base\Exception\InvalidArgumentValue
     * @group fieldType
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Url();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new UrlValue( 42 ) );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Url\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new Url();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );

        $value = new UrlValue( "http://ez.no/" );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Url\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $link = "http://ez.no/";
        $ft = new Url();
        $fieldValue = $ft->toPersistenceValue( new UrlValue( $link ) );

        self::assertSame( array( "link" => $link, "text" => null ), $fieldValue->data );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Url\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $link = "http://ez.no/";
        $value = new UrlValue( $link );
        self::assertSame( $link, $value->link );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Url\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $link = "http://ez.no/";
        $value = UrlValue::fromString( $link );
        self::assertInstanceOf( "eZ\\Publish\\Core\\Repository\\FieldType\\Url\\Value", $value );
        self::assertSame( $link, $value->link );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Url\Value::__toString
     */
    public function testFieldValueToString()
    {
        $link = "http://ez.no/";
        $value = UrlValue::fromString( $link );
        self::assertSame( $link, (string)$value );

        self::assertSame(
            $link,
            UrlValue::fromString( (string)$value )->link,
            "fromString() and __toString() must be compatible"
        );
    }
}
