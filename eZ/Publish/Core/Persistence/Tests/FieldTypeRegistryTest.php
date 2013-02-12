<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Tests\FieldTypeRegistryTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Tests;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\FieldTypeRegistry;

/**
 * Test case for FieldTypeRegistry
 */
class FieldTypeRegistryTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::__construct
     *
     * @return void
     */
    public function testConstructor()
    {
        $fieldType = $this->getFieldTypeMock();
        $registry = new FieldTypeRegistry( array( "some-type" => $fieldType ) );

        $this->assertAttributeSame(
            array(
                "some-type" => $fieldType,
            ),
            "coreFieldTypeMap",
            $registry
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getFieldType
     *
     * @return void
     */
    public function testGetFieldTypeInstance()
    {
        $instance = $this->getFieldTypeMock();
        $registry = new FieldTypeRegistry( array( "some-type" => $instance ) );

        $result = $registry->getFieldType( "some-type" );

        $this->assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\FieldType", $result );
        $this->assertAttributeSame(
            $instance,
            "internalFieldType",
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getFieldType
     *
     * @return void
     */
    public function testGetFieldTypeCallable()
    {
        $instance = $this->getFieldTypeMock();
        $closure = function () use ( $instance )
        {
            return $instance;
        };
        $registry = new FieldTypeRegistry( array( "some-type" => $closure ) );

        $result = $registry->getFieldType( "some-type" );

        $this->assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\FieldType", $result );
        $this->assertAttributeSame(
            $instance,
            "internalFieldType",
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getFieldType
     *
     * @expectedException \RuntimeException
     *
     * @return void
     */
    public function testGetNotFound()
    {
        $registry = new FieldTypeRegistry( array() );
        $registry->getFieldType( "not-found" );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::getFieldType
     *
     * @expectedException \RuntimeException
     *
     * @return void
     */
    public function testGetNotCallableOrInstance()
    {
        $registry = new FieldTypeRegistry( array( "some-type" => new \DateTime() ) );
        $registry->getFieldType( "some-type" );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\FieldTypeRegistry::register
     *
     * @return void
     */
    public function testRegister()
    {
        $fieldType = $this->getFieldTypeMock();
        $registry = new FieldTypeRegistry( array() );
        $registry->register( "some-type", $fieldType );

        $this->assertAttributeSame(
            array(
                "some-type" => $fieldType,
            ),
            "coreFieldTypeMap",
            $registry
        );
    }

    /**
     * Returns a mock for persistence field type.
     *
     * @return \eZ\Publish\SPI\Persistence\FieldType
     */
    protected function getFieldTypeMock()
    {
        return $this->getMock(
            "eZ\\Publish\\SPI\\FieldType\\FieldType"
        );
    }
}
