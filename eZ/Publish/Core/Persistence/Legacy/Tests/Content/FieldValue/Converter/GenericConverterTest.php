<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\FieldType\Generic\ValueSerializerInterface;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\GenericConverter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Generic converter in Legacy storage.
 */
class GenericConverterTest extends TestCase
{
    private const EXAMPLE_DATA = [
        'foo' => 'foo',
        'bar' => 'bar',
    ];

    private const EXAMPLE_JSON = '{"foo":"foo","bar":"bar"}';

    /** @var \eZ\Publish\Core\FieldType\Generic\ValueSerializerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $serializer;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\GenericConverter */
    private $converter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = $this->createMock(ValueSerializerInterface::class);
        $this->converter = new GenericConverter($this->serializer);
    }

    public function testToStorageValue(): void
    {
        $fieldValue = new FieldValue();
        $fieldValue->data = self::EXAMPLE_DATA;
        $fieldValue->sortKey = 'key';

        $this->serializer
            ->expects($this->once())
            ->method('encode')
            ->with($fieldValue->data)
            ->willReturn(self::EXAMPLE_JSON);

        $storageValue = new StorageFieldValue();

        $this->converter->toStorageValue($fieldValue, $storageValue);

        $this->assertEquals(self::EXAMPLE_JSON, $storageValue->dataText);
        $this->assertEquals('key', $storageValue->sortKeyString);
    }

    public function testEmptyToStorageValue(): void
    {
        $this->serializer
            ->expects($this->never())
            ->method('encode');

        $storageValue = new StorageFieldValue();

        $this->converter->toStorageValue(new FieldValue(), $storageValue);

        $this->assertNull($storageValue->dataText);
    }

    public function testToFieldValue(): void
    {
        $storageValue = new StorageFieldValue();
        $storageValue->sortKeyString = 'key';
        $storageValue->dataText = self::EXAMPLE_JSON;

        $this->serializer
            ->expects($this->once())
            ->method('decode')
            ->with(self::EXAMPLE_JSON)
            ->willReturn(self::EXAMPLE_DATA);

        $fieldValue = new FieldValue();

        $this->converter->toFieldValue($storageValue, $fieldValue);

        $this->assertEquals('key', $fieldValue->sortKey);
        $this->assertEquals(self::EXAMPLE_DATA, $fieldValue->data);
        $this->assertNull($fieldValue->externalData);
    }

    public function testEmptyToFieldValue(): void
    {
        $this->serializer
            ->expects($this->never())
            ->method('decode');

        $fieldValue = new FieldValue();

        $this->converter->toFieldValue(new StorageFieldValue(), $fieldValue);

        $this->assertNull($fieldValue->data);
    }

    public function testToStorageFieldDefinition(): void
    {
        $fieldTypeConstraints = new FieldTypeConstraints();
        $fieldTypeConstraints->fieldSettings = new FieldSettings(self::EXAMPLE_DATA);

        $fieldDefinition = new FieldDefinition([
            'fieldTypeConstraints' => $fieldTypeConstraints,
        ]);

        $this->serializer
            ->expects($this->once())
            ->method('encode')
            ->with(new FieldSettings(self::EXAMPLE_DATA))
            ->willReturn(self::EXAMPLE_JSON);

        $storageFieldDefinition = new StorageFieldDefinition();

        $this->converter->toStorageFieldDefinition($fieldDefinition, $storageFieldDefinition);

        $this->assertEquals(self::EXAMPLE_JSON, $storageFieldDefinition->dataText5);
    }

    public function testEmptyToStorageFieldDefinition(): void
    {
        $this->serializer
            ->expects($this->never())
            ->method('encode');

        $storageFieldDefinition = new StorageFieldDefinition();

        $this->converter->toStorageFieldDefinition(new FieldDefinition(), $storageFieldDefinition);

        $this->assertNull($storageFieldDefinition->dataText5);
    }

    public function testToFieldDefinition(): void
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $storageFieldDefinition->dataText5 = self::EXAMPLE_JSON;

        $this->serializer
            ->expects($this->once())
            ->method('decode')
            ->with(self::EXAMPLE_JSON)
            ->willReturn(self::EXAMPLE_DATA);

        $fieldDefinition = new FieldDefinition();

        $this->converter->toFieldDefinition($storageFieldDefinition, $fieldDefinition);

        $this->assertEquals(
            new FieldSettings(self::EXAMPLE_DATA),
            $fieldDefinition->fieldTypeConstraints->fieldSettings
        );
    }

    public function testEmptyToFieldDefinition(): void
    {
        $this->serializer
            ->expects($this->never())
            ->method('decode');

        $fieldDefinition = new FieldDefinition();

        $this->converter->toFieldDefinition(new StorageFieldDefinition(), $fieldDefinition);

        $this->assertNull($fieldDefinition->fieldTypeConstraints->fieldSettings);
    }
}
