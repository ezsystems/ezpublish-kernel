<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\HandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\Core\Persistence\Legacy;
use eZ\Publish\Core\FieldType;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FileSystemIterator;

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
class BinaryFileIntegrationTest extends FileBaseIntegrationTest
{
    /**
     * Returns the storage dir used by the file service
     *
     * @return string
     */
    protected function getStorageDir()
    {
        return 'var/files';
    }

    /**
     * Returns the storage identifier prefix used by the file service
     *
     * @return void
     */
    protected function getStorageIdentifierPrefix()
    {
        return '';
    }

    /**
     * Get name of tested field type
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezbinaryfile';
    }

    /**
     * Get handler with required custom field types registered
     *
     * @return Handler
     */
    public function getCustomHandler()
    {
        $handler = $this->getHandler();

        $handler->getFieldTypeRegistry()->register(
            'ezauthor',
            new FieldType\BinaryFile\Type(
                $this->getFileService(),
                $this->getMimeTypeDetector()
            )
        );
        $handler->getStorageRegistry()->register(
            'ezbinaryfile',
            new FieldType\BinaryFile\BinaryFileStorage(
                array(
                    'LegacyStorage' => new FieldType\BinaryFile\BinaryFileStorage\Gateway\LegacyStorage(),
                ),
                $this->getFileService(),
                new FieldType\BinaryBase\PathGenerator\LegacyPathGenerator()
            )
        );
        $handler->getFieldValueConverterRegistry()->register(
            'ezbinaryfile',
            new Legacy\Content\FieldValue\Converter\BinaryFile()
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
        return new FieldTypeConstraints(
            array(
                'validators' => array(
                    'FileSizeValidator' => array(
                        'maxFileSize' => 2, // 2 MB
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
            array( 'fieldType', 'ezbinaryfile' ),
            array(
                'fieldTypeConstraints',
                new FieldTypeConstraints(
                    array(
                        'validators' => array(
                            'FileSizeValidator' => array(
                                'maxFileSize' => 2, // 2 MB
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
        return new Content\FieldValue(
            array(
                'data'         => null,
                'externalData' => array(
                    'path' => ( $path = __DIR__ . '/_fixtures/image.jpg' ),
                    'fileName' => 'Ice-Flower-Binary.jpg',
                    'fileSize' => filesize( $path ),
                    'mimeType' => 'image/jpeg',
                    'downloadCount' => 0,
                ),
                'sortKey'      => '',
            )
        );
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
        $this->assertNotNull( $field->value->externalData );

        $this->assertTrue(
            file_exists( ( $path = $this->getTempDir() . '/' . $this->getStorageDir() . '/' . $field->value->externalData['path'] ) )
        );

        $this->assertEquals( 'Ice-Flower-Binary.jpg', $field->value->externalData['fileName'] );
        $this->assertEquals( filesize( $path ), $field->value->externalData['fileSize'] );
        $this->assertEquals( 'image/jpeg', $field->value->externalData['mimeType'] );
        $this->assertEquals( 0, $field->value->externalData['downloadCount'] );

        $this->assertNull( $field->value->data );
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
        return new Content\FieldValue(
            array(
                'data'         => null,
                'externalData' => array(
                    'path' => ( $path = __DIR__ . '/_fixtures/image.png' ),
                    'fileName' => 'Blueish-Blue-Binary.jpg',
                    'fileSize' => filesize( $path ),
                    'mimeType' => 'image/png',
                    'downloadCount' => 23,
                ),
                'sortKey'      => '',
            )
        );
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
        $this->assertNotNull( $field->value->externalData );

        $this->assertTrue(
            file_exists( ( $filePath = $this->getTempDir() . '/' . $this->getStorageDir() . '/' . $field->value->externalData['path'] ) )
        );

        // Check old file removed before update
        $this->assertEquals(
            1,
            count( glob( dirname( $filePath ) . '/*' ) )
        );

        $this->assertEquals( 'Blueish-Blue-Binary.jpg', $field->value->externalData['fileName'] );
        $this->assertEquals( filesize( $filePath ), $field->value->externalData['fileSize'] );
        $this->assertEquals( 'image/png', $field->value->externalData['mimeType'] );
        $this->assertEquals( 23, $field->value->externalData['downloadCount'] );

        $this->assertNull( $field->value->data );
    }

    /**
     * Can be overwritten to assert that additional data has been deleted
     *
     * @param Content $content
     *
     * @return void
     */
    public function assertDeletedFieldDataCorrect( Content $content )
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->getTempDir() . '/' . $this->getStorageDir(),
                FileSystemIterator::KEY_AS_PATHNAME | FileSystemIterator::SKIP_DOTS | FileSystemIterator::CURRENT_AS_FILEINFO
            ),
            RecursiveIteratorIterator::CHILD_FIRST
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
     * @dep_ends \eZ\Publish\SPI\Tests\FieldType\BinaryFileIntegrationTest::testCreateContentType
     */
    public function testBinaryFilesNotDeletedIfReferencesStillExist()
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
            $firstField->id,
            $secondField->id
        );

        $this->assertEquals( $firstField->value, $secondField->value );

        $this->deleteContent( $firstContent );

        $this->assertTrue(
            file_exists( $this->getTempDir() . '/' . $this->getStorageDir() . '/' . $secondField->value->externalData['path'] )
        );

        $this->deleteContent( $secondContent );

        $this->assertFalse(
            file_exists( $this->getTempDir() . '/' . $this->getStorageDir() . '/' . $secondField->value->externalData['path'] )
        );
    }
}
