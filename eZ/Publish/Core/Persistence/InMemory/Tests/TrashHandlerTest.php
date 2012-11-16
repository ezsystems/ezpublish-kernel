<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\TrashHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\CreateStruct as ContentCreateStruct,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound,
    eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Test case for Location Handler using in memory storage.
 */
class TrashHandlerTest extends HandlerTest
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
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    protected $trashHandler;

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->trashHandler = $this->persistenceHandler->trashHandler();
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
                                            "data" => "Welcome $i"
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
        $this->trashHandler->emptyTrash();
        $this->trashHandler = null;
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
     * @covers \eZ\Publish\Core\Persistence\InMemory\TrashHandler::loadTrashItem
     * @group trashHandler
     */
    public function testLoad()
    {
        $trashed = $this->trashHandler->trashSubtree( $this->locations[0]->id );
        $trashedId = $trashed->id;
        unset( $trashed );

        $trashed = $this->trashHandler->loadTrashItem( $trashedId );
        self::assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Trashed', $trashed );
        foreach ( $this->locations[0] as $property => $value )
        {
            self::assertEquals( $value, $trashed->$property, "Property {$property} did not match");
        }
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     * @covers \eZ\Publish\Core\Persistence\InMemory\TrashHandler::loadTrashItem
     * @group trashHandler
     */
    public function testLoadNonExistent()
    {
        $this->trashHandler->loadTrashItem( 0 );
    }

    /**
     * @group trashHandler
     * @covers \eZ\Publish\Core\Persistence\InMemory\TrashHandler::trashSubtree
     */
    public function testTrashSubtree()
    {
        $this->markTestIncomplete();
    }

    /**
     * @group trashHandler
     * @covers \eZ\Publish\Core\Persistence\InMemory\TrashHandler::untrashLocation
     */
    public function testUntrashLocation()
    {
        $this->markTestIncomplete();
    }

    /**
     * @group trashHandler
     * @covers \eZ\Publish\Core\Persistence\InMemory\TrashHandler::listTrashed
     */
    public function testListTrashed()
    {
        $this->markTestIncomplete();
    }

    /**
     * @group trashHandler
     * @covers \eZ\Publish\Core\Persistence\InMemory\TrashHandler::emptyTrash
     */
    public function testEmptyTrash()
    {
        $this->markTestIncomplete();
    }

    /**
     * @group trashHandler
     * @covers \eZ\Publish\Core\Persistence\InMemory\TrashHandler::emptyOne
     */
    public function testEmptyOne()
    {
        $this->markTestIncomplete();
    }
}
