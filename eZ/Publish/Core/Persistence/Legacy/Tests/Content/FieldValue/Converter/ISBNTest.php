<?php

/**
 * File containing the ISBNTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\ISBNConverter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use PHPUnit\Framework\TestCase;

/**
 * Test for ISBNConverter in Legacy Storage.
 */
class ISBNTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\ISBNConverter */
    protected $converter;

    protected function setUp()
    {
        $this->converter = new ISBNConverter();
    }

    /**
     * @dataProvider providerForTestToFieldDefinition
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\ISBNConverter::toFieldDefinition
     */
    public function testToFieldDefinition($dataInt, $excpectedIsbn13Value)
    {
        $fieldDef = new PersistenceFieldDefinition();
        $storageDefinition = new StorageFieldDefinition([
            'dataInt1' => $dataInt,
        ]);

        $this->converter->toFieldDefinition($storageDefinition, $fieldDef);

        /** @var FieldSettings $fieldSettings */
        $fieldSettings = $fieldDef->fieldTypeConstraints->fieldSettings;
        self::assertSame($excpectedIsbn13Value, $fieldSettings['isISBN13']);
    }

    public function providerForTestToFieldDefinition()
    {
        return [
            [1, true],
            [0, false],
            [null, false],
        ];
    }
}
