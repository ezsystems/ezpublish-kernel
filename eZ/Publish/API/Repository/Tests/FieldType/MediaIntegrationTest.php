<?php
/**
 * File contains: eZ\Publish\API\Repository\Tests\FieldType\MediaIntegrationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;

use eZ\Publish\Core\FieldType\Media\Value as MediaValue;
use eZ\Publish\Core\FieldType\Media\Type as MediaType;
use eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Integration test for use field type
 *
 * @group integration
 * @group field-type
 */
class MediaIntegrationTest extends FileBaseIntegrationTest
{
    /**
     * Stores the loaded image path for copy test.
     */
    protected static $loadedMediaPath;

    /**
     * IOService storage prefix for the tested Type's files
     * @var string
     */
    protected static $storagePrefixConfigKey = 'binaryfile_storage_prefix';

    protected function getStoragePrefix()
    {
        return $this->getConfigValue( self::$storagePrefixConfigKey );
    }

    /**
     * Sets up fixture data.
     *
     * @return void
     */
    protected function getFixtureData()
    {
        return array(
            'create' => array(
                'path' => ( $path = __DIR__ . '/_fixtures/image.jpg' ),
                'fileName' => 'Icy-Night-Flower-Binary.jpg',
                'fileSize' => filesize( $path ),
                'mimeType' => 'image/jpeg',
                // Left out 'hasControlls', 'autoplay', 'loop', 'height' and
                // 'width' by intention (will be set to defaults)
            ),
            'update' => array(
                'path' => ( $path = __DIR__ . '/_fixtures/image.png' ),
                'fileName' => 'Blue-Blue-Blue-Sindelfingen.png',
                'fileSize' => filesize( $path ),
                // Left out 'mimeType' by intention (will be auto-detected)
                'hasController' => true,
                'autoplay' => true,
                'loop' => true,
                'width' => 23,
                'height' => 42,
            ),
        );
    }

    /**
     * Get name of tested field tyoe
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezmedia';
    }

    /**
     * Get expected settings schema
     *
     * @return array
     */
    public function getSettingsSchema()
    {
        return array(
            'mediaType' => array(
                'type' => 'choice',
                'default' => MediaType::TYPE_HTML5_VIDEO,
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
            'mediaType' => MediaType::TYPE_FLASH,
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
        return new MediaValue( $fixtureData['create'] );
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
            'eZ\\Publish\\Core\\FieldType\\Media\\Value',
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
            file_exists( $path = $this->getInstallDir() . '/' . $this->getStorageDir() . '/' . $this->getStoragePrefix() . '/' . $field->value->path ),
            "File $path exists."
        );

        self::$loadedMediaPath = $field->value->path;
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
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentValue',
            ),
            array(
                new MediaValue( array() ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentValue',
            ),
            array(
                array(
                    'path' => '/foo/bar/sindelfingen.pdf',
                ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentValue',
            ),
            array(
                new MediaValue(
                    array(
                        'path' => '/foo/bar/sindelfingen.pdf',
                    )
                ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentValue',
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
        return new MediaValue( $fixtureData['update'] );
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
            'eZ\\Publish\\Core\\FieldType\\Media\\Value',
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
            file_exists( $path = $this->getInstallDir() . '/' . $this->getStorageDir() . '/' . $this->getStoragePrefix() . '/' . $field->value->path ),
            "File $path exists."
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
            self::$loadedMediaPath,
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
        // Defaults set by type
        $fixture['create']['hasController'] = false;
        $fixture['create']['autoplay'] = false;
        $fixture['create']['loop'] = false;
        $fixture['create']['width'] = 0;
        $fixture['create']['height'] = 0;
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

    public function providerForTestIsEmptyValue()
    {
        return array(
            array( new MediaValue ),
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
