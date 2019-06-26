<?php

/**
 * File containing the UrlTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\UrlConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Url converter in Legacy storage.
 */
class UrlTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\UrlConverter */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new UrlConverter();
    }

    /**
     * @group fieldType
     * @group url
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\UrlConverter::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue();
        $text = 'eZ Systems';
        $value->data = ['text' => $text];
        $value->externalData = 'http://ez.no/';
        $value->sortKey = false;
        $storageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue($value, $storageFieldValue);
        self::assertSame($text, $storageFieldValue->dataText);
    }

    /**
     * @group fieldType
     * @group url
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\UrlConverter::toFieldValue
     */
    public function testToFieldValue()
    {
        $text = "A link's text";
        $urlId = 842;
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->dataText = $text;
        $storageFieldValue->dataInt = $urlId;
        $storageFieldValue->sortKeyString = false;
        $storageFieldValue->sortKeyInt = false;
        $fieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $fieldValue);
        self::assertInternalType('array', $fieldValue->data);
        self::assertFalse($fieldValue->sortKey);
        self::assertSame($text, $fieldValue->data['text']);
        self::assertEquals($urlId, $fieldValue->data['urlId']);
    }

    /**
     * @group fieldType
     * @group url
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\UrlConverter::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $this->converter->toStorageFieldDefinition(new PersistenceFieldDefinition(), new StorageFieldDefinition());
    }

    /**
     * @group fieldType
     * @group url
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\UrlConverter::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $this->converter->toFieldDefinition(new StorageFieldDefinition(), new PersistenceFieldDefinition());
    }
}
