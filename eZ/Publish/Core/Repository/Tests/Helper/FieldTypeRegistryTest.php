<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\ContentTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\Tests\Helper;

use eZ\Publish\Core\Repository\Helper\FieldTypeRegistry;
use PHPUnit_Framework_TestCase;

/**
 * Unit test case for FieldTypeRegistry helper.
 */
class FieldTypeRegistryTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $fieldTypes = array('field types');

        $registry = new FieldTypeRegistry($fieldTypes);

        $this->assertAttributeSame(
            $fieldTypes,
            'fieldTypes',
            $registry
        );
    }

    protected function getFieldTypeMock()
    {
        return $this->getMock('eZ\\Publish\\SPI\\FieldType\\FieldType');
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
            'one' => $this->getFieldTypeMock(),
        );

        $registry = new FieldTypeRegistry($fieldTypes);

        $fieldType = $registry->getFieldType('one');

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\FieldType\\FieldType',
            $fieldType
        );
    }

    public function testGetClosureFieldType()
    {
        $fieldTypes = array(
            'one' => $this->getClosure($this->getFieldTypeMock()),
        );

        $registry = new FieldTypeRegistry($fieldTypes);

        $fieldType = $registry->getFieldType('one');

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\FieldType\\FieldType',
            $fieldType
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFound\FieldTypeNotFoundException
     */
    public function testGetFieldTypeThrowsNotFoundException()
    {
        $registry = new FieldTypeRegistry(array());

        $registry->getFieldType('none');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage $fieldTypes[none] must be instance of SPI\FieldType\FieldType or callable
     */
    public function testGetFieldTypeThrowsRuntimeExceptionIncorrectType()
    {
        $registry = new FieldTypeRegistry(
            array(
                'none' => "I'm not a field type",
            )
        );

        $registry->getFieldType('none');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage $fieldTypes[none] must be instance of SPI\FieldType\FieldType or callable
     */
    public function testGetClosureFieldTypeThrowsRuntimeExceptionIncorrectType()
    {
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
            'one' => $this->getFieldTypeMock(),
            'two' => $this->getClosure($this->getFieldTypeMock()),
        );

        $registry = new FieldTypeRegistry($fieldTypes);

        $fieldTypes = $registry->getFieldTypes();

        $this->assertInternalType('array', $fieldTypes);
        $this->assertCount(2, $fieldTypes);
        $this->assertArrayHasKey('one', $fieldTypes);
        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\FieldType\\FieldType',
            $fieldTypes['one']
        );
        $this->assertArrayHasKey('two', $fieldTypes);
        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\FieldType\\FieldType',
            $fieldTypes['two']
        );
    }
}
