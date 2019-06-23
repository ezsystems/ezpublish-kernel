<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\HandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
use eZ\Publish\Core\IO\MimeTypeDetector\FileInfo;

/**
 * Integration test for legacy storage field types.
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
     * Returns the storage identifier prefix used by the file service.
     *
     * @return string
     */
    protected function getStoragePrefix()
    {
        return self::$container->getParameter('binaryfile_storage_prefix');
    }

    /**
     * Get name of tested field type.
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezbinaryfile';
    }

    /**
     * Get handler with required custom field types registered.
     *
     * @return \eZ\Publish\SPI\Persistence\Handler
     */
    public function getCustomHandler()
    {
        $fieldType = new FieldType\BinaryFile\Type();
        $fieldType->setTransformationProcessor($this->getTransformationProcessor());

        $this->ioService = self::$container->get('ezpublish.fieldType.ezbinaryfile.io_service');

        return $this->getHandler(
            'ezbinaryfile',
            $fieldType,
            new Legacy\Content\FieldValue\Converter\BinaryFileConverter(),
            new FieldType\BinaryFile\BinaryFileStorage(
                new FieldType\BinaryFile\BinaryFileStorage\Gateway\LegacyStorage($this->getDatabaseHandler()),
                $this->ioService,
                new FieldType\BinaryBase\PathGenerator\LegacyPathGenerator(),
                new FileInfo()
            )
        );
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
            [
                'validators' => [
                    'FileSizeValidator' => [
                        'maxFileSize' => 2, // 2 MB
                    ],
                ],
            ]
        );
    }

    /**
     * Get field definition data values.
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getFieldDefinitionData()
    {
        return [
            // The ezint field type does not have any special field definition
            // properties
            ['fieldType', 'ezbinaryfile'],
            [
                'fieldTypeConstraints',
                new FieldTypeConstraints(
                    [
                        'validators' => [
                            'FileSizeValidator' => [
                                'maxFileSize' => 2, // 2 MB
                            ],
                        ],
                    ]
                ),
            ],
        ];
    }

    /**
     * Get initial field value.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getInitialValue()
    {
        return new Content\FieldValue(
            [
                'data' => null,
                'externalData' => [
                    'id' => null,
                    'inputUri' => ($path = __DIR__ . '/_fixtures/image.jpg'),
                    'fileName' => 'Ice-Flower-Binary.jpg',
                    'fileSize' => filesize($path),
                    'mimeType' => 'image/jpeg',
                    'downloadCount' => 0,
                    'uri' => __DIR__ . '/_fixtures/image.jpg',
                ],
                'sortKey' => '',
            ]
        );
    }

    /**
     * Asserts that the loaded field data is correct.
     *
     * Performs assertions on the loaded field, mainly checking that the
     * $field->value->externalData is loaded correctly. If the loading of
     * external data manipulates other aspects of $field, their correctness
     * also needs to be asserted. Make sure you implement this method agnostic
     * to the used SPI\Persistence implementation!
     */
    public function assertLoadedFieldDataCorrect(Field $field)
    {
        $this->assertNotNull($field->value->externalData);

        $this->assertIOIdExists($field->value->externalData['id']);

        $this->assertEquals('Ice-Flower-Binary.jpg', $field->value->externalData['fileName']);
        $this->assertEquals($this->getFilesize($field->value->externalData['id']), $field->value->externalData['fileSize']);
        $this->assertEquals('image/jpeg', $field->value->externalData['mimeType']);
        $this->assertEquals(0, $field->value->externalData['downloadCount']);

        $this->assertNull($field->value->data);
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
            [
                'data' => null,
                'externalData' => [
                    // used to ensure that inputUri has precedence over 'id'
                    'id' => 'some/value',
                    'inputUri' => ($path = __DIR__ . '/_fixtures/image.png'),
                    'fileName' => 'Blueish-Blue-Binary.jpg',
                    'fileSize' => filesize($path),
                    // on purpuse wrong, as it should be ignored by storage
                    'mimeType' => 'foo/bar',
                    'downloadCount' => 23,
                    'uri' => __DIR__ . '/_fixtures/image.jpg',
                ],
                'sortKey' => '',
            ]
        );
    }

    /**
     * Asserts that the updated field data is loaded correct.
     *
     * Performs assertions on the loaded field after it has been updated,
     * mainly checking that the $field->value->externalData is loaded
     * correctly. If the loading of external data manipulates other aspects of
     * $field, their correctness also needs to be asserted. Make sure you
     * implement this method agnostic to the used SPI\Persistence
     * implementation!
     */
    public function assertUpdatedFieldDataCorrect(Field $field)
    {
        $this->assertNotNull($field->value->externalData);

        $this->assertIOIdExists($field->value->externalData['id']);

        $path = $this->getPathFromId($field->value->externalData['id']);
        // Check old file removed before update
        $this->assertEquals(
            1,
            count(glob(dirname($path) . '/*'))
        );

        $this->assertEquals('Blueish-Blue-Binary.jpg', $field->value->externalData['fileName']);
        $this->assertEquals(filesize($path), $field->value->externalData['fileSize']);
        $this->assertEquals('image/png', $field->value->externalData['mimeType']);
        $this->assertEquals(23, $field->value->externalData['downloadCount']);

        $this->assertNull($field->value->data);
    }

    /**
     * Can be overwritten to assert that additional data has been deleted.
     *
     * @param Content $content
     */
    public function assertDeletedFieldDataCorrect(Content $content)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->getStorageDir(),
                FileSystemIterator::KEY_AS_PATHNAME | FileSystemIterator::SKIP_DOTS | FileSystemIterator::CURRENT_AS_FILEINFO
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $path => $fileInfo) {
            if ($fileInfo->isFile()) {
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
