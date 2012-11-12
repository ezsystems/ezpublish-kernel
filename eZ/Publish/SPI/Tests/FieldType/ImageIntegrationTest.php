<?php
/**
 * File contains: eZ\Publish\SPI\Tests\FieldType\ImageIntegrationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Tests\FieldType;
use eZ\Publish\Core\Persistence\Legacy,
    eZ\Publish\Core\FieldType,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\Field;

/**
 * Integration test for legacy storage field types
 *
 * This abstract base test case is supposed to be the base for field type
 * integration tests. It basically calls all involved methods in the field type 
 * ``Converter`` and ``Storage`` implementations. Fo get it working implement
 * the abstract methods in a sensible way.
 *
 * The following actions are performed by this test using the custom field
 * type:
 *
 * - Create a new content type with the given field type
 * - Load create content type
 * - Create content object of new content type
 * - Load created content
 * - Copy created content
 * - Remove copied content
 *
 * @group integration
 */
class ImageIntegrationTest extends FileBaseIntegrationTest
{
    /**
     * Returns the storage dir used by the file service
     *
     * @return string
     */
    protected function getStorageDir()
    {
        return '';
    }

    /**
     * Returns the storage identifier prefix used by the file service
     *
     * @return void
     */
    protected function getStorageIdentifierPrefix()
    {
        return'var/my_site/storage/images';
    }

    /**
     * Get name of tested field tyoe
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezimage';
    }

    /**
     * Get handler with required custom field types registered
     *
     * @return Handler
     */
    public function getCustomHandler()
    {
        $handler = $this->getHandler();

        $handler->getStorageRegistry()->register(
            'ezimage',
            new FieldType\Image\ImageStorage(
                array(
                    'LegacyStorage' => new FieldType\Image\ImageStorage\Gateway\LegacyStorage(),
                ),
                $this->getFileService(),
                new FieldType\Image\PathGenerator\LegacyPathGenerator()
            )
        );
        $handler->getFieldValueConverterRegistry()->register(
            'ezimage',
            new Legacy\Content\FieldValue\Converter\Image()
        );

        return $handler;
    }

    /**
     * Returns the FieldTypeConstraints to be used to create a field definition
     * of the FieldType under test.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints
     */
    public function getTypeConstraints()
    {
        return new Content\FieldTypeConstraints(
            array(
                'validators' => array(
                    'FileSizeValidator' => array(
                        'maxFileSize' => 2 * 1024 * 1024, // 2 MB
                    )
                )
            )
        );
    }

    /**
     * Get field definition data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getFieldDefinitionData()
    {
        return array(
            // The ezint field type does not have any special field definition
            // properties
            array( 'fieldType', 'ezimage' ),
            array(
                'fieldTypeConstraints',
                new Content\FieldTypeConstraints(
                    array(
                        'validators' => array(
                            'FileSizeValidator' => array(
                                'maxFileSize' => 2 * 1024 * 1024, // 2 MB
                            )
                        )
                    )
                )
            ),
        );
    }

    /**
     * Get initial field value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getInitialValue()
    {
        return new Content\FieldValue( array(
            'data'         => null,
            'externalData' => array(
                'path' => __DIR__ . '/_fixtures/image.jpg',
                'fileName' => 'Ice-Flower.jpg',
                'alternativeText' => 'An icy flower.',
            ),
            'sortKey'      => '',
        ) );
    }

    /**
     * Asserts that the loaded field data is correct
     *
     * Performs assertions on the loaded field, mainly checking that the
     * $field->value->externalData is loaded correctly. If the loading of
     * external data manipulates other aspects of $field, their correctness
     * also needs to be asserted. Make sure you implement this method agnostic
     * to the used SPI\Persistence implementation!
     */
    public function assertLoadedFieldDataCorrect( Field $field )
    {
        $this->assertNotNull( $field->value->data );

        $this->assertTrue(
            file_exists( $this->getTempDir() . '/' . $field->value->data['path'] )
        );

        $this->assertEquals( 'Ice-Flower.jpg', $field->value->data['fileName'] );

        $this->assertEquals( 'An icy flower.', $field->value->data['alternativeText'] );

        $this->assertNull( $field->value->externalData );
    }

    /**
     * Get update field value.
     *
     * Use to update the field
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getUpdatedValue()
    {
        return new Content\FieldValue( array(
            'data'         => null,
            'externalData' => array(
                'path' => __DIR__ . '/_fixtures/image.png',
                'fileName' => 'Blueish-Blue.jpg',
                'alternativeText' => 'This blue is so blueish.',
            ),
            'sortKey'      => '',
        ) );
    }

    /**
     * Asserts that the updated field data is loaded correct
     *
     * Performs assertions on the loaded field after it has been updated,
     * mainly checking that the $field->value->externalData is loaded
     * correctly. If the loading of external data manipulates other aspects of
     * $field, their correctness also needs to be asserted. Make sure you
     * implement this method agnostic to the used SPI\Persistence
     * implementation!
     *
     * @return void
     */
    public function assertUpdatedFieldDataCorrect( Field $field )
    {
        $this->assertNotNull( $field->value->data );

        $this->assertTrue(
            file_exists( ( $filePath = $this->getTempDir() . '/' . $field->value->data['path'] ) )
        );

        // Check old files not removed before update
        // need to stay there for reference integrity
        $this->assertEquals(
            2,
            count( glob( dirname( $filePath ) . '/*' ) )
        );

        $this->assertEquals( 'Blueish-Blue.jpg', $field->value->data['fileName'] );

        $this->assertEquals( 'This blue is so blueish.', $field->value->data['alternativeText'] );

        $this->assertNull( $field->value->externalData );
    }

    /**
     * Can be overwritten to assert that additional data has been deleted
     *
     * @param Content $content
     * @return void
     */
    public function assertDeletedFieldDataCorrect( Content $content )
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->getTempDir() . '/' . $this->getStorageDir(),
                \FileSystemIterator::KEY_AS_PATHNAME | \FileSystemIterator::SKIP_DOTS | \ FilesystemIterator::CURRENT_AS_FILEINFO

            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ( $iterator as $path => $fileInfo )
        {
            if ( $fileInfo->isFile() )
            {
                $this->fail(
                    sprintf(
                        'Found undeleted file "%s"',
                        $path
                    )
                );
            }
        }

    }

    /**
     * @dep_ends \eZ\Publish\SPI\Tests\FieldType\ImageIntegrationTest::testCreateContentType
     */
    public function testImagesNotDeletedIfReferencesStillExist()
    {
        $contentType = $this->createContentType();

        $firstContent = $this->createContent( $contentType, $this->getInitialValue() );

        $firstField = null;
        foreach ( $firstContent->fields as $field )
        {
            if ( $field->type === $this->getTypeName() )
            {
                $firstField = $field;
            }
        }

        $clonedValue = clone $firstField->value;

        // Create an image reference copy
        $secondContent = $this->createContent( $contentType, $clonedValue );

        $secondField = null;
        foreach ( $secondContent->fields as $field )
        {
            if ( $field->type === $this->getTypeName() )
            {
                $secondField = $field;
            }
        }

        $this->assertNotEquals(
            $firstField->value->data['fieldId'],
            $secondField->value->data['fieldId']
        );
        unset( $firstField->value->data['fieldId'] );
        unset( $secondField->value->data['fieldId'] );

        $this->assertEquals( $firstField->value, $secondField->value );

        $this->deleteContent( $firstContent );

        $this->assertTrue(
            file_exists( $this->getTempDir() . '/' . $secondField->value->data['path'] )
        );

        $this->deleteContent( $secondContent );

        $this->assertFalse(
            file_exists( $this->getTempDir() . '/' . $secondField->value->data['path'] )
        );
    }
}

