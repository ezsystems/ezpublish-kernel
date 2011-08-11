<?php
/**
 * File contains: ezp\Base\Tests\ReadOnlyCollectionTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Tests;
use ezp\Base\Collection\ReadOnly;

/**
 * Test case for Collection\ReadOnly class
 *
 */
class ReadOnlyCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadOnlyCollection
     */
    private $collection;

    public function setUp()
    {
        parent::setUp();
        $this->collection = new ReadOnly( array( 1, 55, 'collection', 'test' ) );
    }

    /**
     * Test constructor
     * @covers ezp\Base\Collection\ReadOnly::__construct
     */
    public function testFromArray()
    {
        $this->assertInstanceOf( 'ezp\\Base\\Collection\\ReadOnly', new ReadOnly( array( 1, 55, 'collection', 'test' ) ) );
    }

    /**
     * Test offsetExists
     * @covers ezp\Base\Collection\ReadOnly::offsetExists
     */
    public function testExists()
    {
        $this->assertTrue( isset( $this->collection[3] ) );
    }

    /**
     * Test offsetGet
     * @covers ezp\Base\Collection\ReadOnly::offsetGet
     */
    public function testGet()
    {
        $this->assertEquals( 1, $this->collection[0] );
        $this->assertEquals( 55, $this->collection[1] );
        $this->assertEquals( 'test', $this->collection[3] );
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     * @covers ezp\Base\Collection\ReadOnly::offsetGet
     */
    public function testGetInvalid()
    {
        $this->collection[4];
    }

    /**
     * @expectedException ezp\Base\Exception\ReadOnly
     * @covers ezp\Base\Collection\ReadOnly::offsetSet
     */
    public function testSetInvalid()
    {
        $this->collection[2] = 42;
    }

    /**
     * @expectedException ezp\Base\Exception\ReadOnly
     * @covers ezp\Base\Collection\ReadOnly::offsetSet
     */
    public function testSetAppendInvalid()
    {
        $this->collection[] = 42;
    }

    /**
     * @expectedException ezp\Base\Exception\ReadOnly
     * @covers ezp\Base\Collection\ReadOnly::offsetUnset
     */
    public function testUnset()
    {
        unset( $this->collection[2] );
    }
}
