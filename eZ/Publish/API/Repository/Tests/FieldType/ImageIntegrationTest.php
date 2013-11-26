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
     * PCRE that verifies an imageId
     * @var string
     */
    private $imageIdPCRE = '#[0-9]+-[0-9]+#';

    /**
     * PCRE that verifies a versioned image path, relative to the doc root, without leading /
     *
     * Example: var/ezdemo_site/storage/images-versioned/222/1-eng-US/Icy-Night-Flower.jpg
     *
     * @var string
     */
    private $versionedImagePCRE = '#var/ezdemo_site/storage/images-versioned/[0-9]+/[0-9]+-[a-z]{3}-[A-Z]{2}/[a-zA-Z\-]+\.[a-z]{3,4}#';

    /**
     * PCRE that verifies a published image path, relative to the doc root, without leading /
     *
     * Example: var/ezdemo_site/storage/images/my_icy_flower/222-1-eng-US/Icy-Night-Flower.jpg
     *
     * @var string
     */
    private $publishedImagePCRE = '#var/ezdemo_site/storage/images/([a-zA-Z0-9_]+/)*[0-9]+-[0-9]+-[a-z]{3}-[A-Z]{2}/[a-zA-Z\-]+\.[a-z]{3,4}#';

    /**
     * Stores the loaded image path for copy test.
     */
    protected static $loadedImagePath;

    /**
     * IOService storage prefix for the tested Type's files
     * @var string
     */
    protected static $storagePrefixConfigKey = 'image_storage_prefix';

    protected function getStoragePrefix()
    {
        return $this->getConfigValue( self::$storagePrefixConfigKey );
    }

    /**
     * Sets up fixture data.
     *
     * @return array
     */
    protected function getFixtureData()
    {
        return array(
            'create' => array(
                'fileName' => 'Icy-Night-Flower.jpg',
                'id' => ( $path = __DIR__ . '/_fixtures/image.jpg' ),
                'alternativeText' => 'My icy flower at night',
                'fileSize' => filesize( $path ),
                'imageId' => null,
            ),
            'update' => array(
                'fileName' => 'Blue-Blue-Blue.png',
                'id' => ( $path = __DIR__ . '/_fixtures/image.png' ),
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
        unset( $expectedData['id'], $expectedData['imageId'] );

        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );

        self::assertTrue(
            (bool)preg_match(
                $this->versionedImagePCRE,
                $field->value->id
            ),
            "Failed asserting that {$field->value->id} matches expected versioned image path format {$this->versionedImagePCRE}"
        );

        self::assertTrue(
            (bool)preg_match(
                $this->imageIdPCRE,
                $field->value->imageId
            ),
            "Failed asserting that {$field->value->imageId} matches expected imageId format {$this->imageIdPCRE}"
        );

        /**
         * Disabled.
         * See explanation in eZ\Publish\API\Repository\Tests\FieldType\BinaryFileIntegrationTest::assertFileDataLoadedCorrect()
         */
        /*$this->assertTrue(
            $exists = file_exists( $path = $this->getInstallDir() . '/' . $field->value->id ),
            "Asserting that $path exists."
        );*/

        self::$loadedImagePath = $field->value->id;
    }

    public function assertPublishedFieldDataLoadedCorrect( Field $field )
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\Image\\Value',
            $field->value
        );

        $fixtureData = $this->getFixtureData();
        $expectedData = $fixtureData['create'];

        // Will change during storage
        unset( $expectedData['id'], $expectedData['imageId'] );

        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );

        // @todo Since API integration tests create content without a node, this path is actually incomplete, since
        // it may not contain a node_path_string
        self::assertTrue(
            (bool)preg_match(
                $this->publishedImagePCRE,
                $field->value->id
            ),
            "Failed asserting that {$field->value->id} matches expected published image path format {$this->publishedImagePCRE}"
        );

        self::assertTrue(
            (bool)preg_match(
                $this->imageIdPCRE,
                $field->value->imageId
            ),
            "Failed asserting that {$field->value->imageId} matches expected imageId format {$this->imageIdPCRE}"
        );

        /**
         * Disabled.
         * See explanation in eZ\Publish\API\Repository\Tests\FieldType\BinaryFileIntegrationTest::assertFileDataLoadedCorrect()
         */
        /*$this->assertTrue(
            $exists = file_exists( $path = $this->getInstallDir() . '/' . $field->value->id ),
            "Asserting that $path exists."
        );*/

        self::$loadedImagePath = $field->value->id;
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
                new ImageValue(
                    array(
                        'id' => __DIR__ . '/_fixtures/image.jpg',
                    )
                ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new ImageValue(
                    array(
                        'id' => __DIR__ . '/_fixtures/image.jpg',
                        'fileName' => __DIR__ . '/_fixtures/image.jpg',
                    )
                ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
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
        unset( $expectedData['id'] );

        $expectedData['uri'] = $field->value->uri;

        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );

        /**
         * Disabled.
         * See explanation in eZ\Publish\API\Repository\Tests\FieldType\BinaryFileIntegrationTest::assertFileDataLoadedCorrect()
         */
        /*$this->assertTrue(
            file_exists( $path = $this->getInstallDir() . '/' . $field->value->id ),
            "Asserting that file $path exists"
        );*/
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
        $this->assertPublishedFieldDataLoadedCorrect( $field );

        $this->assertEquals(
            self::$loadedImagePath,
            $field->value->id
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

        // the URI is generated by the FieldType storage
        $fixture['create']['uri'] = $fixture['create']['id'];
        $fixture['create']['path'] = $fixture['create']['id'];

        $fieldValue = $this->getValidCreationFieldData();
        $fieldValue->uri = $fixture['create']['uri'];

        return array(
            array(
                $fieldValue,
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

    /**
     * Tests that copying a Content with an image field will re-use the
     * image from the copied field as a reference instead of copying it.
     */
    public function testInherentCopyForNewLanguage()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

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
                $paths[$field->languageCode] = $field->value->id;
            }
        }

        $this->assertTrue(
            isset( $paths['eng-US'] ) && isset( $paths['ger-DE'] ),
            "Failed asserting that file path for all languages were found in draft"
        );

        $this->assertEquals(
            $paths['eng-US'],
            $paths['ger-DE']
        );

        $contentService->deleteContent( $updatedDraft->contentInfo );
    }

    /**
     * Creates an image content, publishes it, and creates a second version without changing the image
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function createContentWithNewVersion()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $type = $this->createContentType(
            $this->getValidFieldSettings(),
            $this->getValidValidatorConfiguration(),
            array(),
            // Causes a copy of the image value for each language in legacy
            // storage
            array( 'isTranslatable' => false )
        );

        // Create content with image in version 1
        $version1Draft = $this->createContent( $this->getValidCreationFieldData(), $type );
        $content = $contentService->publishVersion( $version1Draft->versionInfo );

        // Update content to version 2  without changing the image
        $version2Draft = $contentService->createContentDraft( $content->contentInfo );
        $updateStruct = $contentService->newContentUpdateStruct();
        $updateStruct->setField( 'name', __METHOD__ );
        $updatedDraft = $contentService->updateContent( $version2Draft->versionInfo, $updateStruct );
        $content = $contentService->publishVersion( $updatedDraft->versionInfo );

        // Since the image wasn't changed, the URI in V2 must be the same as V1
        self::assertEquals(
            $this->testCreatedFieldType(
                $contentService->loadContent( $content->id, null, 1 )
            )->value->uri,
            $this->testCreatedFieldType(
                $contentService->loadContent( $content->id, null, 1 )
            )->value->uri,
            " image uri in version 2 is identical to version 1"
        );

        return $content;
    }

    /**
     * Tests behaviour when an image content is edited without the image being changed
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content The content copy
     */
    public function testInherentCopyForNewVersion()
    {
        $contentService = $this->getRepository()->getContentService();

        $content = $this->createContentWithNewVersion();

        // copy this content to a new one
        $contentCopy = $contentService->copyContent(
            $content->contentInfo,
            $this->getRepository()->getLocationService()->newLocationCreateStruct( 43 )
        );

        /*self::assertNotEquals(
            $this->testCreatedFieldType( $content )->value->uri,
            $this->testCreatedFieldType( $contentCopy )->value->uri,
            " published image uri in content copy is different from the source object's"
        );*/

        return $contentCopy;
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
