<?php

/**
 * File containing the RatingTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\RatingConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Rating converter in Legacy storage.
 */
class RatingTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\RatingConverter */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new RatingConverter();
    }

    /**
     * @group fieldType
     * @group rating
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\RatingConverter::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue();
        $value->data = false;
        $value->sortKey = false;
        $storageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue($value, $storageFieldValue);
        self::assertSame(null, $storageFieldValue->dataInt);
    }

    /**
     * @group fieldType
     * @group rating
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\RatingConverter::toStorageValue
     */
    public function testToStorageValueDisabled()
    {
        $value = new FieldValue();
        $value->data = true;
        $value->sortKey = false;
        $storageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue($value, $storageFieldValue);
        self::assertSame(1, $storageFieldValue->dataInt);
    }

    /**
     * @group fieldType
     * @group rating
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\RatingConverter::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->dataInt = null;
        $fieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $fieldValue);
        self::assertSame(false, $fieldValue->data);
    }

    /**
     * @group fieldType
     * @group rating
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\RatingConverter::toFieldValue
     */
    public function testToFieldValueDisabled()
    {
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->dataInt = 1;
        $fieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $fieldValue);
        self::assertSame(true, $fieldValue->data);
    }

    /**
     * @group fieldType
     * @group rating
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\RatingConverter::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $this->converter->toStorageFieldDefinition(new PersistenceFieldDefinition(), new StorageFieldDefinition());
    }

    /**
     * @group fieldType
     * @group rating
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\RatingConverter::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $this->converter->toFieldDefinition(new StorageFieldDefinition(), new PersistenceFieldDefinition());
    }
}
