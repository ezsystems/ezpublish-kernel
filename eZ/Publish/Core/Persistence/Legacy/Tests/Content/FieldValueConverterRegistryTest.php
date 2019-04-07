<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValueConverterRegistryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as Registry;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

/**
 * Test case for FieldValue Converter Registry.
 */
class FieldValueConverterRegistryTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry::register
     */
    public function testRegister(): void
    {
        $converter = $this->getFieldValueConverterMock();
        $registry = new Registry(array('some-type' => $converter));

        $this->assertAttributeSame(
            array(
                'some-type' => $converter,
            ),
            'converterMap',
            $registry
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry::getConverter
     */
    public function testGetStorage(): void
    {
        $converter = $this->getFieldValueConverterMock();
        $registry = new Registry(array('some-type' => $converter));

        $res = $registry->getConverter('some-type');

        $this->assertSame(
            $converter,
            $res
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry::getConverter
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     * @expectedException \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     */
    public function testGetNotFound(): void
    {
        $registry = new Registry(array());

        $registry->getConverter('not-found');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry::hasConverter
     */
    public function testHasStorage(): void
    {
        $converter = $this->getFieldValueConverterMock();
        $registry = new Registry(array('some-type' => $converter));

        $this->assertTrue(
            $registry->hasConverter('some-type')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry::hasConverter
     */
    public function testHasNoStorage(): void
    {
        $converter = $this->getFieldValueConverterMock();
        $registry = new Registry(array('some-type' => $converter));

        $this->assertFalse(
            $registry->hasConverter('some-other-type')
        );
    }

    /**
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFieldValueConverterMock()
    {
        return $this->createMock(Converter::class);
    }
}
