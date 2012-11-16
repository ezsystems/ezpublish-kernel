<?php
/**
 * File contains: eZ\Publish\API\Repository\Tests\FieldType\CountryIntegrationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;
use eZ\Publish\Core\FieldType\Country\Value as CountryValue,
    eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Integration test for use field type
 *
 * @group integration
 * @group field-type
 */
class CountryIntegrationTest extends BaseIntegrationTest
{
    /**
     * Get name of tested field tyoe
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezcountry';
    }

    /**
     * Get expected settings schema
     *
     * @return array
     */
    public function getSettingsSchema()
    {
        return array(
            "isMultiple" => array(
                "type" => "boolean",
                "default" => false
            )
        );
    }

    /**
     * Get a valid $fieldSettings value
     *
     * @return mixed
     */
    public function getValidFieldSettings()
    {
        return array(
            "isMultiple" => false
        );
    }

    /**
     * Get $fieldSettings value not accepted by the field type
     *
     * @return mixed
     */
    public function getInvalidFieldSettings()
    {
        return array(
            'somethingUnknown' => 0,
        );
    }

    /**
     * Get expected validator schema
     *
     * @return array
     */
    public function getValidatorSchema()
    {
        return array();
    }

    /**
     * Get a valid $validatorConfiguration
     *
     * @return mixed
     */
    public function getValidValidatorConfiguration()
    {
        return array();
    }

    /**
     * Get $validatorConfiguration not accepted by the field type
     *
     * @return mixed
     */
    public function getInvalidValidatorConfiguration()
    {
        return array(
            "unknown" => array( "value" => 42 ),
        );
    }

    /**
     * Get initial field data for valid object creation
     *
     * @return mixed
     */
    public function getValidCreationFieldData()
    {
        return new CountryValue(
            array(
                "BE" => array(
                    "Name" => "Belgium",
                    "Alpha2" => "BE",
                    "Alpha3" => "BEL",
                    "IDC" => 32,
                )
            )
        );
    }

    /**
     * Asserts that the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidCreationFieldData()}
     * was stored and loaded correctly.
     *
     * @param Field $field
     * @return void
     */
    public function assertFieldDataLoadedCorrect( Field $field )
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\Country\\Value',
            $field->value
        );

        $expectedData = array(
            "countries" => array(
                "BE" => array(
                    "Name" => "Belgium",
                    "Alpha2" => "BE",
                    "Alpha3" => "BEL",
                    "IDC" => 32,
                )
            )
        );

        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );
    }

    /**
     * Get field data which will result in errors during creation
     *
     * This is a PHPUnit data provider.
     *
     * The returned records must contain of an error producing data value and
     * the expected exception class (from the API or SPI, not implementation
     * specific!) as the second element. For example:
     *
     * <code>
     * array(
     *      array(
     *          new DoomedValue( true ),
     *          'eZ\\Publish\\API\\Repository\\Exceptions\\ContentValidationException'
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array[]
     */
    public function provideInvalidCreationFieldData()
    {
        return array(
            array(
                'Sindelfingen',
                'eZ\\Publish\\API\\Repository\\Exceptions\\InvalidArgumentException'
            ),
            array(
                array( "NON_VALID_ALPHA2_CODE"  ),
                'eZ\\Publish\\API\\Repository\\Exceptions\\InvalidArgumentException'
            ),
            array(
                array( "BE", "FR"  ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\ContentFieldValidationException'
            ),
            array(
                new CountryValue(
                    array(
                        "NON_VALID_ALPHA2_CODE" => array(
                            "Name" => "Belgium",
                            "Alpha2" => "BE",
                            "Alpha3" => "BEL",
                            "IDC" => 32,
                        )
                    )
                ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\ContentFieldValidationException'
            ),
        );
    }

    /**
     * Get update field externals data
     *
     * @return array
     */
    public function getValidUpdateFieldData()
    {
        return new CountryValue(
            array(
                "FR" => array(
                    "Name" => "France",
                    "Alpha2" => "FR",
                    "Alpha3" => "FRA",
                    "IDC" => 33,
                )
            )
        );
    }

    /**
     * Get externals updated field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function assertUpdatedFieldDataLoadedCorrect( Field $field )
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\Country\\Value',
            $field->value
        );

        $expectedData = array(
            "countries" => array(
                "FR" => array(
                    "Name" => "France",
                    "Alpha2" => "FR",
                    "Alpha3" => "FRA",
                    "IDC" => 33,
                )
            )
        );
        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );
    }

    /**
     * Get field data which will result in errors during update
     *
     * This is a PHPUnit data provider.
     *
     * The returned records must contain of an error producing data value and
     * the expected exception class (from the API or SPI, not implementation
     * specific!) as the second element. For example:
     *
     * <code>
     * array(
     *      array(
     *          new DoomedValue( true ),
     *          'eZ\\Publish\\API\\Repository\\Exceptions\\ContentValidationException'
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array[]
     */
    public function provideInvalidUpdateFieldData()
    {
        return $this->provideInvalidCreationFieldData();
    }

    /**
     * Asserts the the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidCreationFieldData()}
     * was copied and loaded correctly.
     *
     * @param Field $field
     */
    public function assertCopiedFieldDataLoadedCorrectly( Field $field )
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\Country\\Value',
            $field->value
        );

        $expectedData = array(
            "countries" => array(
                "BE" => array(
                    "Name" => "Belgium",
                    "Alpha2" => "BE",
                    "Alpha3" => "BEL",
                    "IDC" => 32,
                )
            )
        );
        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );
    }

    /**
     * Get data to test to hash method
     *
     * This is a PHPUnit data provider
     *
     * The returned records must have the the original value assigned to the
     * first index and the expected hash result to the second. For example:
     *
     * <code>
     * array(
     *      array(
     *          new MyValue( true ),
     *          array( 'myValue' => true ),
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array
     */
    public function provideToHashData()
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
                        ),
                        "FR" => array(
                            "Name" => "France",
                            "Alpha2" => "FR",
                            "Alpha3" => "FRA",
                            "IDC" => 33,
                        )
                    )
                ),
                array( 'BE', 'FR' ),
            ),
        );
    }

    /**
     * Get expectations for the fromHash call on our field value
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function provideFromHashData()
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
                ),
            ),
        );
    }

    public function providerForTestIsEmptyValue()
    {
        return array(
            array( new CountryValue ),
            array( new CountryValue( array() ) ),
        );
    }

    public function providerForTestIsNotEmptyValue()
    {
        return array(
            array(
                $this->getValidCreationFieldData()
            ),
        );
    }
}
