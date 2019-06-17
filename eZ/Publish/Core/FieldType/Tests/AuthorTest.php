<?php

/**
 * File containing the AuthorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\Author\Type as AuthorType;
use eZ\Publish\Core\FieldType\Author\Value as AuthorValue;
use eZ\Publish\Core\FieldType\Author\AuthorCollection;
use eZ\Publish\Core\FieldType\Author\Author;
use eZ\Publish\Core\FieldType\Value;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * @group fieldType
 * @group ezauthor
 */
class AuthorTest extends FieldTypeTest
{
    /**
     * @var \eZ\Publish\Core\FieldType\Author\Author[]
     */
    private $authors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authors = array(
            new Author(array('name' => 'Boba Fett', 'email' => 'boba.fett@bountyhunters.com')),
            new Author(array('name' => 'Darth Vader', 'email' => 'darth.vader@evilempire.biz')),
            new Author(array('name' => 'Luke Skywalker', 'email' => 'luke@imtheone.net')),
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
     * @return \eZ\Publish\SPI\FieldType\FieldType
     */
    protected function createFieldTypeUnderTest()
    {
        $fieldType = new AuthorType();
        $fieldType->setTransformationProcessor($this->getTransformationProcessorMock());

        return $fieldType;
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
        return array(
            'defaultAuthor' => array(
                'type' => 'choice',
                'default' => AuthorType::DEFAULT_VALUE_EMPTY,
            ),
        );
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
                InvalidArgumentException::class,
            ),
            array(
                23,
                InvalidArgumentException::class,
            ),
            array(
                array('foo'),
                InvalidArgumentException::class,
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
                new AuthorValue(array()),
            ),
            array(
                array(
                    new Author(array('name' => 'Boba Fett', 'email' => 'boba.fett@example.com')),
                ),
                new AuthorValue(
                    array(
                        new Author(array('id' => 1, 'name' => 'Boba Fett', 'email' => 'boba.fett@example.com')),
                    )
                ),
            ),
            array(
                array(
                    new Author(array('name' => 'Boba Fett', 'email' => 'boba.fett@example.com')),
                    new Author(array('name' => 'Darth Vader', 'email' => 'darth.vader@example.com')),
                ),
                new AuthorValue(
                    array(
                        new Author(array('id' => 1, 'name' => 'Boba Fett', 'email' => 'boba.fett@example.com')),
                        new Author(array('id' => 2, 'name' => 'Darth Vader', 'email' => 'darth.vader@example.com')),
                    )
                ),
            ),
        );
    }

    /**
     * Provide input for the toHash() method.
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
                new AuthorValue(array()),
                array(),
            ),
            array(
                new AuthorValue(
                    array(
                        new Author(array('id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com')),
                    )
                ),
                array(
                    array('id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com'),
                ),
            ),
            array(
                new AuthorValue(
                    array(
                        new Author(array('id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com')),
                        new Author(array('id' => 2, 'name' => 'Joe Bielefeld', 'email' => 'bielefeld@example.com')),
                    )
                ),
                array(
                    array('id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com'),
                    array('id' => 2, 'name' => 'Joe Bielefeld', 'email' => 'bielefeld@example.com'),
                ),
            ),
        );
    }

    /**
     * Provide input to fromHash() method.
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
                new AuthorValue(array()),
            ),
            array(
                array(
                    array('id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com'),
                ),
                new AuthorValue(
                    array(
                        new Author(array('id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com')),
                    )
                ),
            ),
            array(
                array(
                    array('id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com'),
                    array('id' => 2, 'name' => 'Joe Bielefeld', 'email' => 'bielefeld@example.com'),
                ),
                new AuthorValue(
                    array(
                        new Author(array('id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com')),
                        new Author(array('id' => 2, 'name' => 'Joe Bielefeld', 'email' => 'bielefeld@example.com')),
                    )
                ),
            ),
        );
    }

    /**
     * Provide data sets with field settings which are considered valid by the
     * {@link validateFieldSettings()} method.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of field settings.
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(),
     *      ),
     *      array(
     *          array( 'rows' => 2 )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidFieldSettings()
    {
        return array(
            array(
                array(),
            ),
            array(
                array(
                    'defaultAuthor' => AuthorType::DEFAULT_VALUE_EMPTY,
                ),
            ),
            array(
                array(
                    'defaultAuthor' => AuthorType::DEFAULT_CURRENT_USER,
                ),
            ),
        );
    }

    /**
     * Provide data sets with field settings which are considered invalid by the
     * {@link validateFieldSettings()} method. The method must return a
     * non-empty array of validation error when receiving such field settings.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of field settings.
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          true,
     *      ),
     *      array(
     *          array( 'nonExistentKey' => 2 )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInValidFieldSettings()
    {
        return array(
            array(
                array(
                    // non-existent setting
                    'useSeconds' => 23,
                ),
            ),
            array(
                array(
                    //defaultAuthor must be constant
                    'defaultAuthor' => 42,
                ),
            ),
        );
    }

    protected function tearDown(): void
    {
        unset($this->authors);
        parent::tearDown();
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testValidatorConfigurationSchema()
    {
        $ft = $this->createFieldTypeUnderTest();
        self::assertEmpty(
            $ft->getValidatorConfigurationSchema(),
            'The validator configuration schema does not match what is expected.'
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidType()
    {
        $ft = $this->createFieldTypeUnderTest();
        $ft->acceptValue($this->createMock(Value::class));
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = $this->createFieldTypeUnderTest();
        $value = new AuthorValue();
        $value->authors = 'This is not a valid author collection';
        $ft->acceptValue($value);
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = $this->createFieldTypeUnderTest();
        $author = new Author();
        $author->name = 'Boba Fett';
        $author->email = 'boba.fett@bountyhunters.com';
        $value = new AuthorValue(array($author));
        $newValue = $ft->acceptValue($value);
        self::assertSame($value, $newValue);
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new AuthorValue();
        self::assertInstanceOf(AuthorCollection::class, $value->authors);
        self::assertSame(array(), $value->authors->getArrayCopy());
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $value = new AuthorValue($this->authors);
        self::assertInstanceOf(AuthorCollection::class, $value->authors);
        self::assertSame($this->authors, $value->authors->getArrayCopy());
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Value::__toString
     */
    public function testFieldValueToString()
    {
        $value = new AuthorValue($this->authors);

        $authorsName = array();
        foreach ($this->authors as $author) {
            $authorsName[] = $author->name;
        }

        self::assertSame(implode(', ', $authorsName), $value->__toString());
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\AuthorCollection::offsetSet
     */
    public function testAddAuthor()
    {
        $value = new AuthorValue();
        $value->authors[] = $this->authors[0];
        self::assertSame(1, $this->authors[0]->id);
        self::assertSame(1, count($value->authors));

        $this->authors[1]->id = 10;
        $value->authors[] = $this->authors[1];
        self::assertSame(10, $this->authors[1]->id);

        $this->authors[2]->id = -1;
        $value->authors[] = $this->authors[2];
        self::assertSame($this->authors[1]->id + 1, $this->authors[2]->id);
        self::assertSame(3, count($value->authors));
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\AuthorCollection::removeAuthorsById
     */
    public function testRemoveAuthors()
    {
        $existingIds = array();
        foreach ($this->authors as $author) {
            $id = random_int(1, 100);
            if (in_array($id, $existingIds)) {
                continue;
            }
            $author->id = $id;
            $existingIds[] = $id;
        }

        $value = new AuthorValue($this->authors);
        $value->authors->removeAuthorsById(array($this->authors[1]->id, $this->authors[2]->id));
        self::assertSame(count($this->authors) - 2, count($value->authors));
        self::assertSame(array($this->authors[0]), $value->authors->getArrayCopy());
    }

    /**
     * Returns the identifier of the field type under test.
     *
     * @return string
     */
    protected function provideFieldTypeIdentifier()
    {
        return 'ezauthor';
    }

    /**
     * Provides data for the getName() test.
     *
     * @return array
     */
    public function provideDataForGetName(): array
    {
        $authorList = new AuthorValue(
            [
                new Author(['id' => 1, 'name' => 'Boba Fett', 'email' => 'boba.fett@example.com']),
                new Author(['id' => 2, 'name' => 'Luke Skywalker', 'email' => 'luke.skywalker@example.com']),
            ]
        );

        return [
            [$this->getEmptyValueExpectation(), [], 'en_GB', ''],
            [$authorList, [], 'en_GB', 'Boba Fett'],
        ];
    }
}
