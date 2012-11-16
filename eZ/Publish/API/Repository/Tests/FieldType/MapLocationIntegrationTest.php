<?php
/**
 * File contains: eZ\Publish\API\Repository\Tests\FieldType\MapLocationIntegrationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;
use eZ\Publish\Core\FieldType\MapLocation\Value as MapLocationValue,
    eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Integration test for use field type
 *
 * @group integration
 * @group field-type
 */
class MapLocationIntegrationTest extends BaseIntegrationTest
{
    /**
     * Get name of tested field tyoe
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezgmaplocation';
    }

    /**
     * Get expected settings schema
     *
     * @return array
     */
    public function getSettingsSchema()
    {
        return array();
    }

    /**
     * Get a valid $fieldSettings value
     *
     * @return mixed
     */
    public function getValidFieldSettings()
    {
        return array();
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
            'unkknown' => array( 'value' => 23 )
        );
    }

    /**
     * Get initial field data for valid object creation
     *
     * @return mixed
     */
    public function getValidCreationFieldData()
    {
        return new MapLocationValue( array(
            'latitude' => 51.559997,
            'longitude' => 6.767921,
            'address' => 'Bielefeld',
        ) );
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
        $this->assertEquals(
            $this->getValidCreationFieldData(),
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
                new \stdClass(),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType',
            ),
            array(
                23,
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType',
            ),
            array(
                new MapLocationValue( array(
                    'latitude' => 'string'
                ) ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType',
            ),
            array(
                new MapLocationValue( array(
                    'latitude' => 23.42,
                    'longitude' => 'invalid',
                ) ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType',
            ),
            array(
                new MapLocationValue( array(
                    'latitude' => 23.42,
                    'longitude' => 42.23,
                    'address' => true,
                ) ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType',
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
        // https://maps.google.de/maps?qll=,&spn=0.139491,0.209942&sll=51.983611,8.574829&sspn=0.36242,0.839767&oq=Punta+Cana&t=h&hnear=Punta+Cana,+La+Altagracia,+Dominikanische+Republik&z=13
        return new MapLocationValue( array(
            'latitude' => 18.524701,
            'longitude' => -68.363113,
            'address' => 'Punta Cana',
        ) );
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
        $this->assertEquals(
            $this->getValidUpdateFieldData(),
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
        $this->assertEquals(
            $this->getValidCreationFieldData(),
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
                new MapLocationValue( array(
                    'latitude' => 51.559997,
                    'longitude' => 6.767921,
                    'address' => 'Bielefeld',
                ) ),
                array(
                    'latitude' => 51.559997,
                    'longitude' => 6.767921,
                    'address' => 'Bielefeld',
                ),
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
                array(
                    'latitude' => 51.559997,
                    'longitude' => 6.767921,
                    'address' => 'Bielefeld',
                ),
                new MapLocationValue( array(
                    'latitude' => 51.559997,
                    'longitude' => 6.767921,
                    'address' => 'Bielefeld',
                ) )
            ),
        );
    }

    public function providerForTestIsEmptyValue()
    {
        return array(
            array( new MapLocationValue ),
            array(
                new MapLocationValue(
                    array(
                        'latitude' => null,
                        'longitude' => null,
                    )
                )
            )
        );
    }

    public function providerForTestIsNotEmptyValue()
    {
        return array(
            array(
                $this->getValidCreationFieldData()
            ),
            array(
                new MapLocationValue(
                    array(
                        'latitude' => 0,
                        'longitude' => 0,
                    )
                )
            )
        );
    }
}
