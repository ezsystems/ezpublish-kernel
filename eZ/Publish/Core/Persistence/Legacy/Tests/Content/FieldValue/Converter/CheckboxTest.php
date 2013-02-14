<?php
/**
 * File containing the CheckboxTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Checkbox as CheckboxConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use PHPUnit_Framework_TestCase;

/**
 * Test case for Checkbox converter in Legacy storage
 */
class CheckboxTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Checkbox
     */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new CheckboxConverter;
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Checkbox::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = true;
        $value->sortKey = 1;
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( (int)$value->data, $storageFieldValue->dataInt );
        self::assertSame( $value->sortKey, $storageFieldValue->sortKeyInt );
        self::assertSame( '', $storageFieldValue->sortKeyString );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Checkbox::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataInt = 1;
        $storageFieldValue->sortKeyInt = 1;
        $storageFieldValue->sortKeyString = '';
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertSame( (bool)$storageFieldValue->dataInt, $fieldValue->data );
        self::assertSame( $storageFieldValue->sortKeyInt, $fieldValue->sortKey );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Checkbox::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $defaultBool = false;
        $storageFieldDef = new StorageFieldDefinition;
        $defaultValue = new FieldValue;
        $defaultValue->data = $defaultBool;
        $fieldDef = new PersistenceFieldDefinition(
            array(
                'defaultValue' => $defaultValue
            )
        );

        $this->converter->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
        self::assertSame(
            (int)$fieldDef->defaultValue->data,
            $storageFieldDef->dataInt3
        );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Checkbox::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $defaultBool = true;
        $fieldDef = new PersistenceFieldDefinition;
        $storageDef = new StorageFieldDefinition(
            array(
                'dataInt3' => 1,
            )
        );

        $this->converter->toFieldDefinition( $storageDef, $fieldDef );
        self::assertSame( $defaultBool, $fieldDef->defaultValue->data );
        self::assertNull( $fieldDef->fieldTypeConstraints->fieldSettings );
    }
}
