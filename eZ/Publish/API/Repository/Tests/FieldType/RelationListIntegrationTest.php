<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\RepositoryTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;
use eZ\Publish\API\Repository,
    eZ\Publish\Core\FieldType\RelationList\Value as RelationListValue,
    eZ\Publish\Core\FieldType\RelationList\Type as RelationListType,
    eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Integration test for use field type
 *
 * @group integration
 * @group field-type
 */
class RelationListFieldTypeIntegrationTest extends BaseIntegrationTest
{
    /**
     * Get name of tested field tyoe
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezobjectrelationlist';
    }

    /**
     * @see eZ\Publish\API\Repository\Tests\FieldType\BaseIntegrationTest::getSettingsSchema()
     */
    public function getSettingsSchema()
    {
        return array(
            'selectionMethod' => array(
                'type' => 'int',
                'default' => RelationListType::SELECTION_BROWSE,
            ),
            'selectionDefaultLocation' => array(
                'type' => 'string',
                'default' => null,
            ),
            'selectionContentTypes' => array(
                'type' => 'array',
                'default' => array(),
            ),
        );
    }

    /**
     * @see eZ\Publish\API\Repository\Tests\FieldType\BaseIntegrationTest::getValidatorSchema()
     */
    public function getValidatorSchema()
    {
        return array();
    }

    /**
     * Get a valid $fieldSettings value
     *
     * @return mixed
     * @TODO Implement correctly
     */
    public function getValidFieldSettings()
    {
        return array(
            'selectionMethod' => 1,
            'selectionDefaultLocation' => '2',
            'selectionContentTypes' => array( 'blog_post' ),
        );
    }

    /**
     * Get a valid $validatorConfiguration
     *
     * @return mixed
     * @TODO Implement correctly
     */
    public function getValidValidatorConfiguration()
    {
        return array();
    }

    /**
     * Get $fieldSettings value not accepted by the field type
     *
     * @return mixed
     * @TODO Implement correctly
     */
    public function getInvalidFieldSettings()
    {
        return array( 'selectionMethod' => 'a', 'selectionDefaultLocation' => true, 'unknownSetting' => false );
    }

    /**
     * Get $validatorConfiguration not accepted by the field type
     *
     * @return mixed
     * @TODO Implement correctly
     */
    public function getInvalidValidatorConfiguration()
    {
        return array( 'noValidator' => true );
    }

    /**
     * Get initial field data for valid object creation
     *
     * @return mixed
     */
    public function getValidCreationFieldData()
    {
        return new RelationListValue( array( 4 ) );
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
    public function assertFieldDataLoadedCorrect( Field $field)
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\RelationList\\Value',
            $field->value
        );

        $expectedData = array(
            'destinationContentIds' => array( 4 ),
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
                new RelationListValue( array( null ) ),
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
        return new RelationListValue( array( 49 ) );
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
            'eZ\\Publish\\Core\\FieldType\\RelationList\\Value',
            $field->value
        );

        $expectedData = array(
            'destinationContentIds' => array( 49 ),
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
            'eZ\\Publish\\Core\\FieldType\\RelationList\\Value',
            $field->value
        );

        $expectedData = array(
            'destinationContentIds' => array( 4 )
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
                new RelationListValue( array( 4 ) ),
                array(
                    'destinationContentIds' => array( 4 ),
                )
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
                array( 'destinationContentIds' => array( 4 ) ),
                new RelationListValue( array( 4 ) )
            ),
        );
    }

    public function providerForTestIsEmptyValue()
    {
        return array(
            array( new RelationListValue ),
            array( new RelationListValue( array() ) ),
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
