<?php
/**
 * File containing the DateAndTimeTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\DateAndTime\Type as DateAndTime;
use eZ\Publish\Core\FieldType\DateAndTime\Value as DateAndTimeValue;
use ReflectionObject;
use DateTime;
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
        return new DateAndTime();
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
            "useSeconds" => array(
                "type" => "bool",
                "default" => false
            ),
            "defaultType" => array(
                "type" => "choice",
                "default" => DateAndTime::DEFAULT_EMPTY
            ),
            "dateInterval" => array(
                "type" => "dateInterval",
                "default" => null
            )
        );
    }

    /**
     * Returns the empty value expected from the field type.
     *
     * @return void
     */
    protected function getEmptyValueExpectation()
    {
        return new DateAndTimeValue;
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
                null,
                new DateAndTimeValue,
            ),
            array(
                '2012-08-28 12:20 Europe/Berlin',
                DateAndTimeValue::fromString( '2012-08-28 12:20 Europe/Berlin' ),
            ),
            array(
                1346149200,
                DateAndTimeValue::fromTimestamp( 1346149200 )
            ),
            array(
                ( $dateTime = new \DateTime() ),
                new DateAndTimeValue( $dateTime ),
            ),
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
                null,
                null,
            ),
            array(
                new DateAndTimeValue( $date = new \DateTime( 'Tue, 28 Aug 2012 12:20:00 +0200' ) ),
                array(
                    'rfc850' => $date->format( \DateTime::RFC850 ),
                    'timestamp' => $date->getTimeStamp(),
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
        $date = new \DateTime( 'Tue, 28 Aug 2012 12:20:00 +0200' );

        return array(
            array(
                null,
                null,
            ),
            array(
                array(
                    'rfc850' => $date->format( \DateTime::RFC850 ),
                    'timestamp' => $date->getTimeStamp(),
                ),
                new DateAndTimeValue( $date ),
            ),
            array(
                array(
                    'timestamp' => $date->getTimeStamp(),
                ),
                DateAndTimeValue::fromTimestamp( $date->getTimeStamp() ),
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
                array()
            ),
            array(
                array(
                    'useSeconds' => true,
                    'defaultType' => DateAndTime::DEFAULT_EMPTY,
                )
            ),
            array(
                array(
                    'useSeconds' => false,
                    'defaultType' => DateAndTime::DEFAULT_CURRENT_DATE,
                )
            ),
            array(
                array(
                    'useSeconds' => false,
                    'defaultType' => DateAndTime::DEFAULT_CURRENT_DATE_ADJUSTED,
                    'dateInterval' => new DateInterval( 'P2Y' ),
                )
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
                )
            ),
            array(
                array(
                    // defaultType must be constant
                    'defaultType' => 42,
                )
            ),
            array(
                array(
                    // No dateInterval allowed with this defaultType
                    'defaultType' => DateAndTime::DEFAULT_EMPTY,
                    'dateInterval' => new DateInterval( 'P2Y' ),
                )
            ),
            array(
                array(
                    // dateInterval must be a \DateInterval
                    'defaultType' => DateAndTime::DEFAULT_CURRENT_DATE_ADJUSTED,
                    'dateInterval' => new stdClass(),
                )
            ),
        );
    }
}
