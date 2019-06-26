<?php

/**
 * File containing the KeywordTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\KeywordConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Keyword converter in Legacy storage.
 */
class KeywordTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\KeywordConverter */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new KeywordConverter();
    }

    /**
     * @group fieldType
     * @group keyword
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\KeywordConverter::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue();
        $value->data = ['key1', 'key2'];
        $value->sortKey = false;
        $storageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue($value, $storageFieldValue);
        $this->assertNull($storageFieldValue->dataText);
        $this->assertNull($storageFieldValue->dataInt);
        $this->assertNull($storageFieldValue->dataFloat);
        $this->assertEquals(0, $storageFieldValue->sortKeyInt);
        $this->assertEquals('', $storageFieldValue->sortKeyString);
    }

    /**
     * @group fieldType
     * @group keyword
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\KeywordConverter::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue();
        $fieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $fieldValue);
        $this->assertSame([], $fieldValue->data);
        $this->assertEquals('', $fieldValue->sortKey);
    }

    /**
     * @group fieldType
     * @group keyword
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\KeywordConverter::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $this->converter->toStorageFieldDefinition(new PersistenceFieldDefinition(), new StorageFieldDefinition());
    }

    /**
     * @group fieldType
     * @group keyword
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\KeywordConverter::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $this->converter->toFieldDefinition(new StorageFieldDefinition(), new PersistenceFieldDefinition());
    }
}
