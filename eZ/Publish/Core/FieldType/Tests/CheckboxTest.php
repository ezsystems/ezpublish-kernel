<?php

/**
 * File containing the CheckboxTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\Checkbox\Type as Checkbox;
use eZ\Publish\Core\FieldType\Checkbox\Value as CheckboxValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * @group fieldType
 * @group ezboolean
 */
class CheckboxTest extends FieldTypeTest
{
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
        $fieldType = new Checkbox();
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
        return array();
    }

    /**
     * Returns the empty value expected from the field type.
     *
     * @return \eZ\Publish\Core\FieldType\Checkbox\Value
     */
    protected function getEmptyValueExpectation()
    {
        return new CheckboxValue(false);
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
                23,
                InvalidArgumentException::class,
            ),
            array(
                new CheckboxValue(42),
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
                false,
                new CheckboxValue(false),
            ),
            array(
                true,
                new CheckboxValue(true),
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
                new CheckboxValue(true),
                true,
            ),
            array(
                new CheckboxValue(false),
                false,
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
                true,
                new CheckboxValue(true),
            ),
            array(
                false,
                new CheckboxValue(false),
            ),
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Checkbox\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $ft = $this->createFieldTypeUnderTest();
        $fieldValue = $ft->toPersistenceValue(new CheckboxValue(true));

        self::assertTrue($fieldValue->data);
        self::assertSame(1, $fieldValue->sortKey);
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Checkbox\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $bool = true;
        $value = new CheckboxValue($bool);
        self::assertSame($bool, $value->bool);
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Checkbox\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new CheckboxValue();
        self::assertFalse($value->bool);
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Checkbox\Value::__toString
     */
    public function testFieldValueToString()
    {
        $valueTrue = new CheckboxValue(true);
        $valueFalse = new CheckboxValue(false);
        self::assertSame('1', (string)$valueTrue);
        self::assertSame('0', (string)$valueFalse);
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezboolean';
    }

    public function provideDataForGetName(): array
    {
        return [
            [new CheckboxValue(true), [], 'en_GB', '1'],
            [new CheckboxValue(false), [], 'en_GB', '0'],
        ];
    }
}
