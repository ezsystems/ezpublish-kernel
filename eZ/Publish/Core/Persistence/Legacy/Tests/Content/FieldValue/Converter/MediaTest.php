<?php
/**
 * File containing the MediaTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\Media\Type as MediaType;
use eZ\Publish\Core\FieldType\Media\Value as MediaTypeValue;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Media as MediaTypeConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\IO\InMemoryHandler as InMemoryIOHandler;
use eZ\Publish\Core\Persistence\InMemory\Handler as InMemoryPersistenceHandler;

/**
 * Test case for MediaType converter in Legacy storage
 */
class MediaTest extends \PHPUnit_Framework_TestCase
{
    protected $converter;

    protected function setUp()
    {
        $this->converter = MediaTypeConverter::create();
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
            'FileSizeValidator' => array( 'maxFileSize' => 1048576 )
        );
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'mediaType' => MediaType::TYPE_HTML5_VIDEO
            )
        );

        $fieldDef = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => $fieldTypeConstraints,
                'defaultValue' => null
            )
        );

        $this->converter->toStorageFieldDefinition( $fieldDef, $storageFieldDef );

        self::assertSame(
            $fieldDef->fieldTypeConstraints->validators['FileSizeValidator'],
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
                'FileSizeValidator' => array( 'maxFileSize' => $storageDef->dataInt1 )
            ),
            $fieldDef->fieldTypeConstraints->validators
        );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\FieldType\\FieldSettings', $fieldDef->fieldTypeConstraints->fieldSettings );
        self::assertSame(
            array( 'mediaType' => MediaType::TYPE_HTML5_VIDEO ),
            $fieldDef->fieldTypeConstraints->fieldSettings->getArrayCopy()
        );
    }
}
