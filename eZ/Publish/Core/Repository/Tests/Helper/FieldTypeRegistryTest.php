<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\ContentTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Helper;

use eZ\Publish\Core\Repository\Helper\FieldTypeRegistry;
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

    protected function getClosure($returnValue)
    {
        return function () use ($returnValue) {
            return $returnValue;
        };
    }

    public function testGetFieldType()
    {
        $fieldTypes = array(
            self::FIELD_TYPE_ID => $this->getFieldTypeMock(),
        );

        $registry = new FieldTypeRegistry($fieldTypes);

        $fieldType = $registry->getFieldType(self::FIELD_TYPE_ID);

        $this->assertInstanceOf(
            FieldType::class,
            $fieldType
        );
    }

    public function testGetClosureFieldType()
    {
        $fieldTypes = array(
            self::FIELD_TYPE_ID => $this->getClosure($this->getFieldTypeMock()),
        );

        $registry = new FieldTypeRegistry($fieldTypes);

        $fieldType = $registry->getFieldType(self::FIELD_TYPE_ID);

        $this->assertInstanceOf(
            FieldType::class,
            $fieldType
        );
    }

    public function testGetFieldTypeThrowsNotFoundException()
    {
        $this->expectException(\eZ\Publish\Core\Base\Exceptions\NotFound\FieldTypeNotFoundException::class);

        $registry = new FieldTypeRegistry(array());

        $registry->getFieldType('none');
    }

    public function testGetFieldTypeThrowsRuntimeExceptionIncorrectType()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('$fieldTypes[none] must be instance of SPI\\FieldType\\FieldType or callable');

        $registry = new FieldTypeRegistry(
            array(
                'none' => "I'm not a field type",
            )
        );

        $registry->getFieldType('none');
    }

    public function testGetClosureFieldTypeThrowsRuntimeExceptionIncorrectType()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('$fieldTypes[none] must be instance of SPI\\FieldType\\FieldType or callable');

        $registry = new FieldTypeRegistry(
            array(
                'none' => $this->getClosure("I'm not a field type"),
            )
        );

        $registry->getFieldType('none');
    }

    public function testGetFieldTypes()
    {
        $fieldTypes = array(
            self::FIELD_TYPE_ID => $this->getFieldTypeMock(),
            'two' => $this->getClosure($this->getFieldTypeMock()),
        );

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
