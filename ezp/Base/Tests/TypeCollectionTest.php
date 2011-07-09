<?php
/**
 * File contains: ezp\Base\Tests\TypeCollectionTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base_tests
 */

namespace ezp\Base\Tests;

/**
 * Test case for TypeCollection class
 *
 * @package ezp
 * @subpackage base_tests
 */
class TypeCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Base\TypeCollection
     */
    private $collection;

    public function __construct()
    {
        parent::__construct();
        $this->setName( "ReadOnlyCollectionTest class tests" );
        $this->collection = new \ezp\Base\TypeCollection(
            'ezp\Base\Tests\TypeCollectionTestTypeClass',
            array(
                new TypeCollectionTestTypeClass( 1 ),
                new TypeCollectionTestTypeClass( 42 ),
                new TypeCollectionTestTypeClass( 22 ),
                'key' => new TypeCollectionTestTypeClass( 0 ),
            )
        );
    }
    /**
     * Test offsetExists
     */
    public function testExists()
    {
        $this->assertTrue( isset( $this->collection[2] ) );
        $this->assertTrue( isset( $this->collection['key'] ) );
    }

    /**
     * Test offsetGet
     */
    public function testGet()
    {
        $this->assertEquals( 42, $this->collection[1]->id );
        $this->assertEquals( 0, $this->collection['key']->id );
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testGetInvalid()
    {
        $this->collection[4];
    }

    /**
     * Test set
     */
    public function testSet()
    {
        $this->collection['temp'] = new TypeCollectionTestTypeClass( 13 );
        $this->assertEquals( 5, count( $this->collection ) );
    }
    /**
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     */
    public function testSetInvalid()
    {
        $this->collection[2] = 42;
    }

    /**
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     */
    public function testSetAppendInvalid()
    {
        $this->collection[] = 42;
    }

    /**
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     */
    public function testAppendInvalid()
    {
        $this->collection->append( 42 );
    }

    /**
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     */
    public function testExchangeArray()
    {
        $this->collection->exchangeArray( array( 42 ) );
    }

    /**
     * test unset
     */
    public function testUnSet()
    {
        unset( $this->collection['key'] );
        $this->assertEquals( 3, count( $this->collection ) );
    }
}


/**
 * Used by TypeCollectionTest as the type items in collection needs to be
 *
 * @internal
 */
class TypeCollectionTestTypeClass
{
    public function __construct( $id )
    {
        $this->id = $id;
    }
}
