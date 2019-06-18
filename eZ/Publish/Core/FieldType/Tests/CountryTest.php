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
            [
                'BE' => [
                    'Name' => 'Belgium',
                    'Alpha2' => 'BE',
                    'Alpha3' => 'BEL',
                    'IDC' => 32,
                ],
                'FR' => [
                    'Name' => 'France',
                    'Alpha2' => 'FR',
                    'Alpha3' => 'FRA',
                    'IDC' => 33,
                ],
                'NO' => [
                    'Name' => 'Norway',
                    'Alpha2' => 'NO',
                    'Alpha3' => 'NOR',
                    'IDC' => 47,
                ],
                'KP' => [
                    'Name' => "Korea, Democratic People's Republic of",
                    'Alpha2' => 'KP',
                    'Alpha3' => 'PRK',
                    'IDC' => 850,
                ],
                'TF' => [
                    'Name' => 'French Southern Territories',
                    'Alpha2' => 'TF',
                    'Alpha3' => 'ATF',
                    'IDC' => 0,
                ],
                'CF' => [
                    'Name' => 'Central African Republic',
                    'Alpha2' => 'CF',
                    'Alpha3' => 'CAF',
                    'IDC' => 236,
                ],
            ]
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
        return [];
    }

    /**
     * Returns the settings schema expected from the field type.
     *
     * @return array
     */
    protected function getSettingsSchemaExpectation()
    {
        return [
            'isMultiple' => [
                'type' => 'boolean',
                'default' => false,
            ],
        ];
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
        return [
            [
                'LegoLand',
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ],
            [
                ['Norway', 'France', 'LegoLand'],
                'eZ\\Publish\\Core\\FieldType\\Country\\Exception\\InvalidValue',
            ],
            [
                ['FR', 'BE', 'LE'],
                'eZ\\Publish\\Core\\FieldType\\Country\\Exception\\InvalidValue',
            ],
            [
                ['FRE', 'BEL', 'LEG'],
                'eZ\\Publish\\Core\\FieldType\\Country\\Exception\\InvalidValue',
            ],
        ];
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
        return [
            [
                ['BE', 'FR'],
                new CountryValue(
                    [
                        'BE' => [
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ],
                        'FR' => [
                            'Name' => 'France',
                            'Alpha2' => 'FR',
                            'Alpha3' => 'FRA',
                            'IDC' => 33,
                        ],
                    ]
                ),
            ],
            [
                ['Belgium'],
                new CountryValue(
                    [
                        'BE' => [
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ],
                    ]
                ),
            ],
            [
                ['BE'],
                new CountryValue(
                    [
                        'BE' => [
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ],
                    ]
                ),
            ],
            [
                ['BEL'],
                new CountryValue(
                    [
                        'BE' => [
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ],
                    ]
                ),
            ],
        ];
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
        return [
            [
                new CountryValue(
                    [
                        'BE' => [
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ],
                    ]
                ),
                ['BE'],
            ],
            [
                new CountryValue(
                    [
                        'BE' => [
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ],
                        'FR' => [
                            'Name' => 'France',
                            'Alpha2' => 'FR',
                            'Alpha3' => 'FRA',
                            'IDC' => 33,
                        ],
                    ]
                ),
                ['BE', 'FR'],
            ],
        ];
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
        return [
            [
                ['BE'],
                new CountryValue(
                    [
                        'BE' => [
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ],
                    ]
                ),
            ],
            [
                ['BE', 'FR'],
                new CountryValue(
                    [
                        'BE' => [
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ],
                        'FR' => [
                            'Name' => 'France',
                            'Alpha2' => 'FR',
                            'Alpha3' => 'FRA',
                            'IDC' => 33,
                        ],
                    ]
                ),
            ],
        ];
    }

    public function provideDataForGetName()
    {
        return [
            [
                new CountryValue(),
                '',
            ],
            [
                new CountryValue(['FR' => ['Name' => 'France']]),
                'France',
            ],
            [
                new CountryValue(['FR' => ['Name' => 'France'], 'DE' => ['Name' => 'Deutschland']]),
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
        return [
            [
                [
                    'fieldSettings' => [
                        'isMultiple' => true,
                    ],
                ],
                new CountryValue(),
            ],
            [
                [
                    'fieldSettings' => [
                        'isMultiple' => false,
                    ],
                ],
                new CountryValue(
                    [
                        'BE' => [
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ],
                    ]
                ),
            ],
            [
                [
                    'fieldSettings' => [
                        'isMultiple' => true,
                    ],
                ],
                new CountryValue(
                    [
                        'BE' => [
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ],
                        'FR' => [
                            'Name' => 'France',
                            'Alpha2' => 'FR',
                            'Alpha3' => 'FRA',
                            'IDC' => 33,
                        ],
                    ]
                ),
            ],
        ];
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
        return [
            [
                [
                    'fieldSettings' => [
                        'isMultiple' => false,
                    ],
                ],
                new CountryValue(
                    [
                        'BE' => [
                            'Name' => 'Belgium',
                            'Alpha2' => 'BE',
                            'Alpha3' => 'BEL',
                            'IDC' => 32,
                        ],
                        'FR' => [
                            'Name' => 'France',
                            'Alpha2' => 'FR',
                            'Alpha3' => 'FRA',
                            'IDC' => 33,
                        ],
                    ]
                ),
                [
                    new ValidationError(
                        'Field definition does not allow multiple countries to be selected.',
                        null,
                        [],
                        'countries'
                    ),
                ],
            ],
            [
                [
                    'fieldSettings' => [
                        'isMultiple' => true,
                    ],
                ],
                new CountryValue(
                    [
                        'LE' => [
                            'Name' => 'LegoLand',
                            'Alpha2' => 'LE',
                            'Alpha3' => 'LEG',
                            'IDC' => 888,
                        ],
                    ]
                ),
                [
                    new ValidationError(
                        "Country with Alpha2 code '%alpha2%' is not defined in FieldType settings.",
                        null,
                        [
                            '%alpha2%' => 'LE',
                        ],
                        'countries'
                    ),
                ],
            ],
        ];
    }
}
