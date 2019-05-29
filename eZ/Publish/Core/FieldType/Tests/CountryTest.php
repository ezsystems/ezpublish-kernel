<?php

/**
 * File containing the CountryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\Country\Type as Country;
use eZ\Publish\Core\FieldType\Country\Value as CountryValue;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\FieldType\Country\Exception\InvalidValue;

/**
 * @group fieldType
 * @group ezcountry
 */
class CountryTest extends FieldTypeTest
{
    protected function provideFieldTypeIdentifier()
    {
        return 'ezcountry';
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
        $fieldType = new Country(
            array(
                'BE' => array(
                    'Name' => 'Belgium',
                    'Alpha2' => 'BE',
                    'Alpha3' => 'BEL',
                    'IDC' => 32,
                ),
                'FR' => array(
                    'Name' => 'France',
                    'Alpha2' => 'FR',
                    'Alpha3' => 'FRA',
                    'IDC' => 33,
                ),
                'NO' => array(
                    'Name' => 'Norway',
                    'Alpha2' => 'NO',
                    'Alpha3' => 'NOR',
                    'IDC' => 47,
                ),
                'KP' => array(
                    'Name' => "Korea, Democratic People's Republic of",
                    'Alpha2' => 'KP',
                    'Alpha3' => 'PRK',
                    'IDC' => 850,
                ),
                'TF' => array(
                    'Name' => 'French Southern Territories',
                    'Alpha2' => 'TF',
                    'Alpha3' => 'ATF',
                    'IDC' => 0,
                ),
                'CF' => array(
                    'Name' => 'Central African Republic',
                    'Alpha2' => 'CF',
                    'Alpha3' => 'CAF',
                    'IDC' => 236,
                ),
            )
        );
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
            'isMultiple' => array(
                'type' => 'boolean',
                'default' => false,
            ),
        );
    }

    /**
     * Returns the empty value expected from the field type.
     *
     * @return \eZ\Publish\Core\FieldType\Checkbox\Value
     */
    protected function getEmptyValueExpectation()
    {
        return new CountryValue();
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
                'LegoLand',
                InvalidArgumentException::class,
            ),
            array(
                array('Norway', 'France', 'LegoLand'),
                InvalidValue::class,
            ),
            array(
                array('FR', 'BE', 'LE'),
                InvalidValue::class,
            ),
            array(
                array('FRE', 'BEL', 'LEG'),
                InvalidValue::class,
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
                array('BE', 'FR'),
                new CountryValue(
                    array(
                        'BE' => array(
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ),
                        'FR' => array(
                            'Name' => 'France',
                            'Alpha2' => 'FR',
                            'Alpha3' => 'FRA',
                            'IDC' => 33,
                        ),
                    )
                ),
            ),
            array(
                array('Belgium'),
                new CountryValue(
                    array(
                        'BE' => array(
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ),
                    )
                ),
            ),
            array(
                array('BE'),
                new CountryValue(
                    array(
                        'BE' => array(
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ),
                    )
                ),
            ),
            array(
                array('BEL'),
                new CountryValue(
                    array(
                        'BE' => array(
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ),
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
                new CountryValue(
                    array(
                        'BE' => array(
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ),
                    )
                ),
                array('BE'),
            ),
            array(
                new CountryValue(
                    array(
                        'BE' => array(
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ),
                        'FR' => array(
                            'Name' => 'France',
                            'Alpha2' => 'FR',
                            'Alpha3' => 'FRA',
                            'IDC' => 33,
                        ),
                    )
                ),
                array('BE', 'FR'),
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
                array('BE'),
                new CountryValue(
                    array(
                        'BE' => array(
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ),
                    )
                ),
            ),
            array(
                array('BE', 'FR'),
                new CountryValue(
                    array(
                        'BE' => array(
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ),
                        'FR' => array(
                            'Name' => 'France',
                            'Alpha2' => 'FR',
                            'Alpha3' => 'FRA',
                            'IDC' => 33,
                        ),
                    )
                ),
            ),
        );
    }

    public function provideDataForGetName(): array
    {
        return [
            [new CountryValue(), [], 'en_GB', ''],
            [new CountryValue(['FR' => ['Name' => 'France']]), [], 'en_GB', 'France'],
            [
                new CountryValue(['FR' => ['Name' => 'France'], 'DE' => ['Name' => 'Deutschland']]),
                [],
                'en_GB',
                'France, Deutschland',
            ],
        ];
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
                    'fieldSettings' => array(
                        'isMultiple' => true,
                    ),
                ),
                new CountryValue(),
            ),
            array(
                array(
                    'fieldSettings' => array(
                        'isMultiple' => false,
                    ),
                ),
                new CountryValue(
                    array(
                        'BE' => array(
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ),
                    )
                ),
            ),
            array(
                array(
                    'fieldSettings' => array(
                        'isMultiple' => true,
                    ),
                ),
                new CountryValue(
                    array(
                        'BE' => array(
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ),
                        'FR' => array(
                            'Name' => 'France',
                            'Alpha2' => 'FR',
                            'Alpha3' => 'FRA',
                            'IDC' => 33,
                        ),
                    )
                ),
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
                    'fieldSettings' => array(
                        'isMultiple' => false,
                    ),
                ),
                new CountryValue(
                    array(
                        'BE' => array(
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ),
                        'FR' => array(
                            'Name' => 'France',
                            'Alpha2' => 'FR',
                            'Alpha3' => 'FRA',
                            'IDC' => 33,
                        ),
                    )
                ),
                array(
                    new ValidationError(
                        'Field definition does not allow multiple countries to be selected.',
                        null,
                        array(),
                        'countries'
                    ),
                ),
            ),
            array(
                array(
                    'fieldSettings' => array(
                        'isMultiple' => true,
                    ),
                ),
                new CountryValue(
                    array(
                        'LE' => array(
                            'Name' => 'LegoLand',
                            'Alpha2' => 'LE',
                            'Alpha3' => 'LEG',
                            'IDC' => 888,
                        ),
                    )
                ),
                array(
                    new ValidationError(
                        "Country with Alpha2 code '%alpha2%' is not defined in FieldType settings.",
                        null,
                        array(
                            '%alpha2%' => 'LE',
                        ),
                        'countries'
                    ),
                ),
            ),
        );
    }
}
