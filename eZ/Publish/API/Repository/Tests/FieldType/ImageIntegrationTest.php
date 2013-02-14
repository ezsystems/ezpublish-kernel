<?php
/**
 * File contains: eZ\Publish\API\Repository\Tests\FieldType\ImageIntegrationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;

use eZ\Publish\Core\FieldType\Image\Value as ImageValue;
use eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Integration test for use field type
 *
 * @group integration
 * @group field-type
 */
class ImageIntegrationTest extends FileBaseIntegrationTest
{
    /**
     * Stores the loaded image path for copy test.
     */
    protected static $loadedImagePath;

    /**
     * Storage dir settings key
     */
    protected static $storageDirConfigKey = 'image_storage_dir';

    /**
     * Sets up fixture data.
     *
     * @return void
     */
    protected function getFixtureData()
    {
        return array(
            'create' => array(
                'fileName' => 'Icy-Night-Flower.jpg',
                'path' => ( $path = __DIR__ . '/_fixtures/image.jpg' ),
                'alternativeText' => 'My icy flower at night',
                'fileSize' => filesize( $path ),
            ),
            'update' => array(
                'fileName' => 'Blue-Blue-Blue.png',
                'path' => ( $path = __DIR__ . '/_fixtures/image.png' ),
                'alternativeText' => 'Such a blue …',
                'fileSize' => filesize( $path ),
            ),
        );
    }

    /**
     * Get name of tested field type
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezimage';
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
        return array(
            'FileSizeValidator' => array(
                'maxFileSize' => array(
                    'type'    => 'int',
                    'default' => false,
                ),
            )
        );
    }

    /**
     * Get a valid $validatorConfiguration
     *
     * @return mixed
     */
    public function getValidValidatorConfiguration()
    {
        return array(
            'FileSizeValidator' => array(
                'maxFileSize' => 2 * 1024 * 1024, // 2 MB
            ),
        );
    }

    /**
     * Get $validatorConfiguration not accepted by the field type
     *
     * @return mixed
     */
    public function getInvalidValidatorConfiguration()
    {
        return array(
            'StringLengthValidator' => array(
                'minStringLength' => new \stdClass(),
            )
        );
    }

    /**
     * Get initial field data for valid object creation
     *
     * @return mixed
     */
    public function getValidCreationFieldData()
    {
        $fixtureData = $this->getFixtureData();
        return new ImageValue( $fixtureData['create'] );
    }

    /**
     * Asserts that the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidCreationFieldData()}
     * was stored and loaded correctly.
     *
     * @param Field $field
     *
     * @return void
     */
    public function assertFieldDataLoadedCorrect( Field $field )
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\Image\\Value',
            $field->value
        );

        $fixtureData = $this->getFixtureData();
        $expectedData = $fixtureData['create'];

        // Will change during storage
        unset( $expectedData['path'] );

        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );

        $this->assertTrue(
            file_exists( $this->getInstallDir() . '/' . $field->value->path )
        );

        self::$loadedImagePath = $field->value->path;
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
                array(),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType',
            ),
            array(
                new ImageValue( array() ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType',
            ),
            array(
                new ImageValue(
                    array(
                        'path' => __DIR__ . '/_fixtures/image.jpg',
                    )
                ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType',
            ),
            array(
                new ImageValue(
                    array(
                        'path' => __DIR__ . '/_fixtures/image.jpg',
                        'fileName' => __DIR__ . '/_fixtures/image.jpg',
                    )
                ),
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
        $fixtureData = $this->getFixtureData();
        return new ImageValue( $fixtureData['update'] );
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
            'eZ\\Publish\\Core\\FieldType\\Image\\Value',
            $field->value
        );

        $fixtureData = $this->getFixtureData();
        $expectedData = $fixtureData['update'];
        // Will change during storage
        unset( $expectedData['path'] );

        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );

        $this->assertTrue(
            file_exists( $this->getInstallDir() . '/' . $field->value->path )
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
        $this->assertFieldDataLoadedCorrect( $field );

        $this->assertEquals(
            self::$loadedImagePath,
            $field->value->path
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
        $fixture = $this->getFixtureData();
        return array(
            array(
                $this->getValidCreationFieldData(),
                $fixture['create'],
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
        $fixture = $this->getFixtureData();
        return array(
            array(
                $fixture['create'],
                $this->getValidCreationFieldData()
            ),
        );
    }

    public function testInherentCopyForNewLanguage()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();

        $type = $this->createContentType(
            $this->getValidFieldSettings(),
            $this->getValidValidatorConfiguration(),
            array(),
            // Causes a copy of the image value for each language in legacy
            // storage
            array( 'isTranslatable' => false )
        );

        $draft = $this->createContent( $this->getValidCreationFieldData(), $type );

        $updateStruct = $contentService->newContentUpdateStruct();
        $updateStruct->initialLanguageCode = 'ger-DE';
        $updateStruct->setField( 'name', 'Sindelfingen' );

        // Automatically creates a copy of the image field in the back ground
        $updatedDraft = $contentService->updateContent( $draft->versionInfo, $updateStruct );

        $paths = array();
        foreach ( $updatedDraft->getFields() as $field )
        {
            if ( $field->fieldDefIdentifier === 'data' )
            {
                $paths[$field->languageCode] = $field->value->path;
            }
        }

        $this->assertTrue( isset( $paths['eng-US'] ) && isset( $paths['ger-DE'] ) );
        $this->assertEquals(
            $paths['eng-US'],
            $paths['ger-DE']
        );

        $contentService->deleteContent( $updatedDraft->contentInfo );
    }

    public function providerForTestIsEmptyValue()
    {
        return array(
            array( new ImageValue ),
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
