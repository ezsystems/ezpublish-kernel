<?php

/**
 * File containing the RatingTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\Rating\Type as Rating;
use eZ\Publish\Core\FieldType\Rating\Value;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use ReflectionObject;

/**
 * @group fieldType
 * @group ezsrrating
 */
class RatingTest extends FieldTypeTest
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
        $fieldType = new Rating();
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
        array();
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
     * @return mixed
     */
    protected function getEmptyValueExpectation()
    {
        return new Value();
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
                'sindelfingen',
                InvalidArgumentException::class,
            ),
            array(
                array(),
                InvalidArgumentException::class,
            ),
            array(
                new Value('sindelfingen'),
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
                new Value(false),
            ),
            array(
                true,
                new Value(true),
            ),
            array(
                new Value(),
                new Value(false),
            ),
            array(
                new Value(true),
                new Value(true),
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
                new Value(true),
                true,
            ),
            array(
                new Value(false),
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
                new Value(true),
            ),
            array(
                false,
                new Value(false),
            ),
        );
    }

    public function testEmptyValueIsEmpty()
    {
        $this->markTestSkipped('Rating value is never empty');
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
     * @covers \eZ\Publish\Core\FieldType\FieldType::getSettingsSchema
     */
    public function testSettingsSchema()
    {
        $ft = $this->createFieldTypeUnderTest();
        self::assertEmpty(
            $ft->getSettingsSchema(),
            'The settings schema does not match what is expected.'
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Rating\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = $this->createFieldTypeUnderTest();
        $ref = new ReflectionObject($ft);
        $refMethod = $ref->getMethod('acceptValue');
        $refMethod->setAccessible(true);
        $ratingValue = new Value();
        $ratingValue->isDisabled = 'Strings should not work.';
        $refMethod->invoke($ft, $ratingValue);
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Rating\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = $this->createFieldTypeUnderTest();
        $ref = new ReflectionObject($ft);
        $refMethod = $ref->getMethod('acceptValue');
        $refMethod->setAccessible(true);

        $value = new Value(false);
        self::assertSame($value, $refMethod->invoke($ft, $value));
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Rating\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $rating = false;
        $ft = $this->createFieldTypeUnderTest();
        $fieldValue = $ft->toPersistenceValue($fv = new Value($rating));

        self::assertSame($rating, $fieldValue->data);
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Rating\Value::__construct
     */
    public function testBuildFieldValueWithParamFalse()
    {
        $value = new Value(false);
        self::assertFalse($value->isDisabled);
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Rating\Value::__construct
     */
    public function testBuildFieldValueWithParamTrue()
    {
        $value = new Value(true);
        self::assertTrue($value->isDisabled);
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Rating\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new Value();
        self::assertFalse($value->isDisabled);
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezsrrating';
    }

    /**
     * @dataProvider provideDataForGetName
     * @expectedException \RuntimeException
     */
    public function testGetName(SPIValue $value, array $fieldSettings = [], string $languageCode = 'en_GB', $expected)
    {
        $fieldSettingsMock = $this->getFieldDefinitionMock($fieldSettings);

        $this->getFieldTypeUnderTest()->getName($value, $fieldSettingsMock, $languageCode);
    }

    public function provideDataForGetName(): array
    {
        return [
            [$this->getEmptyValueExpectation(), [], 'en_GB', ''],
        ];
    }
}
