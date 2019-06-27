<?php

/**
 * File containing the SelectionTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\SelectionConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Selection converter in Legacy storage.
 */
class SelectionTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\SelectionConverter */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $languageServiceMock = $this->createMock(LanguageService::class);

        $this->converter = new SelectionConverter($languageServiceMock);
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\SelectionConverter::toStorageValue
     */
    public function testToStorageValue()
    {
        $fieldValue = new FieldValue();
        $fieldValue->data = [1, 3];
        $fieldValue->sortKey = '1-3';

        $expectedStorageFieldValue = new StorageFieldValue();
        $expectedStorageFieldValue->dataText = '1-3';
        $expectedStorageFieldValue->sortKeyString = '1-3';

        $actualStorageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue($fieldValue, $actualStorageFieldValue);

        $this->assertEquals(
            $expectedStorageFieldValue,
            $actualStorageFieldValue
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\SelectionConverter::toStorageValue
     */
    public function testToStorageValueEmpty()
    {
        $fieldValue = new FieldValue();
        $fieldValue->data = [];
        $fieldValue->sortKey = '';

        $expectedStorageFieldValue = new StorageFieldValue();
        $expectedStorageFieldValue->dataText = '';
        $expectedStorageFieldValue->sortKeyString = '';

        $actualStorageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue($fieldValue, $actualStorageFieldValue);

        $this->assertEquals(
            $expectedStorageFieldValue,
            $actualStorageFieldValue
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\SelectionConverter::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->dataText = '1-3';
        $storageFieldValue->sortKeyString = '1-3';

        $expectedFieldValue = new FieldValue();
        $expectedFieldValue->data = [1, 3];
        $expectedFieldValue->sortKey = '1-3';

        $actualFieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $actualFieldValue);

        $this->assertEquals(
            $expectedFieldValue,
            $actualFieldValue
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\SelectionConverter::toFieldValue
     */
    public function testToFieldValueEmpty()
    {
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->dataText = '';
        $storageFieldValue->sortKeyString = '';

        $expectedFieldValue = new FieldValue();
        $expectedFieldValue->data = [];
        $expectedFieldValue->sortKey = '';

        $actualFieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $actualFieldValue);

        $this->assertEquals(
            $expectedFieldValue,
            $actualFieldValue
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\SelectionConverter::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionMultiple()
    {
        $fieldDefinition = new PersistenceFieldDefinition(
            [
                'fieldTypeConstraints' => new FieldTypeConstraints(
                    [
                        'fieldSettings' => new FieldSettings(
                            [
                                'isMultiple' => true,
                                'options' => [
                                    0 => 'First',
                                    1 => 'Second',
                                    2 => 'Third',
                                ],
                            ]
                        ),
                    ]
                ),
            ]
        );

        $expectedStorageFieldDefinition = new StorageFieldDefinition();
        $expectedStorageFieldDefinition->dataInt1 = 1;
        $expectedStorageFieldDefinition->dataText5 = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<ezselection><options><option id="0" name="First"/><option id="1" name="Second"/><option id="2" name="Third"/></options></ezselection>

EOT;

        $actualStorageFieldDefinition = new StorageFieldDefinition();

        $this->converter->toStorageFieldDefinition($fieldDefinition, $actualStorageFieldDefinition);

        $this->assertEquals($expectedStorageFieldDefinition, $actualStorageFieldDefinition);
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\SelectionConverter::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionSingle()
    {
        $fieldDefinition = new PersistenceFieldDefinition(
            [
                'fieldTypeConstraints' => new FieldTypeConstraints(
                    [
                        'fieldSettings' => new FieldSettings(
                            [
                                'isMultiple' => false,
                                'options' => [
                                    0 => 'First',
                                ],
                            ]
                        ),
                    ]
                ),
            ]
        );

        $expectedStorageFieldDefinition = new StorageFieldDefinition();
        $expectedStorageFieldDefinition->dataInt1 = 0;
        $expectedStorageFieldDefinition->dataText5 = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<ezselection><options><option id="0" name="First"/></options></ezselection>

EOT;

        $actualStorageFieldDefinition = new StorageFieldDefinition();

        $this->converter->toStorageFieldDefinition($fieldDefinition, $actualStorageFieldDefinition);

        $this->assertEquals($expectedStorageFieldDefinition, $actualStorageFieldDefinition);
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\SelectionConverter::toFieldDefinition
     */
    public function testToFieldDefinitionMultiple()
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $storageFieldDefinition->dataInt1 = 1;
        $storageFieldDefinition->dataText5 = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<ezselection>
  <options>
    <option id="0" name="First"/>
    <option id="1" name="Second"/>
    <option id="2" name="Third"/>
  </options>
</ezselection>
EOT;

        $expectedFieldDefinition = new PersistenceFieldDefinition(
            [
                'name' => [
                    'eng-GB' => 'test name',
                ],
                'mainLanguageCode' => 'eng-GB',
                'fieldTypeConstraints' => new FieldTypeConstraints(
                    [
                        'fieldSettings' => new FieldSettings(
                            [
                                'isMultiple' => true,
                                'options' => [
                                    0 => 'First',
                                    1 => 'Second',
                                    2 => 'Third',
                                ],
                                'multilingualOptions' => [
                                    'eng-GB' => [
                                        0 => 'First',
                                        1 => 'Second',
                                        2 => 'Third',
                                    ],
                                ],
                            ]
                        ),
                    ]
                ),
                'defaultValue' => new FieldValue(
                    [
                        'data' => [],
                        'sortKey' => '',
                    ]
                ),
            ]
        );

        $actualFieldDefinition = new PersistenceFieldDefinition(
            [
                'name' => [
                    'eng-GB' => 'test name',
                ],
                'mainLanguageCode' => 'eng-GB',
            ]
        );

        $this->converter->toFieldDefinition($storageFieldDefinition, $actualFieldDefinition);

        $this->assertEquals($expectedFieldDefinition, $actualFieldDefinition);
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\SelectionConverter::toFieldDefinition
     */
    public function testToFieldDefinitionSingleEmpty()
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $storageFieldDefinition->dataInt1 = 0;
        $storageFieldDefinition->dataText5 = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<ezselection>
  <options>
  </options>
</ezselection>
EOT;

        $expectedFieldDefinition = new PersistenceFieldDefinition(
            [
                'name' => [
                    'eng-GB' => 'test name',
                ],
                'mainLanguageCode' => 'eng-GB',
                'fieldTypeConstraints' => new FieldTypeConstraints(
                    [
                        'fieldSettings' => new FieldSettings(
                            [
                                'isMultiple' => false,
                                'options' => [],
                                'multilingualOptions' => [
                                    'eng-GB' => [],
                                ],
                            ]
                        ),
                    ]
                ),
                'defaultValue' => new FieldValue(['data' => []]),
            ]
        );

        $actualFieldDefinition = new PersistenceFieldDefinition(
            [
                'name' => [
                    'eng-GB' => 'test name',
                ],
                'mainLanguageCode' => 'eng-GB',
            ]
        );

        $this->converter->toFieldDefinition($storageFieldDefinition, $actualFieldDefinition);

        $this->assertEquals($expectedFieldDefinition, $actualFieldDefinition);
    }
}
