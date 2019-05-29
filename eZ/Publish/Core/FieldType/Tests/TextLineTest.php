<?php

/**
 * File containing the TextLineTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\TextLine\Type as TextLineType;
use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * @group fieldType
 * @group ezstring
 */
class TextLineTest extends FieldTypeTest
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
     * @return \eZ\Publish\Core\FieldType\FieldType
     */
    protected function createFieldTypeUnderTest()
    {
        $fieldType = new TextLineType();
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
        return array(
            'StringLengthValidator' => array(
                'minStringLength' => array(
                    'type' => 'int',
                    'default' => 0,
                ),
                'maxStringLength' => array(
                    'type' => 'int',
                    'default' => null,
                ),
            ),
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
     * @return \eZ\Publish\Core\FieldType\TextLine\Value
     */
    protected function getEmptyValueExpectation()
    {
        return new TextLineValue();
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
                new TextLineValue(23),
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
                new TextLineValue(),
            ),
            array(
                '',
                new TextLineValue(),
            ),
            array(
                ' ',
                new TextLineValue(),
            ),
            array(
                ' sindelfingen ',
                new TextLineValue(' sindelfingen '),
            ),
            array(
                new TextLineValue(' sindelfingen '),
                new TextLineValue(' sindelfingen '),
            ),
            array(
                // 11+ numbers - EZP-21771
                '12345678901',
                new TextLineValue('12345678901'),
            ),
            array(
                new TextLineValue(''),
                new TextLineValue(),
            ),
            array(
                new TextLineValue(' '),
                new TextLineValue(),
            ),
            array(
                new TextLineValue(null),
                new TextLineValue(),
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
                new TextLineValue(),
                null,
            ),
            array(
                new TextLineValue(''),
                null,
            ),
            array(
                new TextLineValue('sindelfingen'),
                'sindelfingen',
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
                null,
                new TextLineValue(),
            ),
            array(
                '',
                new TextLineValue(),
            ),
            array(
                'sindelfingen',
                new TextLineValue('sindelfingen'),
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
     *              'StringLengthValidator' => array(
     *                  'minStringLength' => 0,
     *                  'maxStringLength' => 23,
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
                array(),
            ),
            array(
                array(
                    'StringLengthValidator' => array(
                        'minStringLength' => null,
                    ),
                ),
            ),
            array(
                array(
                    'StringLengthValidator' => array(
                        'minStringLength' => 23,
                    ),
                ),
            ),
            array(
                array(
                    'StringLengthValidator' => array(
                        'maxStringLength' => null,
                    ),
                ),
            ),
            array(
                array(
                    'StringLengthValidator' => array(
                        'maxStringLength' => 23,
                    ),
                ),
            ),
            array(
                array(
                    'StringLengthValidator' => array(
                        'minStringLength' => 23,
                        'maxStringLength' => 42,
                    ),
                ),
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
     *                  'iinStringLength' => 0,
     *                  'maxStringLength' => 23,
     *              )
     *          )
     *      ),
     *      array(
     *          array(
     *              'StringLengthValidator' => array(
     *                  // Incorrect value types
     *                  'minStringLength' => true,
     *                  'maxStringLength' => false,
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
                    'StringLengthValidator' => array(
                        'nonExistentValue' => 23,
                    ),
                ),
            ),
            array(
                array(
                    'StringLengthValidator' => array(
                        'minStringLength' => .23,
                    ),
                ),
            ),
            array(
                array(
                    'StringLengthValidator' => array(
                        'maxStringLength' => .42,
                    ),
                ),
            ),
            array(
                array(
                    'StringLengthValidator' => array(
                        'minStringLength' => -23,
                    ),
                ),
            ),
            array(
                array(
                    'StringLengthValidator' => array(
                        'maxStringLength' => -42,
                    ),
                ),
            ),
            array(
                array(
                    'StringLengthValidator' => array(
                        'maxStringLength' => 23,
                        'minStringLength' => 42,
                    ),
                ),
            ),
        );
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezstring';
    }

    public function provideDataForGetName(): array
    {
        return array(
            array($this->getEmptyValueExpectation(), [], 'en_GB', ''),
            array(new TextLineValue('This is a line of text'), [], 'en_GB', 'This is a line of text'),
        );
    }

    /**
     * Provides data sets with validator configuration and/or field settings and
     * field value which are considered valid by the {@link validate()} method.
     *
     * ATTENTION: This is a default implementation, which must be overwritten if
     * a FieldType supports validation!
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(
     *              "validatorConfiguration" => array(
     *                  "StringLengthValidator" => array(
     *                      "minStringLength" => 2,
     *                      "maxStringLength" => 10,
     *                  ),
     *              ),
     *          ),
     *          new TextLineValue( "lalalala" ),
     *      ),
     *      array(
     *          array(
     *              "fieldSettings" => array(
     *                  'isMultiple' => true
     *              ),
     *          ),
     *          new CountryValue(
     *              array(
     *                  "BE" => array(
     *                      "Name" => "Belgium",
     *                      "Alpha2" => "BE",
     *                      "Alpha3" => "BEL",
     *                      "IDC" => 32,
     *                  ),
     *              ),
     *          ),
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidDataForValidate()
    {
        return array(
            array(
                array(
                    'validatorConfiguration' => array(
                        'StringLengthValidator' => array(
                            'minStringLength' => 2,
                            'maxStringLength' => 10,
                        ),
                    ),
                ),
                new TextLineValue('lalalala'),
            ),
            array(
                array(
                    'validatorConfiguration' => array(
                        'StringLengthValidator' => array(
                            'maxStringLength' => 10,
                        ),
                    ),
                ),
                new TextLineValue('lililili'),
            ),
            array(
                array(
                    'validatorConfiguration' => array(
                        'StringLengthValidator' => array(
                            'maxStringLength' => 10,
                        ),
                    ),
                ),
                new TextLineValue('♔♕♖♗♘♙♚♛♜♝'),
            ),
        );
    }

    /**
     * Provides data sets with validator configuration and/or field settings,
     * field value and corresponding validation errors returned by
     * the {@link validate()} method.
     *
     * ATTENTION: This is a default implementation, which must be overwritten
     * if a FieldType supports validation!
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(
     *              "validatorConfiguration" => array(
     *                  "IntegerValueValidator" => array(
     *                      "minIntegerValue" => 5,
     *                      "maxIntegerValue" => 10
     *                  ),
     *              ),
     *          ),
     *          new IntegerValue( 3 ),
     *          array(
     *              new ValidationError(
     *                  "The value can not be lower than %size%.",
     *                  null,
     *                  array(
     *                      "size" => 5
     *                  ),
     *              ),
     *          ),
     *      ),
     *      array(
     *          array(
     *              "fieldSettings" => array(
     *                  "isMultiple" => false
     *              ),
     *          ),
     *          new CountryValue(
     *              "BE" => array(
     *                  "Name" => "Belgium",
     *                  "Alpha2" => "BE",
     *                  "Alpha3" => "BEL",
     *                  "IDC" => 32,
     *              ),
     *              "FR" => array(
     *                  "Name" => "France",
     *                  "Alpha2" => "FR",
     *                  "Alpha3" => "FRA",
     *                  "IDC" => 33,
     *              ),
     *          )
     *      ),
     *      array(
     *          new ValidationError(
     *              "Field definition does not allow multiple countries to be selected."
     *          ),
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInvalidDataForValidate()
    {
        return array(
            array(
                array(
                    'validatorConfiguration' => array(
                        'StringLengthValidator' => array(
                            'minStringLength' => 5,
                            'maxStringLength' => 10,
                        ),
                    ),
                ),
                new TextLineValue('aaa'),
                array(
                    new ValidationError(
                        'The string cannot be shorter than %size% character.',
                        'The string cannot be shorter than %size% characters.',
                        array(
                            '%size%' => 5,
                        ),
                        'text'
                    ),
                ),
            ),
            array(
                array(
                    'validatorConfiguration' => array(
                        'StringLengthValidator' => array(
                            'minStringLength' => 5,
                            'maxStringLength' => 10,
                        ),
                    ),
                ),
                new TextLineValue('0123456789012345'),
                array(
                    new ValidationError(
                        'The string can not exceed %size% character.',
                        'The string can not exceed %size% characters.',
                        array(
                            '%size%' => 10,
                        ),
                        'text'
                    ),
                ),
            ),
            array(
                array(
                    'validatorConfiguration' => array(
                        'StringLengthValidator' => array(
                            'minStringLength' => 10,
                            'maxStringLength' => 5,
                        ),
                    ),
                ),
                new TextLineValue('1234567'),
                array(
                    new ValidationError(
                        'The string can not exceed %size% character.',
                        'The string can not exceed %size% characters.',
                        array(
                            '%size%' => 5,
                        ),
                        'text'
                    ),
                    new ValidationError(
                        'The string cannot be shorter than %size% character.',
                        'The string cannot be shorter than %size% characters.',
                        array(
                            '%size%' => 10,
                        ),
                        'text'
                    ),
                ),
            ),
            array(
                array(
                    'validatorConfiguration' => array(
                        'StringLengthValidator' => array(
                            'minStringLength' => 5,
                            'maxStringLength' => 10,
                        ),
                    ),
                ),
                new TextLineValue('ABC♔'),
                array(
                    new ValidationError(
                        'The string cannot be shorter than %size% character.',
                        'The string cannot be shorter than %size% characters.',
                        array(
                            '%size%' => 5,
                        ),
                        'text'
                    ),
                ),
            ),
        );
    }
}
