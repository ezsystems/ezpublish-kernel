<?php

/**
 * File contains: eZ\Publish\SPI\Tests\FieldType\ImageIntegrationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\Core\Persistence\Legacy;
use eZ\Publish\Core\IO;
use eZ\Publish\Core\FieldType;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Field;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FileSystemIterator;

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
class ImageIntegrationTest extends FileBaseIntegrationTest
{
    private $deprecationWarnerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $aliasCleanerMock;

    /**
     * Returns the storage identifier prefix used by the file service.
     *
     * @return string
     */
    protected function getStoragePrefix()
    {
        return self::$container->getParameter('image_storage_prefix');
    }

    /**
     * Get name of tested field type.
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezimage';
    }

    /**
     * Get handler with required custom field types registered.
     *
     * @return \eZ\Publish\SPI\Persistence\Handler
     */
    public function getCustomHandler()
    {
        $fieldType = new FieldType\Image\Type();
        $fieldType->setTransformationProcessor($this->getTransformationProcessor());

        $this->ioService = self::$container->get('ezpublish.fieldType.ezimage.io_service');
        /** @var \eZ\Publish\Core\IO\UrlRedecoratorInterface $urlRedecorator */
        $urlRedecorator = self::$container->get('ezpublish.core.io.image_fieldtype.legacy_url_redecorator');

        return $this->getHandler(
            'ezimage',
            $fieldType,
            new Legacy\Content\FieldValue\Converter\ImageConverter($this->ioService, $urlRedecorator),
            new FieldType\Image\ImageStorage(
                new FieldType\Image\ImageStorage\Gateway\DoctrineStorage(
                    $urlRedecorator,
                    $this->getDatabaseHandler()->getConnection()
                ),
                $this->ioService,
                new FieldType\Image\PathGenerator\LegacyPathGenerator(),
                new IO\MetadataHandler\ImageSize(),
                $this->getDeprecationWarnerMock(),
                $this->getAliasCleanerMock()
            )
        );
    }

    public function getDeprecationWarnerMock()
    {
        if (!isset($this->deprecationWarnerMock)) {
            $this->deprecationWarnerMock = $this->createMock('eZ\Publish\Core\Base\Utils\DeprecationWarnerInterface');
        }

        return $this->deprecationWarnerMock;
    }

    public function getAliasCleanerMock()
    {
        if (!isset($this->aliasCleanerMock)) {
            $this->aliasCleanerMock = $this->createMock('\eZ\Publish\Core\FieldType\Image\AliasCleanerInterface');
        }

        return $this->aliasCleanerMock;
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
                    ),
                ),
            )
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
        return array(
            // The ezint field type does not have any special field definition
            // properties
            array('fieldType', 'ezimage'),
            array(
                'fieldTypeConstraints',
                new Content\FieldTypeConstraints(
                    array(
                        'validators' => array(
                            'FileSizeValidator' => array(
                                'maxFileSize' => 2 * 1024 * 1024, // 2 MB
                            ),
                        ),
                    )
                ),
            ),
        );
    }

    /**
     * Get initial field value.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getInitialValue()
    {
        return new Content\FieldValue(
            array(
                'data' => null,
                'externalData' => array(
                    'inputUri' => ($path = __DIR__ . '/_fixtures/image.jpg'),
                    'fileName' => 'Ice-Flower.jpg',
                    'alternativeText' => 'An icy flower.',
                ),
                'sortKey' => '',
            )
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
        $this->assertNotNull($field->value->data);

        $this->assertIOUriExists($field->value->data['uri']);
        $this->assertEquals('Ice-Flower.jpg', $field->value->data['fileName']);
        $this->assertEquals('An icy flower.', $field->value->data['alternativeText']);
        $this->assertNull($field->value->externalData);
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
                'data' => null,
                'externalData' => array(
                    // should be ignored
                    'id' => 'some/value',
                    'inputUri' => ($path = __DIR__ . '/_fixtures/image.png'),
                    'fileName' => 'Blueish-Blue.jpg',
                    'alternativeText' => 'This blue is so blueish.',
                ),
                'sortKey' => '',
            )
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
        $this->assertNotNull($field->value->data);
        $this->assertIOUriExists($field->value->data['uri']);

        $this->assertEquals('Blueish-Blue.jpg', $field->value->data['fileName']);
        $this->assertEquals('This blue is so blueish.', $field->value->data['alternativeText']);
        $this->assertNull($field->value->externalData);
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

        // @todo This will fail since updating content without publishing a new version isn't supposed to be supported
        // we end up with two images in the attribute's folder, one of which isn't referenced anywhere
        /*foreach ( $iterator as $path => $fileInfo )
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
        }*/
    }

    /**
     * @expectedException \eZ\Publish\Core\IO\Exception\InvalidBinaryFileIdException
     */
    public function testCreateContentUsingIdPropertyThrowsWarning()
    {
        $this->testCreateContentType();
        $contentType = $this->testLoadContentTypeField();
        $this->getDeprecationWarnerMock()
            ->expects($this->never())
            ->method('log');

        $this->createContent($contentType, $this->getDeprecatedIdPropertyValue());
    }

    /**
     * Get initial field value.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getDeprecatedIdPropertyValue()
    {
        return new Content\FieldValue(
            array(
                'data' => null,
                'externalData' => array(
                    'id' => ($path = __DIR__ . '/_fixtures/image.jpg'),
                    'fileName' => 'Ice-Flower.jpg',
                    'alternativeText' => 'An icy flower.',
                ),
                'sortKey' => '',
            )
        );
    }

    /**
     * Overridden to take into account that image moves externaldata to data, unlike BinaryBase.
     *
     * @param $content
     *
     * @return mixed
     */
    protected function deleteStoredFile($content)
    {
        return $this->ioService->deleteBinaryFile(
            $this->ioService->loadBinaryFile($content->fields[1]->value->data['id'])
        );
    }
}
