<?php
/**
 * File contains: ezp\base\tests\ReadOnlyCollectionTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base_tests
 */

namespace ezp\base\tests;

/**
 * Test case for ReadOnlyCollection class
 *
 * @package ezp
 * @subpackage base_tests
 */
class ReadOnlyCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\base\ReadOnlyCollection
     */
    private $collection;

    public function __construct()
    {
        parent::__construct();
        $this->setName( "ReadOnlyCollectionTest class tests" );
        $this->collection = new \ezp\base\ReadOnlyCollection( array( 1, 55, 'collection', 'test' ) );
    }

    /**
     * Test offsetExists
     */
    public function testFromArray()
    {
        $this->assertEquals( 'ezp\base\ReadOnlyCollection', get_class( $this->collection ) );
    }
    /**
     * Test offsetExists
     */
    public function testExists()
    {
        $this->assertTrue( isset( $this->collection[3] ) );
    }

    /**
     * Test offsetGet
     */
    public function testGet()
    {
        $this->assertEquals( 1, $this->collection[0] );
        $this->assertEquals( 55, $this->collection[1] );
        $this->assertEquals( 'test', $this->collection[3] );
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testGetInvalid()
    {
        $this->collection[4];
    }

    /**
     * @expectedException \ezp\base\Exception\ReadOnly
     */
    public function testSetInvalid()
    {
        $this->collection[2] = 42;
    }

    /**
     * @expectedException \ezp\base\Exception\ReadOnly
     */
    public function testSetAppendInvalid()
    {
        $this->collection[] = 42;
    }

    /**
     * @expectedException \ezp\base\Exception\ReadOnly
     */
    public function testUnSet()
    {
        unset( $this->collection[2] );
    }
}
