<?php

/**
 * File containing the DateAndTimeTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\DateAndTime\Type as DateAndTime;
use eZ\Publish\Core\FieldType\DateAndTime\Value as DateAndTimeValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use DateInterval;
use stdClass;

/**
 * @group fieldType
 * @group ezdatetime
 */
class DateAndTimeTest extends FieldTypeTest
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
     * @return FieldType
     */
    protected function createFieldTypeUnderTest()
    {
        $fieldType = new DateAndTime();
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
            'useSeconds' => array(
                'type' => 'bool',
                'default' => false,
            ),
            'defaultType' => array(
                'type' => 'choice',
                'default' => DateAndTime::DEFAULT_EMPTY,
            ),
            'dateInterval' => array(
                'type' => 'dateInterval',
                'default' => null,
            ),
        );
    }

    /**
     * Returns the empty value expected from the field type.
     */
    protected function getEmptyValueExpectation()
    {
        return new DateAndTimeValue();
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
                array(),
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
                null,
                new DateAndTimeValue(),
            ),
            array(
                '2012-08-28 12:20 Europe/Berlin',
                DateAndTimeValue::fromString('2012-08-28 12:20 Europe/Berlin'),
            ),
            array(
                1346149200,
                DateAndTimeValue::fromTimestamp(1346149200),
            ),
            array(
                ($dateTime = new \DateTime()),
                new DateAndTimeValue($dateTime),
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
                new DateAndTimeValue(),
                null,
            ),
            array(
                new DateAndTimeValue($date = new \DateTime('Tue, 28 Aug 2012 12:20:00 +0200')),
                array(
                    'rfc850' => $date->format(\DateTime::RFC850),
                    'timestamp' => $date->getTimeStamp(),
                ),
            ),
        );
    }

    /**
     * @param mixed $inputValue
     * @param array $expectedResult
     *
     * @dataProvider provideInputForFromHash
     */
    public function testFromHash($inputHash, $expectedResult)
    {
        $this->assertIsValidHashValue($inputHash);

        $fieldType = $this->getFieldTypeUnderTest();

        $actualResult = $fieldType->fromHash($inputHash);

        // Tests may run slowly. Allow 20 seconds margin of error.
        $this->assertGreaterThanOrEqual(
            $expectedResult,
            $actualResult,
            'fromHash() method did not create expected result.'
        );
        if ($expectedResult->value !== null) {
            $this->assertLessThan(
                $expectedResult->value->add(new DateInterval('PT20S')),
                $actualResult->value,
                'fromHash() method did not create expected result.'
            );
        }
    }

    /**
     * Provide input to fromHash() method.
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to fromHash(), 2. The expected return value from fromHash().
     *
     * @return array
     */
    public function provideInputForFromHash()
    {
        $date = new \DateTime('Tue, 28 Aug 2012 12:20:00 +0200');

        return array(
            array(
                null,
                new DateAndTimeValue(),
            ),
            array(
                array(
                    'rfc850' => $date->format(\DateTime::RFC850),
                    'timestamp' => $date->getTimeStamp(),
                ),
                new DateAndTimeValue($date),
            ),
            array(
                array(
                    'timestamp' => $date->getTimeStamp(),
                ),
                DateAndTimeValue::fromTimestamp($date->getTimeStamp()),
            ),
        );
    }

    /**
     * @param mixed $inputValue
     * @param string $intervalSpec
     *
     * @dataProvider provideInputForTimeStringFromHash
     */
    public function testTimeStringFromHash($inputHash, $intervalSpec)
    {
        $this->assertIsValidHashValue($inputHash);

        $fieldType = $this->getFieldTypeUnderTest();

        $expectedResult = new DateAndTimeValue(new \DateTime());
        $expectedResult->value->add(new DateInterval($intervalSpec));

        $actualResult = $fieldType->fromHash($inputHash);

        // Tests may run slowly. Allow 20 seconds margin of error.
        $this->assertGreaterThanOrEqual(
            $expectedResult,
            $actualResult,
            'fromHash() method did not create expected result.'
        );
        if ($expectedResult->value !== null) {
            $this->assertLessThan(
                $expectedResult->value->add(new DateInterval('PT20S')),
                $actualResult->value,
                'fromHash() method did not create expected result.'
            );
        }
    }

    /**
     * Provide input to testTimeStringFromHash() method.
     *
     * Returns an array of data provider sets with 2 arguments: 1. A valid
     * timestring input to fromHash(), 2. An interval specification string,
     * from which can be created a DateInterval which can be added to the
     * current DateTime, to be compared with the expected return value from
     * fromHash().
     *
     * @return array
     */
    public function provideInputForTimeStringFromHash()
    {
        return array(
            array(
                array(
                    'timestring' => 'now',
                ),
                'P0Y',
            ),
            array(
                array(
                    'timestring' => '+42 seconds',
                ),
                'PT42S',
            ),
            array(
                array(
                    'timestring' => '+3 months 2 days 5 hours',
                ),
                'P3M2DT5H',
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
                    'useSeconds' => true,
                    'defaultType' => DateAndTime::DEFAULT_EMPTY,
                ),
            ),
            array(
                array(
                    'useSeconds' => false,
                    'defaultType' => DateAndTime::DEFAULT_CURRENT_DATE,
                ),
            ),
            array(
                array(
                    'useSeconds' => false,
                    'defaultType' => DateAndTime::DEFAULT_CURRENT_DATE_ADJUSTED,
                    'dateInterval' => new DateInterval('P2Y'),
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
                    // useSeconds must be bool
                    'useSeconds' => 23,
                ),
            ),
            array(
                array(
                    // defaultType must be constant
                    'defaultType' => 42,
                ),
            ),
            array(
                array(
                    // No dateInterval allowed with this defaultType
                    'defaultType' => DateAndTime::DEFAULT_EMPTY,
                    'dateInterval' => new DateInterval('P2Y'),
                ),
            ),
            array(
                array(
                    // dateInterval must be a \DateInterval
                    'defaultType' => DateAndTime::DEFAULT_CURRENT_DATE_ADJUSTED,
                    'dateInterval' => new stdClass(),
                ),
            ),
        );
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezdatetime';
    }

    public function provideDataForGetName(): array
    {
        return [
            [$this->getEmptyValueExpectation(), [], 'en_GB', ''],
            [DateAndTimeValue::fromTimestamp(438512400), [], 'en_GB', 'Thu 1983-24-11 09:00:00'],
        ];
    }
}
