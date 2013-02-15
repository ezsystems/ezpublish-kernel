<?php
/**
 * File containing the CountryTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\Country\Type as Country;
use eZ\Publish\Core\FieldType\Country\Value as CountryValue;
use ReflectionObject;

/**
 * @group fieldType
 * @group ezcountry
 */
class CountryTest extends FieldTypeTest
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
        return new Country(
            array(
                "BE" => array(
                    "Name" => "Belgium",
                    "Alpha2" => "BE",
                    "Alpha3" => "BEL",
                    "IDC" => 32,
                ),
                "FR" => array(
                    "Name" => "France",
                    "Alpha2" => "FR",
                    "Alpha3" => "FRA",
                    "IDC" => 33,
                ),
                "NO" => array(
                    "Name" => "Norway",
                    "Alpha2" => "NO",
                    "Alpha3" => "NOR",
                    "IDC" => 47,
                ),
                "KP" => array(
                    "Name" => "Korea, Democratic People's Republic of",
                    "Alpha2" => "KP",
                    "Alpha3" => "PRK",
                    "IDC" => 850,
                ),
                "TF" => array(
                    "Name" => "French Southern Territories",
                    "Alpha2" => "TF",
                    "Alpha3" => "ATF",
                    "IDC" => 0,
                ),
                "CF" => array(
                    "Name" => "Central African Republic",
                    "Alpha2" => "CF",
                    "Alpha3" => "CAF",
                    "IDC" => 236,
                ),
            )
        );
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
            "isMultiple" => array(
                "type" => "boolean",
                "default" => false
            )
        );
    }

    /**
     * Returns the empty value expected from the field type.
     *
     * @return \eZ\Publish\Core\FieldType\Checkbox\Value
     */
    protected function getEmptyValueExpectation()
    {
        return new CountryValue;
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
                "LegoLand",
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                array( "Norway", "France", "LegoLand" ),
                'eZ\\Publish\\Core\\FieldType\\Country\\Exception\\InvalidValue',
            ),
            array(
                array( "FR", "BE", "LE" ),
                'eZ\\Publish\\Core\\FieldType\\Country\\Exception\\InvalidValue',
            ),
            array(
                array( "FRE", "BEL", "LEG" ),
                'eZ\\Publish\\Core\\FieldType\\Country\\Exception\\InvalidValue',
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
                array( "BE", "FR" ),
                new CountryValue(
                    array(
                        "BE" => array(
                            "Name" => "Belgium",
                            "Alpha2" => "BE",
                            "Alpha3" => "BEL",
                            "IDC" => 32,
                        ),
                        "FR" => array(
                            "Name" => "France",
                            "Alpha2" => "FR",
                            "Alpha3" => "FRA",
                            "IDC" => 33,
                        )
                    )
                )
            ),
            array(
                array( "Belgium" ),
                new CountryValue(
                    array(
                        "BE" => array(
                            "Name" => "Belgium",
                            "Alpha2" => "BE",
                            "Alpha3" => "BEL",
                            "IDC" => 32,
                        )
                    )
                )
            ),
            array(
                array( "BE" ),
                new CountryValue(
                    array(
                        "BE" => array(
                            "Name" => "Belgium",
                            "Alpha2" => "BE",
                            "Alpha3" => "BEL",
                            "IDC" => 32,
                        )
                    )
                )
            ),
            array(
                array( "BEL" ),
                new CountryValue(
                    array(
                        "BE" => array(
                            "Name" => "Belgium",
                            "Alpha2" => "BE",
                            "Alpha3" => "BEL",
                            "IDC" => 32,
                        )
                    )
                )
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
                new CountryValue(
                    array(
                        "BE" => array(
                            "Name" => "Belgium",
                            "Alpha2" => "BE",
                            "Alpha3" => "BEL",
                            "IDC" => 32,
                        )
                    )
                ),
                array( "BE" ),
            ),
            array(
                new CountryValue(
                    array(
                        "BE" => array(
                            "Name" => "Belgium",
                            "Alpha2" => "BE",
                            "Alpha3" => "BEL",
                            "IDC" => 32,
                        ),
                        "FR" => array(
                            "Name" => "France",
                            "Alpha2" => "FR",
                            "Alpha3" => "FRA",
                            "IDC" => 33,
                        )
                    )
                ),
                array( "BE", "FR" ),
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
                array( "BE" ),
                new CountryValue(
                    array(
                        "BE" => array(
                            "Name" => "Belgium",
                            "Alpha2" => "BE",
                            "Alpha3" => "BEL",
                            "IDC" => 32,
                        )
                    )
                ),
            ),
            array(
                array( "BE", "FR" ),
                new CountryValue(
                    array(
                        "BE" => array(
                            "Name" => "Belgium",
                            "Alpha2" => "BE",
                            "Alpha3" => "BEL",
                            "IDC" => 32,
                        ),
                        "FR" => array(
                            "Name" => "France",
                            "Alpha2" => "FR",
                            "Alpha3" => "FRA",
                            "IDC" => 33,
                        )
                    )
                ),
            ),
        );
    }
}
