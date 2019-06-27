<?php

/**
 * File containing the RelationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\RelationList\Type;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\RelationConverter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Relation converter in Legacy storage.
 */
class RelationTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\RelationConverter */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new RelationConverter();
    }

    /**
     * @group fieldType
     * @group relationlist
     */
    public function testToStorageFieldDefinition()
    {
        $fieldDefinition = new PersistenceFieldDefinition(
            [
                'fieldTypeConstraints' => new FieldTypeConstraints(
                    [
                        'fieldSettings' => [
                            'selectionMethod' => Type::SELECTION_BROWSE,
                            'selectionRoot' => 12345,
                            'selectionContentTypes' => ['article', 'blog_post'],
                        ],
                    ]
                ),
            ]
        );

        $expectedStorageFieldDefinition = new StorageFieldDefinition();
        $expectedStorageFieldDefinition->dataText5 = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<related-objects><constraints><allowed-class contentclass-identifier="article"/><allowed-class contentclass-identifier="blog_post"/></constraints><selection_type value="0"/><contentobject-placement node-id="12345"/></related-objects>

EOT;
        // For BC these are still set
        $expectedStorageFieldDefinition->dataInt1 = 0;
        $expectedStorageFieldDefinition->dataInt2 = 12345;

        $actualStorageFieldDefinition = new StorageFieldDefinition();

        $this->converter->toStorageFieldDefinition($fieldDefinition, $actualStorageFieldDefinition);

        $this->assertEquals(
            $expectedStorageFieldDefinition,
            $actualStorageFieldDefinition
        );
    }
}
