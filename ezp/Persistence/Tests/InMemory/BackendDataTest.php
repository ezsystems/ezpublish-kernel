<?php
/**
 * File contains: ezp\Persistence\Storage\InMemory\BackendDataTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\InMemory;
use PHPUnit_Framework_TestCase,
    ReflectionObject,
    ezp\Base\Exception\NotFound,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Location,
    ezp\Persistence\Content\Location\CreateStruct as LocationCreateStruct,
    ezp\Persistence\Storage\InMemory\Backend;

/**
 * Test case for Handler using in memory storage.
 *
 */
class BackendDataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Persistence\Storage\InMemory\Backend
     */
    protected $backend;

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        // Create a new backend from JSON data and empty Content data to make it clean
        $this->backend = new Backend( json_decode( file_get_contents( 'ezp/Persistence/Storage/InMemory/data.json' ), true ) );
    }

    protected function tearDown()
    {
        unset( $this->backend );
        parent::tearDown();
    }

    /**
     * Clear Content in backend data and custom data for testing
     */
    protected function insertCustomContent()
    {
        $refObj = new ReflectionObject( $this->backend );
        $refData = $refObj->getProperty( 'data' );
        $refData->setAccessible( true );
        $data = $refData->getValue( $this->backend );
        $data['Content'] = array();
        $refData->setValue( $this->backend, $data );

        for ( $i = 0; $i < 10; ++$i)
            $this->backend->create(
                "Content",
                array(
                    "name" => "bar{$i}",
                    "ownerId" => 42
                )
            );

        for ( $i = 0; $i < 10; ++$i)
            $this->backend->create(
                "Content",
                array(
                    "name" => "foo{$i}",
                )
            );
    }

    /**
     * Test finding content without results
     *
     * @dataProvider providerForFindEmpty
     * @covers ezp\Persistence\Storage\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindEmpty( $searchData )
    {
        $this->insertCustomContent();
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
     * @covers ezp\Persistence\Storage\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFind( $searchData, $result )
    {
        $this->insertCustomContent();
        $list = $this->backend->find( "Content", $searchData );
        foreach ( $list as $key => $content )
        {
            $this->assertEquals( $result[$key]['id'], $content->id );
            $this->assertEquals( $result[$key]['name'], $content->name );
        }
    }

    public function providerForFind()
    {
        return array(
            array(
                array( "name" => "bar0" ),
                array(
                    array(
                        "id" => 1,
                        "name" => "bar0",
                    )
                )
            ),
            array(
                array( "name" => "foo5" ),
                array(
                    array(
                        "id" => 16,
                        "name" => "foo5",
                    )
                )
            ),
            array(
                array( "ownerId" => 42 ),
                array(
                    array(
                        "id" => 1,
                        "name" => "bar0",
                    ),
                    array(
                        "id" => 2,
                        "name" => "bar1",
                    ),
                    array(
                        "id" => 3,
                        "name" => "bar2",
                    ),
                    array(
                        "id" => 4,
                        "name" => "bar3",
                    ),
                    array(
                        "id" => 5,
                        "name" => "bar4",
                    ),
                    array(
                        "id" => 6,
                        "name" => "bar5",
                    ),
                    array(
                        "id" => 7,
                        "name" => "bar6",
                    ),
                    array(
                        "id" => 8,
                        "name" => "bar7",
                    ),
                    array(
                        "id" => 9,
                        "name" => "bar8",
                    ),
                    array(
                        "id" => 10,
                        "name" => "bar9",
                    ),
                ),
            ),
        );
    }

    /**
     * Test finding content with multiple ids
     *
     * @covers ezp\Persistence\Storage\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindMultipleIds()
    {
        $this->insertCustomContent();
        $searchIds = array( 3, 5, 7 );
        $list = $this->backend->find( 'Content', array( 'id' => $searchIds ) );
        self::assertEquals( count( $searchIds ), count( $list ) );

        foreach ( $list as $vo )
        {
            self::assertInstanceOf( 'ezp\\Persistence\\Content', $vo );
            self::assertContains( $vo->id, $searchIds );
        }
    }

    /**
     * Test finding content with results
     *
     * @covers ezp\Persistence\Storage\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindMatchOnArray()
    {
        $list = $this->backend->find( "Content\\Type", array( "groupIds" => 1 ) );
        $this->assertEquals( 1, count( $list ) );
        foreach ( $list as $key => $content )
        {
            $this->assertEquals( 1, $content->id );
            $this->assertEquals( 'folder', $content->identifier );
        }
    }

    /**
     * Test finding content with results using join
     *
     * @covers ezp\Persistence\Storage\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindJoin()
    {
        /**
         * @var \ezp\Persistence\Content[] $list
         */
        $list = $this->backend->find( "Content",
                                      array( "id" => 1 ),
                                      array( 'locations' => array(
                                          'type' => 'Content\\Location',
                                          'match' => array( 'contentId' => 'id' ) )
                                      ));
        $this->assertEquals( 1, count( $list ) );
        foreach ( $list as $key => $content )
        {
            $this->assertTrue( $content instanceof Content );
            $this->assertEquals( 1, $content->id );
            $this->assertEquals( 'eZ Publish', $content->name );
            $this->assertEquals( 1, count( $content->locations ) );
            foreach ( $content->locations as $location )
            {
                $this->assertTrue( $location instanceof Location );
                $this->assertEquals( 2, $location->id );
                $this->assertEquals( 1, $location->contentId );
            }
        }
    }

    /**
     * Test finding content with results using join and deep matching
     *
     * @covers ezp\Persistence\Storage\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindJoinDeepMatch()
    {
        /**
         * @var \ezp\Persistence\Content[] $list
         */
        $list = $this->backend->find( "Content",
                                      array( "locations" => array( 'id' => 2 ) ),
                                      array( 'locations' => array(
                                          'type' => 'Content\\Location',
                                          'match' => array( 'contentId' => 'id' ) )
                                      ));
        $this->assertEquals( 1, count( $list ) );
        foreach ( $list as $content )
        {
            $this->assertTrue( $content instanceof Content );
            $this->assertEquals( 1, $content->id );
            $this->assertEquals( 'eZ Publish', $content->name );
            $this->assertEquals( 1, count( $content->locations ) );
            foreach ( $content->locations as $location )
            {
                $this->assertTrue( $location instanceof Location );
                $this->assertEquals( 2, $location->id );
                $this->assertEquals( 1, $location->contentId );
            }
        }
    }

    /**
     * Test finding content with results using join and deep matching where there are several sub elements
     *
     * @covers ezp\Persistence\Storage\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindJoinDeepMatchWithSeveral()
    {
        // Create a new location on content object 1 so it has 2
        $location = new LocationCreateStruct();
        $location->contentId = 1;
        $location->contentVersion = 1;
        $location->parentId = 1;
        $location->mainLocationId = 2;
        $location->remoteId = 'string';
        $location->pathIdentificationString = '/1/3/';
        $location->sortField = Location::SORT_FIELD_MODIFIED;
        $location->sortOrder = Location::SORT_ORDER_DESC;
        $this->backend->create( 'Content\\Location', (array) $location );
        /**
         * @var \ezp\Persistence\Content[] $list
         */
        $list = $this->backend->find( "Content",
                                      array( "locations" => array( 'id' => 2 ) ),
                                      array( 'locations' => array(
                                          'type' => 'Content\\Location',
                                          'match' => array( 'contentId' => 'id' ) )
                                      ));
        $this->assertEquals( 1, count( $list ) );
        foreach ( $list as $content )
        {
            $this->assertTrue( $content instanceof Content );
            $this->assertEquals( 1, $content->id );
            $this->assertEquals( 'eZ Publish', $content->name );
            $this->assertEquals( 2, count( $content->locations ) );
            foreach ( $content->locations as $location )
            {
                $this->assertTrue( $location instanceof Location );
                $this->assertEquals( 1, $location->contentId );
            }
        }
    }

    /**
     * Test finding content with results using join and deep matching where match collides with join
     *
     * @expectedException ezp\Base\Exception\Logic
     * @covers ezp\Persistence\Storage\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindJoinDeepMatchCollision()
    {
        $this->backend->find( "Content",
                                      array( "locations" => array( 'contentId' => 2 ) ),
                                      array( 'locations' => array(
                                          'type' => 'Content\\Location',
                                          'match' => array( 'contentId' => 'id' ) )
                                      ));
    }

    /**
     * Test finding content with results using several levels of join
     *
     * @covers ezp\Persistence\Storage\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindSubJoin()
    {
        $this->markTestIncomplete("Pending Content\\Version and Content\\Field data in data.json");
    }

    /**
     * Test finding content with wildcard
     *
     * @covers \ezp\Persistence\Storage\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindWildcard()
    {
        $this->insertCustomContent();
        $list = $this->backend->find( 'Content', array( 'name' => 'foo%' ) );
        foreach ( $list as $vo )
        {
            self::assertInstanceOf( 'ezp\\Persistence\\Content', $vo );
            self::assertTrue( strpos( $vo->name, 'foo' ) === 0 );
        }
    }

    /**
     * Test counting content without results
     *
     * @dataProvider providerForFindEmpty
     * @covers ezp\Persistence\Storage\InMemory\Backend::count
     * @group inMemoryBackend
     */
    public function testCountEmpty( $searchData )
    {
        $this->insertCustomContent();
        $this->assertEquals(
            0,
            $this->backend->count( "Content", $searchData )
        );
    }

    /**
     * Test counting content with results
     *
     * @dataProvider providerForFind
     * @covers ezp\Persistence\Storage\InMemory\Backend::count
     * @group inMemoryBackend
     */
    public function testCount( $searchData, $result )
    {
        $this->insertCustomContent();
        $this->assertEquals(
            count( $result ),
            $this->backend->count( "Content", $searchData )
        );
    }

    /**
     * Test counting content with results using join and deep matching
     *
     * @covers ezp\Persistence\Storage\InMemory\Backend::count
     * @group inMemoryBackend
     */
    public function testCountJoinDeepMatch()
    {
        $this->assertEquals( 1, $this->backend->count( "Content",
                                      array( "locations" => array( 'id' => 2 ) ),
                                      array( 'locations' => array(
                                          'type' => 'Content\\Location',
                                          'match' => array( 'contentId' => 'id' ) )
                                      )) );
    }

    /**
     * Test count content with results using join and deep matching where match collides with join
     *
     * @expectedException ezp\Base\Exception\Logic
     * @covers ezp\Persistence\Storage\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testCountJoinDeepMatchCollision()
    {
        $this->backend->count( "Content",
                                      array( "locations" => array( 'contentId' => 2 ) ),
                                      array( 'locations' => array(
                                          'type' => 'Content\\Location',
                                          'match' => array( 'contentId' => 'id' ) )
                                      ));
    }

    /**
     * Test loading content without results
     *
     * @dataProvider providerForLoadEmpty
     * @expectedException \ezp\Base\Exception\NotFound
     * @covers ezp\Persistence\Storage\InMemory\Backend::load
     * @group inMemoryBackend
     */
    public function testLoadEmpty( $searchData )
    {
        $this->insertCustomContent();
        $this->backend->load( "Content", $searchData );
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
     * @covers ezp\Persistence\Storage\InMemory\Backend::load
     * @group inMemoryBackend
     */
    public function testLoad( $searchData, $result )
    {
        $this->insertCustomContent();
        $content = $this->backend->load( "Content", $searchData );
        foreach ( $result as $name => $value )
            $this->assertEquals( $value, $content->$name );
    }

    public function providerForLoad()
    {
        return array(
            array(
                1,
                array(
                    "id" => 1,
                    "name" => "bar0",
                    "ownerId" => 42,
                )
            ),
            array(
                "1",
                array(
                    "id" => 1,
                    "name" => "bar0",
                    "ownerId" => 42,
                )
            ),
            array(
                2,
                array(
                    "id" => 2,
                    "name" => "bar1",
                    "ownerId" => 42,
                )
            ),
            array(
                11,
                array(
                    "id" => 11,
                    "name" => "foo0",
                    "ownerId" => null,
                )
            ),
        );
    }

    /**
     * Test updating content on unexisting ID
     *
     * @covers ezp\Persistence\Storage\InMemory\Backend::update
     * @group inMemoryBackend
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
     * @covers ezp\Persistence\Storage\InMemory\Backend::update
     * @group inMemoryBackend
     */
    public function testUpdateNewAttribute()
    {
        $this->insertCustomContent();
        $this->assertTrue(
            $this->backend->update( "Content", 1, array( "ownerId" => 5 ) )
        );
        $content = $this->backend->load( "Content", 1 );
        $this->assertEquals( 1, $content->id );
        $this->assertEquals( 'bar0', $content->name );
        $this->assertEquals( 5, $content->ownerId );
    }

    /**
     * Test updating content
     *
     * @covers ezp\Persistence\Storage\InMemory\Backend::update
     * @group inMemoryBackend
     */
    public function testUpdate()
    {
        $this->insertCustomContent();
        $this->assertTrue(
            $this->backend->update( "Content", 2, array( "name" => 'Testing' ) )
        );
        $content = $this->backend->load( "Content", 2 );
        $this->assertEquals( 'Testing', $content->name );
    }

    /**
     * Test updating content with a null value
     *
     * @covers ezp\Persistence\Storage\InMemory\Backend::update
     * @group inMemoryBackend
     */
    public function testUpdateWithNullValue()
    {
        $this->insertCustomContent();
        $this->assertTrue(
            $this->backend->update( "Content", 3, array( "name" => null ) )
        );
        $content = $this->backend->load( "Content", 3 );
        $this->assertEquals( null, $content->name );
    }

    /**
     * Test deleting content
     *
     * @covers ezp\Persistence\Storage\InMemory\Backend::delete
     * @group inMemoryBackend
     */
    public function testDelete()
    {
        $this->insertCustomContent();
        $this->backend->delete( "Content", 1 );
        try
        {
            $this->backend->load( "Content", 1 );
            $this->fail( "Content has not been deleted" );
        }
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test deleting content which does not exist
     *
     * @expectedException \ezp\Base\Exception\NotFound
     * @covers ezp\Persistence\Storage\InMemory\Backend::delete
     * @group inMemoryBackend
     */
    public function testDeleteNotFound()
    {
        $this->insertCustomContent();
        $this->backend->delete( "Content", 42 );
    }
}
