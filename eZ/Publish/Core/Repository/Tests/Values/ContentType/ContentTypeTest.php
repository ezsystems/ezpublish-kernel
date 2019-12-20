<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Tests\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCollection as APIFieldDefinitionCollection;
use PHPUnit\Framework\TestCase;

class ContentTypeTest extends TestCase
{
    private const EXAMPLE_FIELD_DEFINITION_IDENTIFIER = 'example';
    private const EXAMPLE_FIELD_TYPE_IDENTIFIER = 'ezcustom';

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\ContentType::getProperties
     */
    public function testObjectProperties(): void
    {
        $object = new ContentType([
            'fieldDefinitions' => $this->createMock(APIFieldDefinitionCollection::class),
        ]);

        $properties = $object->attributes();

        self::assertNotContains('internalFields', $properties, 'Internal property found ');
        self::assertContains('contentTypeGroups', $properties, 'Property not found');
        self::assertContains('fieldDefinitions', $properties, 'Property not found');
        self::assertContains('id', $properties, 'Property not found');
        self::assertContains('status', $properties, 'Property not found');
        self::assertContains('identifier', $properties, 'Property not found');
        self::assertContains('creationDate', $properties, 'Property not found');
        self::assertContains('modificationDate', $properties, 'Property not found');
        self::assertContains('creatorId', $properties, 'Property not found');
        self::assertContains('modifierId', $properties, 'Property not found');
        self::assertContains('remoteId', $properties, 'Property not found');
        self::assertContains('urlAliasSchema', $properties, 'Property not found');
        self::assertContains('nameSchema', $properties, 'Property not found');
        self::assertContains('isContainer', $properties, 'Property not found');
        self::assertContains('mainLanguageCode', $properties, 'Property not found');
        self::assertContains('defaultAlwaysAvailable', $properties, 'Property not found');
        self::assertContains('defaultSortField', $properties, 'Property not found');
        self::assertContains('defaultSortOrder', $properties, 'Property not found');

        // check for duplicates and double check existence of property
        $propertiesHash = [];
        foreach ($properties as $property) {
            if (isset($propertiesHash[$property])) {
                self::fail("Property '{$property}' exists several times in properties list");
            } elseif (!isset($object->$property)) {
                self::fail("Property '{$property}' does not exist on object, even though it was hinted to be there");
            }

            $propertiesHash[$property] = 1;
        }
    }

    public function testGetFieldDefinition(): void
    {
        $fieldDefinition = $this->createMock(FieldDefinition::class);

        $fieldDefinitionCollection = $this->createMock(APIFieldDefinitionCollection::class);

        $fieldDefinitionCollection
            ->expects($this->once())
            ->method('has')
            ->with(self::EXAMPLE_FIELD_DEFINITION_IDENTIFIER)
            ->willReturn(true);

        $fieldDefinitionCollection
            ->expects($this->once())
            ->method('get')
            ->with(self::EXAMPLE_FIELD_DEFINITION_IDENTIFIER)
            ->willReturn($fieldDefinition);

        $contentType = new ContentType([
            'fieldDefinitions' => $fieldDefinitionCollection,
        ]);

        $this->assertEquals(
            $fieldDefinition,
            $contentType->getFieldDefinition(self::EXAMPLE_FIELD_DEFINITION_IDENTIFIER)
        );
    }

    public function testHasFieldDefinition(): void
    {
        $fieldDefinitionCollection = $this->createMock(APIFieldDefinitionCollection::class);
        $fieldDefinitionCollection
            ->expects($this->once())
            ->method('has')
            ->with(self::EXAMPLE_FIELD_DEFINITION_IDENTIFIER)
            ->willReturn(true);

        $contentType = new ContentType([
            'fieldDefinitions' => $fieldDefinitionCollection,
        ]);

        $this->assertTrue(
            $contentType->hasFieldDefinition(self::EXAMPLE_FIELD_DEFINITION_IDENTIFIER)
        );
    }

    public function testHasFieldDefinitionOfType(): void
    {
        $fieldDefinitionCollection = $this->createMock(APIFieldDefinitionCollection::class);
        $fieldDefinitionCollection
            ->expects($this->once())
            ->method('anyOfType')
            ->with(self::EXAMPLE_FIELD_TYPE_IDENTIFIER)
            ->willReturn(true);

        $contentType = new ContentType([
            'fieldDefinitions' => $fieldDefinitionCollection,
        ]);

        $this->assertTrue(
            $contentType->hasFieldDefinitionOfType(self::EXAMPLE_FIELD_TYPE_IDENTIFIER)
        );
    }

    public function testGetFieldDefinitionsOfType(): void
    {
        $expectedFieldDefinitionCollection = $this->createMock(APIFieldDefinitionCollection::class);

        $fieldDefinitionCollection = $this->createMock(APIFieldDefinitionCollection::class);
        $fieldDefinitionCollection
            ->expects($this->once())
            ->method('filterByType')
            ->with(self::EXAMPLE_FIELD_TYPE_IDENTIFIER)
            ->willReturn($expectedFieldDefinitionCollection);

        $contentType = new ContentType([
            'fieldDefinitions' => $fieldDefinitionCollection,
        ]);

        $this->assertEquals(
            $expectedFieldDefinitionCollection,
            $contentType->getFieldDefinitionsOfType(self::EXAMPLE_FIELD_TYPE_IDENTIFIER)
        );
    }

    public function testGetFirstFieldDefinitionOfType(): void
    {
        $expectedFieldDefinition = $this->createMock(FieldDefinition::class);

        $filteredFieldDefinitionCollection = $this->createMock(APIFieldDefinitionCollection::class);
        $filteredFieldDefinitionCollection
            ->method('first')
            ->willReturn($expectedFieldDefinition);

        $fieldDefinitionCollection = $this->createMock(APIFieldDefinitionCollection::class);
        $fieldDefinitionCollection
            ->expects($this->once())
            ->method('filterByType')
            ->with(self::EXAMPLE_FIELD_TYPE_IDENTIFIER)
            ->willReturn($filteredFieldDefinitionCollection);

        $contentType = new ContentType([
            'fieldDefinitions' => $fieldDefinitionCollection,
        ]);

        $this->assertEquals(
            $expectedFieldDefinition,
            $contentType->getFirstFieldDefinitionOfType(self::EXAMPLE_FIELD_TYPE_IDENTIFIER)
        );
    }
}
