<?php
/**
 * File containing the eZ\Publish\Core\FieldType\Tests\FieldTypeTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

abstract class StandardizedFieldTypeTest extends FieldTypeTest
{
    /**
     * Generic cache for the getFieldTypeUnderTest() method.
     *
     * @var FieldType
     */
    private $fieldTypeUnderTest;

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
    abstract protected function createFieldTypeUnderTest();

    /**
     * Returns the validator configuration schema expected from the field type.
     *
     * @return array
     */
    abstract protected function getValidatorConfigurationSchemaExpectation();

    /**
     * Returns the settings schema expected from the field type.
     *
     * @return array
     */
    abstract protected function getSettingsSchemaExpectation();

    /**
     * Returns the empty value expected from the field type.
     *
     * @return mixed
     */
    abstract protected function getEmptyValueExpectation();

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
    abstract public function provideInvalidInputForAcceptValue();

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
    abstract public function provideValidInputForAcceptValue();

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
    abstract public function provideInputForToHash();

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
    abstract public function provideInputForFromHash();

    /**
     * Retrieves a test wide cached version of the field type under test.
     *
     * Uses {@link createFieldTypeUnderTest()} to create the instance
     * initially.
     *
     * @return \eZ\Publish\SPI\FieldType\FieldType
     */
    protected function getFieldTypeUnderTest()
    {
        if ( !isset( $this->fieldTypeUnderTest ) )
        {
            $this->fieldTypeUnderTest = $this->createFieldTypeUnderTest();
        }
        return $this->fieldTypeUnderTest;
    }

    public function testValidatorConfigurationSchema()
    {
        $fieldType = $this->getFieldTypeUnderTest();

        self::assertSame(
            $this->getValidatorConfigurationSchemaExpectation(),
            $fieldType->getValidatorConfigurationSchema(),
            'Validator configuration schema not returned correctly.'
        );
    }

    public function testSettingsSchema()
    {
        $fieldType = $this->getFieldTypeUnderTest();

        self::assertSame(
            $this->getSettingsSchemaExpectation(),
            $fieldType->getSettingsSchema(),
            'Settings schema not returned correctly.'
        );
    }

    public function testEmptyValue()
    {
        $fieldType = $this->getFieldTypeUnderTest();

        $this->assertEquals(
            $this->getEmptyValueExpectation(),
            $fieldType->getEmptyValue()
        );
    }

    /**
     * @param mixed $inputValue
     * @param mixed $expectedOutputValue
     * @dataProvider provideValidInputForAcceptValue
     */
    public function testAcceptValue( $inputValue, $expectedOutputValue )
    {
        $fieldType = $this->getFieldTypeUnderTest();

        $outputValue = $fieldType->acceptValue( $inputValue );

        $this->assertEquals(
            $expectedOutputValue,
            $outputValue,
            'acceptValue() did not convert properly.'
        );
    }

    /**
     * @param mixed $inputValue
     * @param Exception $expectedException
     * @dataProvider provideInvalidInputForAcceptValue
     */
    public function testAcceptValueFailsOnInvalidValues( $inputValue, $expectedException )
    {
        $fieldType = $this->getFieldTypeUnderTest();

        try
        {
            $fieldType->acceptValue( $inputValue );
            $this->fail(
                sprintf(
                    'Expected exception of type "%s" not thrown on incorrect input to acceptValue().',
                    $expectedException
                )
            );
        }
        catch ( \Exception $e )
        {
            if ( $e instanceof \PHPUnit_Framework_Exception
                 || $e instanceof \PHPUnit_Framework_Error
                 || $e instanceof \PHPUnit_Framework_AssertionFailedError )
            {
                throw $e;
            }

            $this->assertInstanceOf(
                $expectedException,
                $e
            );
        }
    }

    /**
     * @param mixed $inputValue
     * @param array $expectedResult
     * @dataProvider provideInputForToHash
     */
    public function testToHash( $inputValue, $expectedResult )
    {
        $fieldType = $this->getFieldTypeUnderTest();

        $actualResult = $fieldType->toHash( $inputValue );

        $this->assertEquals(
            $expectedResult,
            $actualResult,
            'toHash() method did not create expected result.'
        );
    }

    /**
     * @param mixed $inputValue
     * @param array $expectedResult
     * @dataProvider provideInputForFromHash
     */
    public function testFromHash( $inputHash, $expectedResult )
    {
        $fieldType = $this->getFieldTypeUnderTest();

        $actualResult = $fieldType->fromHash( $inputHash );

        $this->assertEquals(
            $expectedResult,
            $actualResult,
            'fromHash() method did not create expected result.'
        );
    }

    public function testEmptyValueIsEmpty()
    {
        $fieldType = $this->getFieldTypeUnderTest();

        $this->assertTrue(
            $fieldType->isEmptyValue( $fieldType->getEmptyValue() )
        );
    }

    // @TODO: More test methods …
}
