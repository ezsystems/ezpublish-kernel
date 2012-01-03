<?php
/**
 * File contains: ezp\Persistence\Storage\InMemory\BackendTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\InMemory;
use PHPUnit_Framework_TestCase,
    stdClass,
    ezp\Persistence\Storage\InMemory\Backend;

/**
 * Test case for Handler using in memory storage.
 *
 */
class BackendTest extends PHPUnit_Framework_TestCase
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

        $this->backend = new Backend( array( 'Content' => array() ) );
    }

    /**
     * Test creating content with a wrong type.
     *
     * @param mixed $type Wrong type to create
     * @expectedException ezp\Base\Exception\InvalidArgumentValue
     * @dataProvider providerForWrongType
     * @covers ezp\Persistence\Storage\InMemory\Backend::create
     */
    public function testCreateWrongType( $type )
    {
        $this->backend->create( $type, array() );
    }

    /**
     * Test loading content with a wrong type.
     *
     * @param mixed $type Wrong type to load
     * @expectedException ezp\Base\Exception\InvalidArgumentValue
     * @dataProvider providerForWrongType
     * @covers ezp\Persistence\Storage\InMemory\Backend::load
     */
    public function testLoadWrongType( $type )
    {
        $this->backend->load( $type, 1 );
    }

    /**
     * Test finding content with a wrong type.
     *
     * @param mixed $type Wrong type to find
     * @expectedException ezp\Base\Exception\InvalidArgumentValue
     * @dataProvider providerForWrongType
     * @covers ezp\Persistence\Storage\InMemory\Backend::find
     */
    public function testFindWrongType( $type )
    {
        $this->backend->find( $type, array() );
    }

    /**
     * Test counting content with a wrong type.
     *
     * @param mixed $type Wrong type to count
     * @expectedException ezp\Base\Exception\InvalidArgumentValue
     * @dataProvider providerForWrongType
     * @covers ezp\Persistence\Storage\InMemory\Backend::count
     */
    public function testCountWrongType( $type )
    {
        $this->backend->count( $type, array() );
    }

    /**
     * Test updating content with a wrong type.
     *
     * @param mixed $type Wrong type to update
     * @expectedException ezp\Base\Exception\InvalidArgumentValue
     * @dataProvider providerForWrongType
     * @covers ezp\Persistence\Storage\InMemory\Backend::update
     */
    public function testUpdateWrongType( $type )
    {
        $this->backend->update( $type, 1, array() );
    }

    /**
     * Test deleting content with a wrong type.
     *
     * @param mixed $type Wrong type to delete
     * @expectedException ezp\Base\Exception\InvalidArgumentValue
     * @dataProvider providerForWrongType
     * @covers ezp\Persistence\Storage\InMemory\Backend::delete
     */
    public function testDeleteWrongType( $type )
    {
        $this->backend->delete( $type, 1 );
    }

    /**
     * Test creating content
     *
     * @covers ezp\Persistence\Storage\InMemory\Backend::create
     */
    public function testCreate()
    {
        $content = $this->backend->create( "Content", array( "sectionId" => 2 ));
        $this->assertEquals( 1, $content->id );
        $this->assertEquals( 2, $content->sectionId );
        $this->assertEquals( null, $content->ownerId );
    }

    /**
     * Test creating multiple content
     *
     * @covers ezp\Persistence\Storage\InMemory\Backend::create
     */
    public function testCreateMultiple()
    {
        for ( $i = 1; $i <= 10; ++$i)
        {
            $content = $this->backend->create( "Content", array( "sectionId" => 2 ) );
            $this->assertEquals( $i, $content->id );
        }
    }

    /**
     * Provider for test*WrongType.
     *
     * @see testCreateWrongType
     * @see testLoadWrongType
     */
    public function providerForWrongType()
    {
        return array(
            array( 'wrongType' ),
            array( null ),
            array( false ),
            array( true ),
            array( array() ),
            array( new stdClass ),
        );
    }
}
