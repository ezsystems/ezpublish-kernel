<?php
/**
 * File containing the AuthorTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use ezp\Content\FieldType\Factory,
    ezp\Content\FieldType\Author\Type as AuthorType,
    ezp\Content\FieldType\Author\Value as AuthorValue,
    ezp\Content\FieldType\Author\Author,
    ezp\Content\FieldType\Author\AuthorCollection,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

/**
 * @group fieldType
 * @group ezauthor
 */
class AuthorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Content\FieldType\Author\Author[]
     */
    private $authors;

    protected function setUp()
    {
        parent::setUp();
        $this->authors = array(
            new Author( array( 'name' => 'Boba Fett', 'email' => 'boba.fett@bountyhunters.com' ) ),
            new Author( array( 'name' => 'Darth Vader', 'email' => 'darth.vader@evilempire.biz' ) ),
            new Author( array( 'name' => 'Luke Skywalker', 'email' => 'luke@imtheone.net' ) )
        );
    }

    protected function tearDown()
    {
        unset( $this->authors );
        parent::tearDown();
    }

    /**
     * This test will make sure a correct mapping for the field type string has
     * been made.
     *
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
     * @covers \ezp\Content\FieldType::allowedValidators
     */
    public function testAuthorSupportedValidators()
    {
        $ft = new AuthorType;
        self::assertSame( array(), $ft->allowedValidators(), "The set of allowed validators does not match what is expected." );
    }

    /**
     * @covers \ezp\Content\FieldType\Author\Type::canParseValue
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     */
    public function testCanParseValueInvalidType()
    {
        $ft = new AuthorType;
        $ft->setValue( $this->getMock( 'ezp\\Content\\FieldType\\Value' ) );
    }

    /**
     * @covers \ezp\Content\FieldType\Author\Type::canParseValue
     * @expectedException \ezp\Base\Exception\BadFieldTypeInput
     */
    public function testCanParseValueInvalidFormat()
    {
        $ft = new AuthorType;
        $value = new AuthorValue;
        $value->authors = 'This is not a valid author collection';
        $ft->setValue( $value );
    }

    /**
     * @covers \ezp\Content\FieldType\Author\Type::canParseValue
     */
    public function testCanParseValueValidFormat()
    {
        $ft = new AuthorType;
        $author = new Author;
        $author->name = 'Boba Fett';
        $author->email = 'boba.fett@bountyhunters.com';
        $value = new AuthorValue( array( $author ) );
        $ft->setValue( $value );
        self::assertSame( $value, $ft->getValue() );
    }

    /**
     * @covers \ezp\Content\FieldType\Author\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new AuthorValue;
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\Author\\AuthorCollection', $value->authors );
        self::assertSame( array(), $value->authors->getArrayCopy() );
    }

    /**
     * @covers \ezp\Content\FieldType\Author\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $value = new AuthorValue( $this->authors );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\Author\\AuthorCollection', $value->authors );
        self::assertSame( $this->authors, $value->authors->getArrayCopy() );
    }


    /**
     * @covers \ezp\Content\FieldType\Author\Value::fromString
     * @expectedException \ezp\Base\Exception\Logic
     */
    public function testBuildFieldValueFromString()
    {
        $value = AuthorValue::fromString( 'This is not gonna work' );
    }

    /**
     * @covers \ezp\Content\FieldType\Author\Value::__toString
     */
    public function testFieldValueToString()
    {
        $value = new AuthorValue( $this->authors );

        $authorsName = array();
        foreach ( $this->authors as $author )
        {
            $authorsName[] = $author->name;
        }

        self::assertSame( implode( ', ', $authorsName ), $value->__toString() );
    }

    /**
     * @covers \ezp\Content\FieldType\Author\Value::getTitle
     */
    public function testFieldValueTitle()
    {
        $value = new AuthorValue( $this->authors );
        self::assertSame( $this->authors[0]->name , $value->getTitle() );
    }

    /**
     * @covers \ezp\Content\FieldType\Author\AuthorCollection::offsetSet
     */
    public function testAddAuthor()
    {
        $value = new AuthorValue;
        $value->authors[] = $this->authors[0];
        self::assertSame( 1, $this->authors[0]->id );
        self::assertSame( 1, count( $value->authors ) );

        $this->authors[1]->id = 10;
        $value->authors[] = $this->authors[1];
        self::assertSame( 10, $this->authors[1]->id );

        $this->authors[2]->id = -1;
        $value->authors[] = $this->authors[2];
        self::assertSame( $this->authors[1]->id + 1, $this->authors[2]->id );
        self::assertSame( 3, count( $value->authors ) );
    }

    public function testRemoveAuthors()
    {
        $existingIds = array();
        foreach ( $this->authors as $author )
        {
            $id = mt_rand( 1, 100 );
            if ( in_array( $id, $existingIds ) )
                $id++;
            $author->id = $id;
        }

        $value = new AuthorValue( $this->authors );
        $value->authors->removeAuthorsById( array( $this->authors[1]->id, $this->authors[2]->id ) );
        self::assertSame( count( $this->authors ) - 2, count( $value->authors ) );
        self::assertSame( array( $this->authors[0] ), $value->authors->getArrayCopy() );
    }
}
