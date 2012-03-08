<?php
/**
 * File containing the MediaTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;
use eZ\Publish\Core\Repository\FieldType\Media\Type as MediaType,
    eZ\Publish\Core\Repository\FieldType\Media\Value as MediaTypeValue,
    eZ\Publish\Core\Repository\FieldType\FieldSettings,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Media as MediaTypeConverter,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition,
    eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints,
    eZ\Publish\Core\Repository\Repository,
    eZ\Publish\Core\IO\InMemoryHandler as InMemoryIOHandler,
    eZ\Publish\Core\Persistence\InMemory\Handler as InMemoryPersistenceHandler;

/**
 * Test case for MediaType converter in Legacy storage
 */
class MediaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\MediaType
     */
    protected $converter;

    /**
     * Path to test media
     * @var string
     */
    protected $mediaPath;

    /**
     * Persistence field value to use in tests
     * @var \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    protected $persistenceMediaValue;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new MediaTypeConverter;
        $this->mediaPath = 'ezp/Content/Tests/FieldType/developer-got-hurt.m4v';

        $repository = new Repository( new InMemoryPersistenceHandler(), new InMemoryIOHandler() );
        $mediaValue = new MediaTypeValue( $repository->getIOService(), $this->mediaPath );
        $this->persistenceMediaValue = new FieldValue;
        $this->persistenceMediaValue->data = $mediaValue;
        $this->persistenceMediaValue->sortKey = false;
        $this->persistenceMediaValue->fieldSettings = new FieldSettings(
            array( 'mediaType' => MediaType::TYPE_HTML5_VIDEO )
        );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Media::toStorageValue
     */
    public function testToStorageValue()
    {
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $this->persistenceMediaValue, $storageFieldValue );
        self::assertNull( $storageFieldValue->dataText );
        self::assertNull( $storageFieldValue->dataInt );
        self::assertNull( $storageFieldValue->dataFloat );
        self::assertEquals( 0, $storageFieldValue->sortKeyInt );
        self::assertEquals( '', $storageFieldValue->sortKeyString );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Media::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertNull( $fieldValue->data );
        self::assertNull( $fieldValue->fieldSettings );
        self::assertNull( $fieldValue->sortKey );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Media::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $storageFieldDef = new StorageFieldDefinition;
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->validators = array(
            // Setting max file size to 1MB (1.048.576 bytes)
            MediaTypeConverter::FILESIZE_VALIDATOR_FQN => array( 'maxFileSize' => 1048576 )
        );
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'mediaType' => MediaType::TYPE_HTML5_VIDEO
            )
        );
        $repository = new Repository( new InMemoryPersistenceHandler(), new InMemoryIOHandler() );
        $fieldDef = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => $fieldTypeConstraints,
                'defaultValue' => new MediaTypeValue( $repository->getIOService() )
            )
        );

        $this->converter->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
        self::assertSame(
            $fieldDef->fieldTypeConstraints->validators[MediaTypeConverter::FILESIZE_VALIDATOR_FQN],
            array( 'maxFileSize' => $storageFieldDef->dataInt1 )
        );
        self::assertSame(
            $fieldDef->fieldTypeConstraints->fieldSettings['mediaType'],
            $storageFieldDef->dataText1
        );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Media::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $fieldDef = new PersistenceFieldDefinition;
        $storageDef = new StorageFieldDefinition(
            array(
                'dataInt1' => 1048576,
                'dataText1' => MediaType::TYPE_HTML5_VIDEO
            )
        );

        $this->converter->toFieldDefinition( $storageDef, $fieldDef );
        self::assertSame(
            array(
                MediaTypeConverter::FILESIZE_VALIDATOR_FQN => array( 'maxFileSize' => $storageDef->dataInt1 )
            ),
            $fieldDef->fieldTypeConstraints->validators
        );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Repository\\FieldType\\FieldSettings', $fieldDef->fieldTypeConstraints->fieldSettings );
        self::assertSame(
            array( 'mediaType' => MediaType::TYPE_HTML5_VIDEO ),
            $fieldDef->fieldTypeConstraints->fieldSettings->getArrayCopy()
        );
    }
}
