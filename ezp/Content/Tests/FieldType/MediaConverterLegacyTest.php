<?php
/**
 * File containing the MediaConverterLegacyTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use ezp\Content\FieldType\Media\Type as MediaType,
    ezp\Content\FieldType\Media\Value as MediaTypeValue,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Media as MediaTypeConverter,
    ezp\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition,
    ezp\Persistence\Content\FieldTypeConstraints,
    ezp\Content\FieldType\FieldSettings,
    ezp\Base\BinaryRepository;

/**
 * Test case for MediaType converter in Legacy storage
 */
class MediaConverterLegacyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\MediaType
     */
    protected $converter;

    /**
     * Path to test media
     * @var string
     */
    protected $mediaPath;

    /**
     * Persistence field value to use in tests
     * @var \ezp\Persistence\Content\FieldValue
     */
    protected $persistenceMediaValue;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new MediaTypeConverter;
        BinaryRepository::setOverrideOptions( 'inmemory' );
        $this->mediaPath = __DIR__ . '/developer-got-hurt.m4v';

        $mediaValue = new MediaTypeValue;
        $mediaValue->file = $mediaValue->getHandler()->createFromLocalPath( $this->mediaPath );
        $mediaValue->pluginspage = $mediaValue->getHandler()->getPluginspageByType( MediaType::TYPE_HTML5_VIDEO );
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
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Media::toStorageValue
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
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Media::toFieldValue
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
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Media::toStorageFieldDefinition
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
        $fieldDef = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => $fieldTypeConstraints,
                'defaultValue' => new MediaTypeValue
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
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Media::toFieldDefinition
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
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\FieldSettings', $fieldDef->fieldTypeConstraints->fieldSettings );
        self::assertSame(
            array( 'mediaType' => MediaType::TYPE_HTML5_VIDEO ),
            $fieldDef->fieldTypeConstraints->fieldSettings->getArrayCopy()
        );
    }
}
