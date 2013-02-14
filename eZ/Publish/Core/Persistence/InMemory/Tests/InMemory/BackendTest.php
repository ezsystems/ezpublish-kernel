<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\BackendTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests\InMemory;

use PHPUnit_Framework_TestCase;
use stdClass;
use eZ\Publish\Core\Persistence\InMemory\Backend;

/**
 * Test case for Handler using in memory storage.
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

        $this->backend = new Backend(
            array(
                'Content' => array(),
                'Content\\ContentInfo' => array()
            )
        );
    }

    /**
     * Tear down test (properties)
     */
    protected function tearDown()
    {
        unset( $this->backend );
        parent::tearDown();
    }

    /**
     * Test creating content with a wrong type.
     *
     * @param mixed $type Wrong type to create
     *
     * @expectedException eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     * @dataProvider providerForWrongType
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::create
     */
    public function testCreateWrongType( $type )
    {
        $this->backend->create( $type, array() );
    }

    /**
     * Test loading content with a wrong type.
     *
     * @param mixed $type Wrong type to load
     *
     * @expectedException eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     * @dataProvider providerForWrongType
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::load
     */
    public function testLoadWrongType( $type )
    {
        $this->backend->load( $type, 1 );
    }

    /**
     * Test finding content with a wrong type.
     *
     * @param mixed $type Wrong type to find
     *
     * @expectedException eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     * @dataProvider providerForWrongType
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::find
     */
    public function testFindWrongType( $type )
    {
        $this->backend->find( $type, array() );
    }

    /**
     * Test counting content with a wrong type.
     *
     * @param mixed $type Wrong type to count
     *
     * @expectedException eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     * @dataProvider providerForWrongType
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::count
     */
    public function testCountWrongType( $type )
    {
        $this->backend->count( $type, array() );
    }

    /**
     * Test updating content with a wrong type.
     *
     * @param mixed $type Wrong type to update
     *
     * @expectedException eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     * @dataProvider providerForWrongType
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::update
     */
    public function testUpdateWrongType( $type )
    {
        $this->backend->update( $type, 1, array() );
    }

    /**
     * Test deleting content with a wrong type.
     *
     * @param mixed $type Wrong type to delete
     *
     * @expectedException eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     * @dataProvider providerForWrongType
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::delete
     */
    public function testDeleteWrongType( $type )
    {
        $this->backend->delete( $type, 1 );
    }

    /**
     * Test creating content
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::create
     */
    public function testCreate()
    {
        $content = $this->backend->create( "Content\\ContentInfo", array( "sectionId" => 2 ), true );
        $this->assertEquals( 1, $content->id );
        $this->assertEquals( 2, $content->sectionId );
        $this->assertEquals( null, $content->ownerId );
    }

    /**
     * Test creating multiple content
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::create
     */
    public function testCreateMultiple()
    {
        for ( $i = 1; $i <= 10; ++$i )
        {
            $content = $this->backend->create( "Content\\ContentInfo", array( "sectionId" => 2 ), true );
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
