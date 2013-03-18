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
class MediaIntegrationTest extends FileBaseIntegrationTest
{
    /**
     * Returns the storage dir used by the file service
     *
     * @return string
     */
    protected function getStorageDir()
    {
        return self::$storageDir;
    }

    /**
     * Returns the storage identifier prefix used by the file service
     *
     * @return void
     */
    protected function getStoragePrefix()
    {
        return 'original';
    }

    /**
     * Get name of tested field type
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezmedia';
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
            'ezmedia',
            new FieldType\Media\Type(
                $this->getIOService(),
                $this->getMimeTypeDetector()
            )
        );
        $handler->getStorageRegistry()->register(
            'ezmedia',
            new FieldType\Media\MediaStorage(
                array(
                    'LegacyStorage' => new FieldType\Media\MediaStorage\Gateway\LegacyStorage(),
                ),
                $this->getIOService(),
                new FieldType\BinaryBase\PathGenerator\LegacyPathGenerator(),
                $this->getMimeTypeDetector()
            )
        );
        $handler->getFieldValueConverterRegistry()->register(
            'ezmedia',
            new Legacy\Content\FieldValue\Converter\Media()
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
                        'maxFileSize' => 2 * 1024 * 1024, // 2 MB
                    )
                ),
                'fieldSettings' => new FieldType\FieldSettings(
                    array(
                        'mediaType' => FieldType\Media\Type::TYPE_SILVERLIGHT,
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
            array( 'fieldType', 'ezmedia' ),
            array(
                'fieldTypeConstraints',
                new FieldTypeConstraints(
                    array(
                        'validators' => array(
                            'FileSizeValidator' => array(
                                'maxFileSize' => 2 * 1024 * 1024, // 2 MB
                            )
                        ),
                        'fieldSettings' => new FieldType\FieldSettings(
                            array(
                                'mediaType' => FieldType\Media\Type::TYPE_SILVERLIGHT,
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
                    'fileName' => 'Ice-Flower-Media.jpg',
                    'fileSize' => filesize( $path ),
                    'mimeType' => 'image/jpeg',
                    'hasController' => true,
                    'autoplay' => true,
                    'loop' => true,
                    'width' => 23,
                    'height' => 42,
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
            file_exists( $path = $this->getStorageDir() . '/' . $this->getStoragePrefix() . '/' . $field->value->externalData['path'] ),
            "Stored file $path exists"
        );

        $this->assertEquals( 'Ice-Flower-Media.jpg', $field->value->externalData['fileName'] );
        $this->assertEquals( filesize( $path ), $field->value->externalData['fileSize'] );
        $this->assertEquals( 'image/jpeg', $field->value->externalData['mimeType'] );
        $this->assertEquals( true, $field->value->externalData['hasController'] );
        $this->assertEquals( true, $field->value->externalData['autoplay'] );
        $this->assertEquals( true, $field->value->externalData['loop'] );
        $this->assertEquals( 23, $field->value->externalData['width'] );
        $this->assertEquals( 42, $field->value->externalData['height'] );

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
                    'fileName' => 'Blueish-Blue-Media.jpg',
                    'fileSize' => filesize( $path ),
                    'mimeType' => 'image/png',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => false,
                    'width' => 0,
                    'height' => 0,
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
            file_exists( $path = $this->getStorageDir() . '/' . $this->getStoragePrefix() . '/' . $field->value->externalData['path'] ),
            "Stored file $path exists"
        );

        // Check old file removed before update
        $this->assertEquals(
            1,
            count( glob( dirname( $path ) . '/*' ) )
        );

        $this->assertEquals( 'Blueish-Blue-Media.jpg', $field->value->externalData['fileName'] );
        $this->assertEquals( filesize( $path ), $field->value->externalData['fileSize'] );
        $this->assertEquals( 'image/png', $field->value->externalData['mimeType'] );
        $this->assertEquals( false, $field->value->externalData['hasController'] );
        $this->assertEquals( false, $field->value->externalData['autoplay'] );
        $this->assertEquals( false, $field->value->externalData['loop'] );
        $this->assertEquals( 0, $field->value->externalData['width'] );
        $this->assertEquals( 0, $field->value->externalData['height'] );

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
                $this->getStorageDir(),
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
}
