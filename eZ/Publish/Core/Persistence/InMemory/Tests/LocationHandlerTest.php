<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\LocationHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;
use eZ\Publish\SPI\Persistence\Content\Location as LocationValue,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\CreateStruct as ContentCreateStruct,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound,
    eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Test case for Location Handler using in memory storage.
 */
class LocationHandlerTest extends HandlerTest
{
    /**
     * Number of Content and Location generated for the tests.
     *
     * @var int
     */
    protected $entriesGenerated = 5;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location[]
     */
    protected $locations;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content[]
     */
    protected $contents;

    /**
     * Last inserted location id in setUp
     *
     * @var int
     */
    protected $lastLocationId;

    /**
     * Last inserted content id in setUp
     *
     * @var int
     */
    protected $lastContentId;

    /**
     * Locations which should be removed in tearDown
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Location[]
     */
    protected $locationToDelete = array();

    /**
     * Contents which should be removed in tearDown
     *
     * @var \eZ\Publish\SPI\Persistence\Content[]
     */
    protected $contentToDelete = array();

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->lastLocationId = 2;
        for ( $i = 0 ; $i < $this->entriesGenerated; ++$i )
        {
            $this->contents[] = $content = $this->persistenceHandler->contentHandler()->create(
                new ContentCreateStruct(
                    array(
                        "name" => array( "eng-GB" => "test_$i" ),
                        "ownerId" => 14,
                        "sectionId" => 1,
                        "typeId" => 2,
                        "initialLanguageId" => 2,
                        "fields" => array(
                            new Field(
                                array(
                                    "type" => "ezstring",
                                    // FieldValue object compatible with ezstring
                                    "value" => new FieldValue(
                                        array(
                                            'data' => "Welcome $i"
                                        )
                                    ),
                                    "languageCode" => "eng-GB",
                                )
                            )
                        )
                    )
                )
            );

            $this->lastContentId = $content->versionInfo->contentInfo->id;

            $this->locations[] = $location = $this->persistenceHandler->locationHandler()->create(
                new CreateStruct(
                    array(
                        "contentId" => $this->lastContentId,
                        "contentVersion" => 1,
                        "mainLocationId" => $this->lastLocationId,
                        "sortField" => Location::SORT_FIELD_NAME,
                        "sortOrder" => Location::SORT_ORDER_ASC,
                        "parentId" => $this->lastLocationId,
                    )
                )
            );

            $this->lastLocationId = $location->id;
        }

        $this->locationToDelete = $this->locations;
        $this->contentToDelete = $this->contents;
    }

    /**
     * Removes stuff created in setUp().
     */
    protected function tearDown()
    {
        $locationHandler = $this->persistenceHandler->locationHandler();

        // Removing default objects as well as those created by tests
        foreach ( $this->locationToDelete as $location )
        {
            try
            {
                $locationHandler->removeSubtree( $location->id );
            }
            catch ( NotFound $e )
            {
            }
        }

        $contentHandler = $this->persistenceHandler->contentHandler();
        foreach ( $this->contentToDelete as $content )
        {
            try
            {
                $contentHandler->deleteContent( $content->versionInfo->contentInfo->id );
            }
            catch ( NotFound $e )
            {
            }
        }

        unset( $this->lastLocationId, $this->lastContentId );
        parent::tearDown();
    }

    /**
     * Test load function
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\LocationHandler::load
     * @group locationHandler
     */
    public function testLoad()
    {
        $location = $this->persistenceHandler->locationHandler()->load( $this->lastLocationId );
        $this->assertTrue( $location instanceof LocationValue );
        $this->assertEquals( $this->lastLocationId, $location->id );
        $this->assertEquals( $this->lastContentId, $location->contentId );
        // @todo contentVersion not yet implemented
        //$this->assertEquals( 1, $location->contentVersion );
        $this->assertEmpty( $location->pathIdentificationString );
        $this->assertEquals( $this->lastLocationId, $location->mainLocationId );
        $this->assertEquals( Location::SORT_FIELD_NAME, $location->sortField );
        $this->assertEquals( Location::SORT_ORDER_ASC, $location->sortOrder );
        $this->assertEquals( $this->lastLocationId - 1, $location->parentId );
    }

    /**
     * Test create function
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\LocationHandler::create
     * @group locationHandler
     */
    public function testCreate()
    {
        $location = $this->persistenceHandler->locationHandler()->create(
            new CreateStruct(
                array(
                    "contentId" => 1,
                    "contentVersion" => 1,
                    "pathIdentificationString" => "",
                    "mainLocationId" => 2,
                    "sortField" => Location::SORT_FIELD_NAME,
                    "sortOrder" => Location::SORT_ORDER_ASC,
                    "parentId" => 1,
                )
            )
        );
        $this->locationToDelete[] = $location;
        $this->assertTrue( $location instanceof LocationValue );
        $this->assertEquals( $this->lastLocationId + 1, $location->id );
        $this->assertEquals( 1, $location->contentId );
        // @todo contentVersion not yet implemented
        //$this->assertEquals( 1, $location->contentVersion );
        $this->assertEmpty( $location->pathIdentificationString );
        $this->assertEquals( 2, $location->mainLocationId );
        $this->assertEquals( Location::SORT_FIELD_NAME, $location->sortField );
        $this->assertEquals( Location::SORT_ORDER_ASC, $location->sortOrder );
        $this->assertEquals( 1, $location->parentId );
    }

    /**
     * Test removeSubtree function with no children
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\LocationHandler::removeSubtree
     * @group locationHandler
     */
    public function testRemoveSubtreeNoChildren()
    {
        $locationHandler = $this->persistenceHandler->locationHandler();
        $locationHandler->removeSubtree( $this->lastLocationId );

        try
        {
            $locationHandler->load( $this->lastLocationId );
            $this->fail( "Content not removed correctly" );
        }
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test removeSubtree function with children
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\LocationHandler::removeSubtree
     * @group locationHandler
     */
    public function testRemoveSubtreeChildren()
    {
        $locationHandler = $this->persistenceHandler->locationHandler();
        $locationHandler->removeSubtree( $this->lastLocationId - 2 );

        try
        {
            $locationHandler->load( $this->lastLocationId );
            $this->fail( "Content not removed correctly" );
        }
        catch ( NotFound $e )
        {
        }

        try
        {
            $locationHandler->load( $this->lastLocationId - 1 );
            $this->fail( "Content not removed correctly" );
        }
        catch ( NotFound $e )
        {
        }

        try
        {
            $locationHandler->load( $this->lastLocationId - 2 );
            $this->fail( "Content not removed correctly" );
        }
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test copySubtree function with no children
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\LocationHandler::copySubtree
     * @group locationHandler
     */
    public function testCopySubtreeNoChildren()
    {
        // Copy the last location created in setUp
        $newLocation = $this->persistenceHandler->locationHandler()->copySubtree( $this->lastLocationId, 2 );
        $this->assertTrue( $newLocation instanceof LocationValue );
        $this->assertEquals( $this->lastLocationId + 1 , $newLocation->id );
        $this->assertEquals( $this->lastContentId + 1, $newLocation->contentId );
        $this->assertEquals( 2, $newLocation->depth );
        $this->assertEquals( Location::SORT_FIELD_NAME, $newLocation->sortField );
        $this->assertEquals( Location::SORT_ORDER_ASC, $newLocation->sortOrder );

        // SearchHandler::findSingle() needs reimplementation
        /*$this->assertEquals(
            $this->persistenceHandler->searchHandler()->findSingle(
                new ContentId( $newLocation->contentId )
            )->locations[0],
            $newLocation,
            "Location does not match"
        );*/
    }

    /**
     * Test copySubtree function with children
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\LocationHandler::copySubtree
     * @group locationHandler
     */
    public function testCopySubtreeChildren()
    {
        // Copy the grand parent of the last location created in setUp
        $newLocation = $this->persistenceHandler->locationHandler()->copySubtree( $this->lastLocationId - 2, 2 );
        $this->assertTrue( $newLocation instanceof LocationValue );
        $this->assertEquals( $this->lastLocationId + 1 , $newLocation->id );
        $this->assertEquals( $this->lastContentId + 1, $newLocation->contentId );
        $this->assertEquals( 2, $newLocation->depth );
        $this->assertEquals( Location::SORT_FIELD_NAME, $newLocation->sortField );
        $this->assertEquals( Location::SORT_ORDER_ASC, $newLocation->sortOrder );

        // Verifying the deepest child is present
        // SearchHandler::findSingle() needs reimplementation
        /*foreach (
            $this->persistenceHandler->searchHandler()->findSingle(
                new ContentId( $newLocation->contentId )
            )->locations[0] as $property => $value
        )
        {
            self::assertEquals( $newLocation->$property, $value, "Location does not match" );
        }

        // Verifying the direct child is present
        $loc = $this->persistenceHandler->locationHandler()->load( $newLocation->id - 1 );
        foreach (
            $this->persistenceHandler->searchHandler()->findSingle(
                new ContentId( $newLocation->contentId - 1 )
            )->locations[0] as $property => $value
        )
        {
            self::assertEquals( $loc->$property, $value, "Location does not match" );
        }
        unset( $loc );

        // Verifying the top most copied location (the grand parent) is present
        $loc = $this->persistenceHandler->locationHandler()->load( $newLocation->id - 2 );
        foreach (
            $this->persistenceHandler->searchHandler()->findSingle(
                new ContentId( $newLocation->contentId - 2 )
            )->locations[0] as $property => $value
        )
        {
            self::assertEquals( $loc->$property, $value, "Location does not match" );
        }*/
    }

    /**
     * Tests loadByParentId function with no children
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\LocationHandler::loadByParentId
     * @group locationHandler
     */
    public function testLoadByParentIdNoChildren()
    {
        $this->assertEmpty( $this->persistenceHandler->locationHandler()->loadByParentId( $this->lastLocationId ) );
    }

    /**
     * Tests loadByParentId function with children
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\LocationHandler::loadByParentId
     * @group locationHandler
     */
    public function testLoadByParentIdChildren()
    {
        $this->assertEquals(
            array( end( $this->locations ) ),
            $this->persistenceHandler->locationHandler()->loadByParentId( $this->lastLocationId - 1 )
        );
    }

    /**
     * Tests loadByParentId function on unexisting id
     *
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     * @covers \eZ\Publish\Core\Persistence\InMemory\LocationHandler::loadByParentId
     * @group locationHandler
     */
    public function testLoadByParentIdNotExisting()
    {
        $this->persistenceHandler->locationHandler()->loadByParentId( 123456 );
    }

    /**
     * Test for the changeMainLocation() method.
     *
     * @covers \eZ\Publish\Core\Persistence\InMemory\LocationHandler::changeMainLocation
     * @group locationHandler
     */
    public function testChangeMainLocation()
    {
        // Create additional location to perform this test
        $location = $this->persistenceHandler->locationHandler()->create(
            new CreateStruct(
                array(
                    "contentId" => 1,
                    "contentVersion" => 1,
                    "pathIdentificationString" => "",
                    "mainLocationId" => 2,
                    "sortField" => Location::SORT_FIELD_NAME,
                    "sortOrder" => Location::SORT_ORDER_ASC,
                    "parentId" => 44,
                )
            )
        );
        $this->persistenceHandler->locationHandler()->changeMainLocation(
            1,
            $location->id
        );

        $content = $this->persistenceHandler->contentHandler()->load( 1, 1 );

        $this->assertEquals(
            $location->id,
            $content->versionInfo->contentInfo->mainLocationId,
            "Main location has not been changed"
        );

        $this->assertEquals(
            2,
            $content->versionInfo->contentInfo->sectionId,
            "Subtree section has not been changed"
        );
    }
}
