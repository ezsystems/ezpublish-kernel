<?php
/**
 * File containing the AuthorTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use ezp\Content\FieldType\Factory,
    ezp\Content\FieldType\Author\Type as Author,
    ezp\Content\FieldType\Author\Value as AuthorValue,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Persistence\Content\FieldValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class AuthorTest extends PHPUnit_Framework_TestCase
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
            "ezp\\Content\\FieldType\\Author\\Type",
            Factory::build( "ezauthor" ),
            "Author object not returned for 'ezauthor', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::allowedValidators
     */
    public function testAuthorSupportedValidators()
    {
        $ft = new Author();
        self::assertSame( array(), $ft->allowedValidators(), "The set of allowed validators does not match what is expected." );
    }

    /**
     * @covers \ezp\Content\FieldType\Author\Type::canParseValue
     * @expectedException ezp\Base\Exception\BadFieldTypeInput
     * @group fieldType
     */
    public function testCanParseValueInvalidFormat()
    {
        $this->markTestIncomplete( "@TODO: implement this test" );
        $ft = new Author();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new AuthorValue( /* Some param */ ) );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Author\Type::canParseValue
     */
    public function testCanParseValueValidFormat()
    {
        $this->markTestIncomplete( "@TODO: implement this test" );
        $ft = new Author();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );

        $value = new AuthorValue( /* Some param */ );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Author\Type::toFieldValue
     */
    public function testToFieldValue()
    {
        $this->markTestIncomplete( "@TODO: implement this test" );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Author\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $this->markTestIncomplete( "@TODO: implement this test" );
        $value = new AuthorValue( /* Some param */ );
        self::assertSame( /* some value */ null, $value->value );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Author\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $this->markTestIncomplete( "@TODO: implement this test" );
        $value = new AuthorValue;
        self::assertSame( array(), $value->value );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Author\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $this->markTestIncomplete( "@TODO: implement this test" );
        // String representation for authors
        $authors = null;
        $value = AuthorValue::fromString( $authors );
        self::assertInstanceOf( "ezp\\Content\\FieldType\\Author\\Value", $value );
        self::assertSame( $authors, $value->value );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Author\Value::__toString
     */
    public function testFieldValueToString()
    {
        $this->markTestIncomplete( "@TODO: implement this test" );
        // String representation for authors
        $authors = null;
        $authors = "4200";
        $value = AuthorValue::fromString( $authors );
        self::assertSame( $authors, (string)$value );

        self::assertSame(
            $authors,
            AuthorValue::fromString( (string)$value )->value,
            "fromString() and __toString() must be compatible"
        );
    }
}
