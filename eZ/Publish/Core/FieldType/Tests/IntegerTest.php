<?php
/**
 * File containing the IntegerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\Integer\Type as Integer,
    eZ\Publish\Core\FieldType\Integer\Value as IntegerValue,
    ReflectionObject;

/**
 * @group fieldType
 * @group ezinteger
 */
class IntegerTest extends StandardizedFieldTypeTest
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
        return new Integer();
    }

    /**
     * Returns the validator configuration schema expected from the field type.
     *
     * @return array
     */
    protected function getValidatorConfigurationSchemaExpectation()
    {
        return array(
            "IntegerValueValidator" => array(
                "minIntegerValue" => array(
                    "type" => "int",
                    "default" => 0
                ),
                "maxIntegerValue" => array(
                    "type" => "int",
                    "default" => false
                )
            )
        );
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
     * @return void
     */
    protected function getEmptyValueExpectation()
    {
        return new IntegerValue;
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
                'foo',
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                array(),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new IntegerValue( 'foo' ),
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
                new IntegerValue,
            ),
            array(
                42,
                new IntegerValue( 42 ),
            ),
            array(
                23,
                new IntegerValue( 23 ),
            ),
            array(
                new IntegerValue( 23 ),
                new IntegerValue( 23 ),
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
                new IntegerValue( 42 ),
                42,
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
                null,
                null,
            ),
            array(
                42,
                new IntegerValue( 42 ),
            ),
        );
    }

    /**
     * Provide data sets with validator configurations which are considered
     * valid by the {@link validateValidatorConfiguration()} method.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of validator configurations.
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(),
     *      ),
     *      array(
     *          array(
     *              'IntegerValueValidator' => array(
     *                  'minIntegerValue' => 0,
     *                  'maxIntegerValue' => 23,
     *              )
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidValidatorConfiguration()
    {
        return array(
            array(
                array()
            ),
            array(
                array(
                    'IntegerValueValidator' => array(
                        'minIntegerValue' => false,
                    )
                )
            ),
            array(
                array(
                    'IntegerValueValidator' => array(
                        'minIntegerValue' => 23,
                    )
                )
            ),
            array(
                array(
                    'IntegerValueValidator' => array(
                        'maxIntegerValue' => false,
                    )
                )
            ),
            array(
                array(
                    'IntegerValueValidator' => array(
                        'maxIntegerValue' => 23,
                    )
                )
            ),
            array(
                array(
                    'IntegerValueValidator' => array(
                        'minIntegerValue' => 23,
                        'maxIntegerValue' => 42,
                    )
                )
            ),
        );
    }

    /**
     * Provide data sets with validator configurations which are considered
     * invalid by the {@link validateValidatorConfiguration()} method. The
     * method must return a non-empty array of valiation errors when receiving
     * one of the provided values.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of validator configurations.
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(
     *              'NonExistentValidator' => array(),
     *          ),
     *      ),
     *      array(
     *          array(
     *              // Typos
     *              'InTEgervALUeVALIdator' => array(
     *                  'iinIntegerValue' => 0,
     *                  'maxIntegerValue' => 23,
     *              )
     *          )
     *      ),
     *      array(
     *          array(
     *              'IntegerValueValidator' => array(
     *                  // Incorrect value types
     *                  'minIntegerValue' => true,
     *                  'maxIntegerValue' => false,
     *              )
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInvalidValidatorConfiguration()
    {
        return array(
            array(
                array(
                    'NonExistentValidator' => array(),
                ),
            ),
            array(
                array(
                    'IntegerValueValidator' => array(
                        'nonExistentValue' => 23
                    ),
                ),
            ),
            array(
                array(
                    'IntegerValueValidator' => array(
                        'minIntegerValue' => .23,
                    ),
                ),
            ),
            array(
                array(
                    'IntegerValueValidator' => array(
                        'maxIntegerValue' => .42,
                    ),
                )
            ),
        );
    }
}
