<?php
/**
 * File containing the AuthorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\Author\Type as AuthorType;
use eZ\Publish\Core\FieldType\Author\Value as AuthorValue;
use eZ\Publish\Core\FieldType\Author\Author;

/**
 * @group fieldType
 * @group ezauthor
 */
class AuthorTest extends StandardizedFieldTypeTest
{
    /**
     * @var \eZ\Publish\Core\FieldType\Author\Author[]
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

    /**
     * Returns the field type under test.
     *
     * This method is used by all test cases to retrieve the field type under
     * test. Just create the FieldType instance using mocks from the provided
     * get*Mock() methods and/or custom get*Mock() implementations. You MUST
     * NOT take care for test case wide caching of the field type, just return
     * a new instance from this method!
     *
     * @return FieldType
     */
    protected function createFieldTypeUnderTest()
    {
        return new AuthorType();
    }

    /**
     * Returns the validator configuration schema expected from the field type.
     *
     * @return array
     */
    protected function getValidatorConfigurationSchemaExpectation()
    {
        return array();
    }

    /**
     * Returns the settings schema expected from the field type.
     *
     * @return array
     */
    protected function getSettingsSchemaExpectation()
    {
        return array();
    }

    /**
     * Returns the empty value expected from the field type.
     *
     * @return \eZ\Publish\Core\FieldType\Author\Value
     */
    protected function getEmptyValueExpectation()
    {
        return new AuthorValue();
    }

    /**
     * Data provider for invalid input to acceptValue().
     *
     * Returns an array of data provider sets with 2 arguments: 1. The invalid
     * input to acceptValue(), 2. The expected exception type as a string. For
     * example:
     *
     * <code>
     *  return array(
     *      array(
     *          new \stdClass(),
     *          'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
     *      ),
     *      array(
     *          array(),
     *          'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInvalidInputForAcceptValue()
    {
        return array(
            array(
                'My name',
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                23,
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                array( 'foo' ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
        );
    }

    /**
     * Data provider for valid input to acceptValue().
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to acceptValue(), 2. The expected return value from acceptValue().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          __FILE__,
     *          new BinaryFileValue( array(
     *              'path' => __FILE__,
     *              'fileName' => basename( __FILE__ ),
     *              'fileSize' => filesize( __FILE__ ),
     *              'downloadCount' => 0,
     *              'mimeType' => 'text/plain',
     *          ) )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidInputForAcceptValue()
    {
        return array(
            array(
                array(),
                new AuthorValue( array() ),
            ),
            array(
                array(
                    new Author( array( 'name' => 'Boba Fett', 'email' => 'boba.fett@example.com' ) )
                ),
                new AuthorValue(
                    array(
                        new Author( array( 'id' => 1, 'name' => 'Boba Fett', 'email' => 'boba.fett@example.com' ) )
                    )
                ),
            ),
            array(
                array(
                    new Author( array( 'name' => 'Boba Fett', 'email' => 'boba.fett@example.com' ) ),
                    new Author( array( 'name' => 'Darth Vader', 'email' => 'darth.vader@example.com' ) ),
                ),
                new AuthorValue(
                    array(
                        new Author( array( 'id' => 1, 'name' => 'Boba Fett', 'email' => 'boba.fett@example.com' ) ),
                        new Author( array( 'id' => 2, 'name' => 'Darth Vader', 'email' => 'darth.vader@example.com' ) ),
                    )
                ),
            )
        );
    }

    /**
     * Provide input for the toHash() method
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to toHash(), 2. The expected return value from toHash().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          new BinaryFileValue( array(
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ) ),
     *          array(
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInputForToHash()
    {
        return array(
            array(
                new AuthorValue( array() ),
                array(),
            ),
            array(
                new AuthorValue(
                    array(
                        new Author( array( 'id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com' ) ),
                    )
                ),
                array(
                    array( 'id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com' ),
                ),
            ),
            array(
                new AuthorValue(
                    array(
                        new Author( array( 'id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com' ) ),
                        new Author( array( 'id' => 2, 'name' => 'Joe Bielefeld', 'email' => 'bielefeld@example.com' ) ),
                    )
                ),
                array(
                    array( 'id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com' ),
                    array( 'id' => 2, 'name' => 'Joe Bielefeld', 'email' => 'bielefeld@example.com' ),
                ),
            ),
        );
    }

    /**
     * Provide input to fromHash() method
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to fromHash(), 2. The expected return value from fromHash().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          array(
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ),
     *          new BinaryFileValue( array(
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ) )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInputForFromHash()
    {
        return array(
            array(
                array(),
                new AuthorValue( array() ),
            ),
            array(
                array(
                    array( 'id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com' ),
                ),
                new AuthorValue(
                    array(
                        new Author( array( 'id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com' ) ),
                    )
                ),
            ),
            array(
                array(
                    array( 'id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com' ),
                    array( 'id' => 2, 'name' => 'Joe Bielefeld', 'email' => 'bielefeld@example.com' ),
                ),
                new AuthorValue(
                    array(
                        new Author( array( 'id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com' ) ),
                        new Author( array( 'id' => 2, 'name' => 'Joe Bielefeld', 'email' => 'bielefeld@example.com' ) ),
                    )
                ),
            ),
        );
    }

    protected function tearDown()
    {
        unset( $this->authors );
        parent::tearDown();
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testValidatorConfigurationSchema()
    {
        $ft = new AuthorType();
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
        $ft = new AuthorType();
        self::assertEmpty(
            $ft->getSettingsSchema(),
            "The settings schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidType()
    {
        $ft = new AuthorType();
        $ft->acceptValue( $this->getMock( 'eZ\\Publish\\Core\\FieldType\\Value' ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new AuthorType();
        $value = new AuthorValue;
        $value->authors = 'This is not a valid author collection';
        $ft->acceptValue( $value );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new AuthorType();
        $author = new Author;
        $author->name = 'Boba Fett';
        $author->email = 'boba.fett@bountyhunters.com';
        $value = new AuthorValue( array( $author ) );
        $newValue = $ft->acceptValue( $value );
        self::assertSame( $value, $newValue );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new AuthorValue;
        self::assertInstanceOf( 'eZ\\Publish\\Core\\FieldType\\Author\\AuthorCollection', $value->authors );
        self::assertSame( array(), $value->authors->getArrayCopy() );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $value = new AuthorValue( $this->authors );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\FieldType\\Author\\AuthorCollection', $value->authors );
        self::assertSame( $this->authors, $value->authors->getArrayCopy() );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Value::__toString
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
     * @covers \eZ\Publish\Core\FieldType\Author\Type::getName
     */
    public function testFieldValueTitle()
    {
        $ft = new AuthorType();
        $value = new AuthorValue( $this->authors );
        self::assertSame( $this->authors[0]->name, $ft->getName( $value ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\AuthorCollection::offsetSet
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

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\AuthorCollection::removeAuthorsById
     */
    public function testRemoveAuthors()
    {
        $existingIds = array();
        foreach ( $this->authors as $author )
        {
            $id = mt_rand( 1, 100 );
            if ( in_array( $id, $existingIds ) )
                continue;
            $author->id = $id;
            $existingIds[] = $id;
        }

        $value = new AuthorValue( $this->authors );
        $value->authors->removeAuthorsById( array( $this->authors[1]->id, $this->authors[2]->id ) );
        self::assertSame( count( $this->authors ) - 2, count( $value->authors ) );
        self::assertSame( array( $this->authors[0] ), $value->authors->getArrayCopy() );
    }
}
