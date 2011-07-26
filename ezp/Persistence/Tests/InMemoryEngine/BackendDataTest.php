<?php
/**
 * File contains: ezp\Persistence\Tests\InMemoryEngine\BackendDataTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\InMemoryEngine;
use PHPUnit_Framework_TestCase;

/**
 * Test case for Handler using in memory storage.
 *
 */
class BackendDataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->backend = new Backend();

        for ( $i = 0; $i < 10; ++$i)
            $this->backend->create(
                "Content",
                array(
                    "foo{$i}" => "bar{$i}",
                    "baz{$i}" => "buzz{$i}",
                    "int" => 42,
                )
            );

        for ( $i = 0; $i < 10; ++$i)
            $this->backend->create(
                "Content",
                array(
                    "bar{$i}" => "foo{$i}",
                    "baz{$i}" => "buzz{$i}",
                    "float" => 42.42,
                )
            );
    }

    /**
     * Test finding content without results
     *
     * @dataProvider providerForFindEmpty
     * @covers ezp\Persistence\Tests\InMemoryEngine\Backend::find
     */
    public function testFindEmpty( $searchData )
    {
        $this->assertEquals(
            array(),
            $this->backend->find( "Content", $searchData )
        );
    }

    public function providerForFindEmpty()
    {
        return array(
            array( array( "unexistingKey" => "bar0" ) ),
            array( array( "unexistingKey" => "bar0", "baz0" => "buzz0" ) ),
            array( array( "foo0" => "unexistingValue" ) ),
            array( array( "foo0" => "unexistingValue", "baz0" => "buzz0" ) ),
            array( array( "foo0" => "" ) ),
            array( array( "foo0" => "bar0", "baz0" => "" ) ),
            array( array( "foo0" => "bar0", "baz0" => "buzz1" ) ),
            array( array( "foo0" ) ),
            array( array( "int" ) ),
            array( array( "float" ) ),
        );
    }

    /**
     * Test finding content with results
     *
     * @dataProvider providerForFind
     * @covers ezp\Persistence\Tests\InMemoryEngine\Backend::find
     */
    public function testFind( $searchData, $result )
    {
        $this->assertEquals(
            $result,
            $this->backend->find( "Content", $searchData )
        );
    }

    public function providerForFind()
    {
        return array(
            array(
                array( "foo0" => "bar0" ),
                array(
                    array(
                        "id" => 1,
                        "foo0" => "bar0",
                        "baz0" => "buzz0",
                        "int" => 42,
                    )
                )
            ),
            array(
                array( "bar5" => "foo5" ),
                array(
                    array(
                        "id" => 16,
                        "bar5" => "foo5",
                        "baz5" => "buzz5",
                        "float" => 42.42,
                    )
                )
            ),
            array(
                array( "baz5" => "buzz5" ),
                array(
                    array(
                        "id" => 6,
                        "foo5" => "bar5",
                        "baz5" => "buzz5",
                        "int" => 42,
                    ),
                    array(
                        "id" => 16,
                        "bar5" => "foo5",
                        "baz5" => "buzz5",
                        "float" => 42.42,
                    )
                )
            ),
            array(
                array( "int" => 42 ),
                array(
                    array(
                        "id" => 1,
                        "foo0" => "bar0",
                        "baz0" => "buzz0",
                        "int" => 42,
                    ),
                    array(
                        "id" => 2,
                        "foo1" => "bar1",
                        "baz1" => "buzz1",
                        "int" => 42,
                    ),
                    array(
                        "id" => 3,
                        "foo2" => "bar2",
                        "baz2" => "buzz2",
                        "int" => 42,
                    ),
                    array(
                        "id" => 4,
                        "foo3" => "bar3",
                        "baz3" => "buzz3",
                        "int" => 42,
                    ),
                    array(
                        "id" => 5,
                        "foo4" => "bar4",
                        "baz4" => "buzz4",
                        "int" => 42,
                    ),
                    array(
                        "id" => 6,
                        "foo5" => "bar5",
                        "baz5" => "buzz5",
                        "int" => 42,
                    ),
                    array(
                        "id" => 7,
                        "foo6" => "bar6",
                        "baz6" => "buzz6",
                        "int" => 42,
                    ),
                    array(
                        "id" => 8,
                        "foo7" => "bar7",
                        "baz7" => "buzz7",
                        "int" => 42,
                    ),
                    array(
                        "id" => 9,
                        "foo8" => "bar8",
                        "baz8" => "buzz8",
                        "int" => 42,
                    ),
                    array(
                        "id" => 10,
                        "foo9" => "bar9",
                        "baz9" => "buzz9",
                        "int" => 42,
                    ),
                ),
            ),
            array(
                array( "int" => "42" ),
                array(
                    array(
                        "id" => 1,
                        "foo0" => "bar0",
                        "baz0" => "buzz0",
                        "int" => 42,
                    ),
                    array(
                        "id" => 2,
                        "foo1" => "bar1",
                        "baz1" => "buzz1",
                        "int" => 42,
                    ),
                    array(
                        "id" => 3,
                        "foo2" => "bar2",
                        "baz2" => "buzz2",
                        "int" => 42,
                    ),
                    array(
                        "id" => 4,
                        "foo3" => "bar3",
                        "baz3" => "buzz3",
                        "int" => 42,
                    ),
                    array(
                        "id" => 5,
                        "foo4" => "bar4",
                        "baz4" => "buzz4",
                        "int" => 42,
                    ),
                    array(
                        "id" => 6,
                        "foo5" => "bar5",
                        "baz5" => "buzz5",
                        "int" => 42,
                    ),
                    array(
                        "id" => 7,
                        "foo6" => "bar6",
                        "baz6" => "buzz6",
                        "int" => 42,
                    ),
                    array(
                        "id" => 8,
                        "foo7" => "bar7",
                        "baz7" => "buzz7",
                        "int" => 42,
                    ),
                    array(
                        "id" => 9,
                        "foo8" => "bar8",
                        "baz8" => "buzz8",
                        "int" => 42,
                    ),
                    array(
                        "id" => 10,
                        "foo9" => "bar9",
                        "baz9" => "buzz9",
                        "int" => 42,
                    ),
                ),
            ),
            array(
                array( "float" => 42.42 ),
                array(
                    array(
                        "id" => 11,
                        "bar0" => "foo0",
                        "baz0" => "buzz0",
                        "float" => 42.42,
                    ),
                    array(
                        "id" => 12,
                        "bar1" => "foo1",
                        "baz1" => "buzz1",
                        "float" => 42.42,
                    ),
                    array(
                        "id" => 13,
                        "bar2" => "foo2",
                        "baz2" => "buzz2",
                        "float" => 42.42,
                    ),
                    array(
                        "id" => 14,
                        "bar3" => "foo3",
                        "baz3" => "buzz3",
                        "float" => 42.42,
                    ),
                    array(
                        "id" => 15,
                        "bar4" => "foo4",
                        "baz4" => "buzz4",
                        "float" => 42.42,
                    ),
                    array(
                        "id" => 16,
                        "bar5" => "foo5",
                        "baz5" => "buzz5",
                        "float" => 42.42,
                    ),
                    array(
                        "id" => 17,
                        "bar6" => "foo6",
                        "baz6" => "buzz6",
                        "float" => 42.42,
                    ),
                    array(
                        "id" => 18,
                        "bar7" => "foo7",
                        "baz7" => "buzz7",
                        "float" => 42.42,
                    ),
                    array(
                        "id" => 19,
                        "bar8" => "foo8",
                        "baz8" => "buzz8",
                        "float" => 42.42,
                    ),
                    array(
                        "id" => 20,
                        "bar9" => "foo9",
                        "baz9" => "buzz9",
                        "float" => 42.42,
                    ),
                ),
            ),
        );
    }

    /**
     * Test loading content without results
     *
     * @dataProvider providerForLoadEmpty
     * @covers ezp\Persistence\Tests\InMemoryEngine\Backend::load
     */
    public function testLoadEmpty( $searchData )
    {
        $this->assertNull(
            $this->backend->load( "Content", $searchData )
        );
    }

    public function providerForLoadEmpty()
    {
        return array(
            array( "" ),
            array( null ),
            array( 0 ),
            array( 0.1 ),
            array( "0" ),
            array( "unexistingKey" ),
        );
    }

    /**
     * Test loading content with results
     *
     * @dataProvider providerForLoad
     * @covers ezp\Persistence\Tests\InMemoryEngine\Backend::load
     */
    public function testLoad( $searchData, $result )
    {
        $this->assertEquals(
            $result,
            $this->backend->load( "Content", $searchData )
        );
    }

    public function providerForLoad()
    {
        return array(
            array(
                1,
                array(
                    "id" => 1,
                    "foo0" => "bar0",
                    "baz0" => "buzz0",
                    "int" => 42,
                )
            ),
            array(
                "1",
                array(
                    "id" => 1,
                    "foo0" => "bar0",
                    "baz0" => "buzz0",
                    "int" => 42,
                )
            ),
            array(
                2,
                array(
                    "id" => 2,
                    "foo1" => "bar1",
                    "baz1" => "buzz1",
                    "int" => 42,
                )
            ),
            array(
                11,
                array(
                    "id" => 11,
                    "bar0" => "foo0",
                    "baz0" => "buzz0",
                    "float" => 42.42,
                )
            ),
        );
    }

    /**
     * Test updating content on unexisting ID
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\Backend::update
     */
    public function testUpdateUnexistingId()
    {
        $this->assertFalse(
            $this->backend->update( "Content", 0, array() )
        );
    }

    /**
     * Test updating content with an extra attribute
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\Backend::update
     */
    public function testUpdateNewAttribute()
    {
        $this->assertTrue(
            $this->backend->update( "Content", 1, array( "new" => "data" ) )
        );
        $this->assertEquals(
            array(
                "id" => 1,
                "foo0" => "bar0",
                "baz0" => "buzz0",
                "int" => 42,
                "new" => "data",
            ),
            $this->backend->load( "Content", 1 )
        );
    }

    /**
     * Test updating content
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\Backend::update
     */
    public function testUpdate()
    {
        $this->assertTrue(
            $this->backend->update( "Content", 2, array( "foo1" => "data" ) )
        );
        $this->assertEquals(
            array(
                "id" => 2,
                "foo1" => "data",
                "baz1" => "buzz1",
                "int" => 42,
            ),
            $this->backend->load( "Content", 2 )
        );
    }

    /**
     * Test updating content with a null value
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\Backend::update
     */
    public function testUpdateWithNullValue()
    {
        $this->assertTrue(
            $this->backend->update( "Content", 3, array( "foo2" => null ) )
        );
        $this->assertEquals(
            array(
                "id" => 3,
                "foo2" => null,
                "baz2" => "buzz2",
                "int" => 42,
            ),
            $this->backend->load( "Content", 3 )
        );
    }
}
