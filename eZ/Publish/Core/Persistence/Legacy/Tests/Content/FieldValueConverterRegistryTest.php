<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValueConverterRegistryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as Registry;

/**
 * Test case for FieldValue Converter Registry.
 */
class FieldValueConverterRegistryTest extends TestCase
{
    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry::register
     */
    public function testRegister()
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
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry::getConverter
     */
    public function testGetStorage()
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
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry::getConverter
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     * @expectedException eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     */
    public function testGetNotFound()
    {
        $registry = new Registry(array());

        $registry->getConverter('not-found');
    }

    /**
     * Returns a mock for Storage.
     *
     * @return Storage
     */
    protected function getFieldValueConverterMock()
    {
        return $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter'
        );
    }
}
