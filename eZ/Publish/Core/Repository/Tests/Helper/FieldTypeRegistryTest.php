<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Helper;

use eZ\Publish\Core\Base\Exceptions\NotFound\FieldTypeNotFoundException;
use eZ\Publish\Core\FieldType\FieldTypeRegistry;
use eZ\Publish\SPI\FieldType\FieldType;
use PHPUnit\Framework\TestCase;

/**
 * Unit test case for FieldTypeRegistry helper.
 */
class FieldTypeRegistryTest extends TestCase
{
    private const FIELD_TYPE_ID = 'one';

    public function testConstructor()
    {
        $fieldType = $this->getFieldTypeMock();
        $fieldTypes = [self::FIELD_TYPE_ID => $fieldType];

        $registry = new FieldTypeRegistry($fieldTypes);
        $this->assertTrue($registry->hasFieldType(self::FIELD_TYPE_ID));
    }

    protected function getFieldTypeMock()
    {
        return $this->createMock(FieldType::class);
    }

    public function testGetFieldType()
    {
        $fieldTypes = [
            self::FIELD_TYPE_ID => $this->getFieldTypeMock(),
        ];

        $registry = new FieldTypeRegistry($fieldTypes);

        $fieldType = $registry->getFieldType(self::FIELD_TYPE_ID);

        $this->assertInstanceOf(
            FieldType::class,
            $fieldType
        );
    }

    public function testGetFieldTypeThrowsNotFoundException()
    {
        $this->expectException(FieldTypeNotFoundException::class);

        $registry = new FieldTypeRegistry([]);

        $registry->getFieldType('none');
    }

    public function testGetFieldTypeThrowsRuntimeExceptionIncorrectType()
    {
        $this->expectException(\TypeError::class);

        $registry = new FieldTypeRegistry(
            [
                'none' => "I'm not a field type",
            ]
        );

        $registry->getFieldType('none');
    }

    public function testGetFieldTypes()
    {
        $fieldTypes = [
            self::FIELD_TYPE_ID => $this->getFieldTypeMock(),
            'two' => $this->getFieldTypeMock(),
        ];

        $registry = new FieldTypeRegistry($fieldTypes);

        $fieldTypes = $registry->getFieldTypes();

        $this->assertIsArray($fieldTypes);
        $this->assertCount(2, $fieldTypes);
        $this->assertArrayHasKey(self::FIELD_TYPE_ID, $fieldTypes);
        $this->assertInstanceOf(
            FieldType::class,
            $fieldTypes[self::FIELD_TYPE_ID]
        );
        $this->assertArrayHasKey('two', $fieldTypes);
        $this->assertInstanceOf(
            FieldType::class,
            $fieldTypes['two']
        );
    }
}
