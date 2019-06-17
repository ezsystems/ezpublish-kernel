<?php

/**
 * File containing the eZ\Publish\Core\FieldType\Tests\FieldTypeTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\MapLocation;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

class MapLocationTest extends FieldTypeTest
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
        $fieldType = new MapLocation\Type();
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
     */
    protected function getEmptyValueExpectation()
    {
        return new MapLocation\Value();
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
                'some string',
                InvalidArgumentException::class,
            ),
            array(
                new MapLocation\Value(
                    array(
                        'latitude' => 'foo',
                    )
                ),
                InvalidArgumentException::class,
            ),
            array(
                new MapLocation\Value(
                    array(
                        'latitude' => 23.42,
                        'longitude' => 'bar',
                    )
                ),
                InvalidArgumentException::class,
            ),
            array(
                new MapLocation\Value(
                    array(
                        'latitude' => 23.42,
                        'longitude' => 42.23,
                        'address' => array(),
                    )
                ),
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
                new MapLocation\Value(),
            ),
            array(
                array(),
                new MapLocation\Value(),
            ),
            array(
                new MapLocation\Value(),
                new MapLocation\Value(),
            ),
            array(
                array(
                    'latitude' => 23.42,
                    'longitude' => 42.23,
                    'address' => 'Nowhere',
                ),
                new MapLocation\Value(
                    array(
                        'latitude' => 23.42,
                        'longitude' => 42.23,
                        'address' => 'Nowhere',
                    )
                ),
            ),
            array(
                array(
                    'latitude' => 23,
                    'longitude' => 42,
                    'address' => 'Somewhere',
                ),
                new MapLocation\Value(
                    array(
                        'latitude' => 23,
                        'longitude' => 42,
                        'address' => 'Somewhere',
                    )
                ),
            ),
            array(
                new MapLocation\Value(
                    array(
                        'latitude' => 23.42,
                        'longitude' => 42.23,
                        'address' => 'Nowhere',
                    )
                ),
                new MapLocation\Value(
                    array(
                        'latitude' => 23.42,
                        'longitude' => 42.23,
                        'address' => 'Nowhere',
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
                new MapLocation\Value(),
                null,
            ),
            array(
                new MapLocation\Value(
                    array(
                        'latitude' => 23.42,
                        'longitude' => 42.23,
                        'address' => 'Nowhere',
                    )
                ),
                array(
                    'latitude' => 23.42,
                    'longitude' => 42.23,
                    'address' => 'Nowhere',
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
     *          new BinaryFileValue(
     *              array(
     *                  'path' => 'some/file/here',
     *                  'fileName' => 'sindelfingen.jpg',
     *                  'fileSize' => 2342,
     *                  'downloadCount' => 0,
     *                  'mimeType' => 'image/jpeg',
     *              )
     *          )
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
                null,
                new MapLocation\Value(),
            ),
            array(
                array(
                    'latitude' => 23.42,
                    'longitude' => 42.23,
                    'address' => 'Nowhere',
                ),
                new MapLocation\Value(
                    array(
                        'latitude' => 23.42,
                        'longitude' => 42.23,
                        'address' => 'Nowhere',
                    )
                ),
            ),
        );
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezgmaplocation';
    }

    public function provideDataForGetName(): array
    {
        return [
            [$this->getEmptyValueExpectation(), [], 'en_GB', ''],
            [new MapLocation\Value(['address' => 'Bag End, The Shire']), [], 'en_GB', 'Bag End, The Shire'],
        ];
    }
}
