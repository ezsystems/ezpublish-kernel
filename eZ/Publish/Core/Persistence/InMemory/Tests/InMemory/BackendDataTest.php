<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\BackendDataTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests\InMemory;
use PHPUnit_Framework_TestCase,
    ReflectionObject,
    eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\Location,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct as LocationCreateStruct,
    eZ\Publish\Core\Persistence\InMemory\Backend;

/**
 * Test case for Handler using in memory storage.
 */
class BackendDataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\InMemory\Backend
     */
    protected $backend;

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        // Create a new backend from JSON data and empty Content data to make it clean
        $this->backend = new Backend(
            json_decode( file_get_contents( str_replace( '/Tests/InMemory', '', __DIR__ ) . '/data.json' ), true )
        );
        $this->insertCustomContent();
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
        $data['Content\\VersionInfo'] = array();
        $refData->setValue( $this->backend, $data );

        for ( $i = 0; $i < 10; ++$i)
            $this->backend->create(
                "Content\\VersionInfo",
                array(
                    "_contentId" => 1,
                    "versionNo" => 1,
                    "names" => array( "eng-GB" => "bar{$i}" ),
                    "creatorId" => 42,
                )
            );

        for ( $i = 0; $i < 10; ++$i)
            $this->backend->create(
                "Content\\VersionInfo",
                array(
                    "_contentId" => 1,
                    "versionNo" => 1,
                    "names" => array( "eng-GB" => "foo{$i}" ),
                )
            );
        for ( $i = 0; $i < 10; ++$i)
            $this->backend->create(
                "Content\\Language",
                array(
                    "languageCode" => "lan-{$i}",
                    "isEnabled" => true,
                    "name" => "lang{$i}"
                )
            );
    }

    /**
     * Test finding content without results
     *
     * @dataProvider providerForFindEmpty
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindEmpty( $searchData )
    {
        $this->assertEquals(
            array(),
            $this->backend->find( "Content\\VersionInfo", $searchData )
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
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFind( $searchData, $result )
    {
        foreach ( $this->backend->find( "Content\\VersionInfo", $searchData ) as $key => $version )
        {
            $this->assertEquals( $result[$key]['id'], $version->id );
            $this->assertEquals( array( "eng-GB" => $result[$key]['names'] ), $version->names );
        }
    }

    public function providerForFind()
    {
        return array(
            array(
                array( "names" => "bar0" ),
                array(
                    array(
                        "id" => 1,
                        "names" => "bar0",
                    )
                )
            ),
            array(
                array( "names" => "foo5" ),
                array(
                    array(
                        "id" => 16,
                        "names" => "foo5",
                    )
                )
            ),
            array(
                array( "creatorId" => 42 ),
                array(
                    array(
                        "id" => 1,
                        "names" => "bar0",
                    ),
                    array(
                        "id" => 2,
                        "names" => "bar1",
                    ),
                    array(
                        "id" => 3,
                        "names" => "bar2",
                    ),
                    array(
                        "id" => 4,
                        "names" => "bar3",
                    ),
                    array(
                        "id" => 5,
                        "names" => "bar4",
                    ),
                    array(
                        "id" => 6,
                        "names" => "bar5",
                    ),
                    array(
                        "id" => 7,
                        "names" => "bar6",
                    ),
                    array(
                        "id" => 8,
                        "names" => "bar7",
                    ),
                    array(
                        "id" => 9,
                        "names" => "bar8",
                    ),
                    array(
                        "id" => 10,
                        "names" => "bar9",
                    ),
                ),
            ),
        );
    }

    /**
     * Test finding content with multiple ids
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindMultipleIds()
    {
        $searchIds = array( 3, 5, 7 );
        $list = $this->backend->find( 'Content\\VersionInfo', array( 'id' => $searchIds ) );
        self::assertEquals( count( $searchIds ), count( $list ) );

        foreach ( $list as $vo )
        {
            self::assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo', $vo );
            self::assertContains( $vo->id, $searchIds );
        }
    }

    /**
     * Test finding content with results
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindMatchOnArray()
    {
        $types = $this->backend->find( "Content\\Type", array( "groupIds" => 1 ) );
        $this->assertEquals( 2, count( $types ) );

        $this->assertEquals( 1, $types[0]->id );
        $this->assertEquals( 'folder', $types[0]->identifier );
        $this->assertEquals( 13, $types[1]->id );
        $this->assertEquals( 'comment', $types[1]->identifier );
        }

    /**
     * Test finding content with results using join
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindJoin()
    {
        /**
         * @var \eZ\Publish\SPI\Persistence\Content[] $list
         */
        $list = $this->backend->find(
            'Content',
            array( 'id' => 1 ),
            array(
                'versionInfo' => array(
                    'type' => 'Content\\VersionInfo',
                    'match' => array( '_contentId' => 'id', 'versionNo' => '_currentVersionNo' ),
                    'single' => true,
                    'sub' => array(
                        'contentInfo' => array(
                            'type' => 'Content\\ContentInfo',
                            'match' => array( 'id' => '_contentId' ),
                            'single' => true
                        ),
                    )
                )
            )
        );

        $this->assertEquals( 1, count( $list ) );
        foreach ( $list as $key => $content )
        {
            $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content', $content );
            $this->assertEquals( 1, $content->versionInfo->contentInfo->id );
            $this->assertEquals( 1, $content->versionInfo->contentInfo->sectionId );
            $locations = $this->backend->find(
                'Content\\Location',
                array( 'contentId' => $content->versionInfo->contentInfo->id )
            );
            $this->assertEquals( 1, count( $locations ) );
            foreach ( $locations as $location )
            {
                $this->assertInstanceof( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location', $location );
                $this->assertEquals( 2, $location->id );
                $this->assertEquals( 1, $location->contentId );
            }
        }
    }

    /**
     * Test finding content with results using join and deep matching
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindJoinDeepMatch()
    {
        /**
         * @var \eZ\Publish\SPI\Persistence\Content[] $list
         */
        $list = $this->backend->find(
            'Content',
            array( "versionInfo" => array( 'id' => 2 ) ),
            array(
                'versionInfo' => array(
                    'type' => 'Content\\VersionInfo',
                    'match' => array( '_contentId' => 'id', 'versionNo' => '_currentVersionNo' ),
                    'single' => true,
                    'sub' => array(
                        'contentInfo' => array(
                            'type' => 'Content\\ContentInfo',
                            'match' => array( 'id' => '_contentId' ),
                            'single' => true
                        ),
                    )
                )
            )
        );

        $this->assertEquals( 1, count( $list ) );
        foreach ( $list as $content )
        {
            $this->assertTrue( $content instanceof Content );
            $this->assertEquals( 1, $content->versionInfo->contentInfo->id );
            $this->assertEquals( 1, $content->versionInfo->contentInfo->sectionId );
            $locations = $this->backend->find(
                'Content\\Location',
                array( 'contentId' => $content->versionInfo->contentInfo->id )
            );
            $this->assertEquals( 1, count( $locations ) );
            foreach ( $locations as $location )
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
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::find
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
         * @var \eZ\Publish\SPI\Persistence\Content[] $list
         */
        $list = $this->backend->find(
            'Content',
            array( "versionInfo" => array( 'id' => 2 ) ),
            array(
                'versionInfo' => array(
                    'type' => 'Content\\VersionInfo',
                    'match' => array( '_contentId' => 'id', 'versionNo' => '_currentVersionNo' ),
                    'single' => true,
                    'sub' => array(
                        'contentInfo' => array(
                            'type' => 'Content\\ContentInfo',
                            'match' => array( 'id' => '_contentId' ),
                            'single' => true
                        ),
                    )
                )
            )
        );
        $this->assertEquals( 1, count( $list ) );
        foreach ( $list as $content )
        {
            $this->assertTrue( $content instanceof Content );
            $this->assertEquals( 1, $content->versionInfo->contentInfo->id );
            $this->assertEquals( 1, $content->versionInfo->contentInfo->sectionId );
            $locations = $this->backend->find(
                'Content\\Location',
                array( 'contentId' => $content->versionInfo->contentInfo->id )
            );
            $this->assertEquals( 2, count( $locations ) );
            foreach ( $locations as $location )
            {
                $this->assertTrue( $location instanceof Location );
                $this->assertEquals( 1, $location->contentId );
            }
        }
    }

    /**
     * Test finding content with results using join and deep matching where match collides with join
     *
     * @expectedException LogicException
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindJoinDeepMatchCollision()
    {
        $this->backend->find(
            'Content',
            array( "locations" => array( 'contentId' => 2 ) ),
            array( 'locations' =>
                array(
                    'type' => 'Content\\Location',
                    'match' => array( 'contentId' => 'id' )
                )
            )
        );
    }

    /**
     * Test finding content with results using several levels of join
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindSubJoin()
    {
        self::markTestIncomplete( "Reimplement this test as Fields are not version sub joins any more" );
        /**
         * @var \eZ\Publish\SPI\Persistence\Content[] $list
         */
        $list = $this->backend->find(
            'Content\\ContentInfo',
            array( "locations" => array( 'id' => 2 ) ),
            array(
                'version' => array(
                    'type' => 'Content\\VersionInfo',
                    'single' => true,
                    'match' => array( '_contentId' => 'id', 'versionNo' => 'currentVersionNo' ),
                    'sub' => array(
                        'fields' => array(
                            'type' => 'Content\\Field',
                            'match' => array( '_contentId' => '_contentId', 'versionNo' => 'versionNo' ),
                        )
                    )
                ),
            )
        );
        $this->assertEquals( 1, count( $list ) );
        foreach ( $list as $content )
        {
            $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content', $content );
            $this->assertEquals( 1, $content->id );
            $locations = $this->backend->find(
                'Content\\Location',
                array( 'contentId' => $content->versionInfo->contentInfo->id )
            );
            $this->assertEquals( 1, count( $locations ) );
            foreach ( $locations as $location )
            {
                $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location', $location );
                $this->assertEquals( 2, $location->id );
                $this->assertEquals( 1, $location->contentId );
            }
            $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo', $content->version );
            $this->assertEquals( 3, count( $content->version->fields ) );
            $this->assertEquals( array( "eng-GB" => "bar0" ), $content->version->name );
            foreach ( $content->version->fields as $field )
            {
                $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field', $field );
                $this->assertEquals( $content->currentVersionNo, $field->versionNo );
            }
        }
    }

    /**
     * Test finding content with wildcard
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testFindWildcard()
    {
        $list = $this->backend->find( 'Content\\Language', array( 'name' => "lang%" ) );
        $this->assertEquals( 10, count( $list ) );
        foreach ( $list as $vo )
        {
            self::assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language', $vo );
            self::assertTrue( strpos( $vo->name, 'lang' ) === 0 );
        }
    }

    /**
     * Test counting content without results
     *
     * @dataProvider providerForFindEmpty
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::count
     * @group inMemoryBackend
     */
    public function testCountEmpty( $searchData )
    {
        $this->assertEquals(
            0,
            $this->backend->count( 'Content\\ContentInfo', $searchData )
        );
    }

    /**
     * Test counting content with results
     *
     * @dataProvider providerForFind
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::count
     * @group inMemoryBackend
     */
    public function testCount( $searchData, $result )
    {
        $this->assertEquals(
            count( $result ),
            $this->backend->count( "Content\\VersionInfo", $searchData )
        );
    }

    /**
     * Test counting content with results using join and deep matching
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::count
     * @group inMemoryBackend
     */
    public function testCountJoinDeepMatch()
    {
        $this->assertEquals(
            1,
            $this->backend->count(
                'Content',
                array( "versionInfo" => array( 'id' => 2 ) ),
                array(
                    'versionInfo' => array(
                        'type' => 'Content\\VersionInfo',
                        'match' => array( '_contentId' => 'id', 'versionNo' => '_currentVersionNo' ),
                        'single' => true,
                        'sub' => array(
                            'contentInfo' => array(
                                'type' => 'Content\\ContentInfo',
                                'match' => array( 'id' => '_contentId' ),
                                'single' => true
                            ),
                        )
                    )
                )
            )
        );
    }

    /**
     * Test count content with results using join and deep matching where match collides with join
     *
     * @expectedException LogicException
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::find
     * @group inMemoryBackend
     */
    public function testCountJoinDeepMatchCollision()
    {
        $this->backend->count(
            'Content',
            array( "locations" => array( 'contentId' => 2 ) ),
            array( 'locations' =>
                array(
                    'type' => 'Content\\Location',
                    'match' => array( 'contentId' => 'id' )
                )
            )
        );
    }

    /**
     * Test loading content without results
     *
     * @dataProvider providerForLoadEmpty
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::load
     * @group inMemoryBackend
     */
    public function testLoadEmpty( $searchData )
    {
        $this->backend->load( 'Content', $searchData );
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
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::load
     * @group inMemoryBackend
     */
    public function testLoad( $searchData, $result )
    {
        $version = $this->backend->load( "Content\\VersionInfo", $searchData );
        foreach ( $result as $name => $value )
            $this->assertEquals( $value, $version->$name );
    }

    public function providerForLoad()
    {
        return array(
            array(
                1,
                array(
                    "id" => 1,
                    "names" => array( "eng-GB" => "bar0" ),
                    "creatorId" => 42,
                )
            ),
            array(
                2,
                array(
                    "id" => 2,
                    "names" => array( "eng-GB" => "bar1" ),
                    "creatorId" => 42,
                )
            ),
            array(
                11,
                array(
                    "id" => 11,
                    "names" => array( "eng-GB" => "foo0" ),
                    "creatorId" => null,
                )
            ),
        );
    }

    /**
     * Test updating content on unexisting ID
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::update
     * @group inMemoryBackend
     */
    public function testUpdateUnexistingId()
    {
        $this->assertFalse(
            $this->backend->update( 'Content\\ContentInfo', 0, array() )
        );
    }

    /**
     * Test updating content with an extra attribute
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::update
     * @group inMemoryBackend
     */
    public function testUpdateNewAttribute()
    {
        $this->assertTrue(
            $this->backend->update( 'Content\\ContentInfo', 1, array( "ownerId" => 5 ), true )
        );
        $content = $this->backend->load( 'Content\\ContentInfo', 1 );
        $this->assertEquals( 1, $content->id );
        $this->assertEquals( 1, $content->sectionId );
        $this->assertEquals( 5, $content->ownerId );
    }

    /**
     * Test updating content
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::update
     * @group inMemoryBackend
     */
    public function testUpdate()
    {
        $this->assertTrue(
            $this->backend->update( "Content\\VersionInfo", 2, array( "names" => array( "eng-GB" => "Testing" ) ) )
        );
        $versionInfo = $this->backend->load( "Content\\VersionInfo", 2 );
        $this->assertEquals( array( "eng-GB" => "Testing" ), $versionInfo->names );
    }

    /**
     * Test updating content with a null value
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::update
     * @group inMemoryBackend
     */
    public function testUpdateWithNullValue()
    {
        $this->assertTrue(
            $this->backend->update( "Content\\VersionInfo", 3, array( "names" => null ) )
        );
        $versionInfo = $this->backend->load( "Content\\VersionInfo", 3 );
        $this->assertEquals( null, $versionInfo->names );
    }

    /**
     * Test deleting content
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::delete
     * @group inMemoryBackend
     */
    public function testDelete()
    {
        $this->backend->delete( "Content\\VersionInfo", 1 );
        try
        {
            $this->backend->load( "Content\\VersionInfo", 1 );
            $this->fail( "Content has not been deleted" );
        }
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test deleting content which does not exist
     *
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     * @covers eZ\Publish\Core\Persistence\InMemory\Backend::delete
     * @group inMemoryBackend
     */
    public function testDeleteNotFound()
    {
        $this->backend->delete( "Content\\VersionInfo", 999 );
    }
}
