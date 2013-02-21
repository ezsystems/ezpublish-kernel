<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\TrashHandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;

use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\CreateStruct as ContentCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct as TypeCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use eZ\Publish\API\Repository\Values\Content\Location;

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
     * ContentTypes which should be removed in tearDown
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type[]
     */
    protected $contentTypeToDelete = array();

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $type = $this->persistenceHandler->contentTypeHandler()->create( $this->getTypeCreateStruct() );
        $this->contentTypeToDelete[] = $type;

        $this->trashHandler = $this->persistenceHandler->trashHandler();
        $this->lastLocationId = 2;
        for ( $i = 0; $i < $this->entriesGenerated; ++$i )
        {
            $this->contents[] = $content = $this->persistenceHandler->contentHandler()->create(
                new ContentCreateStruct(
                    array(
                        "name" => array( "eng-GB" => "test_$i" ),
                        "ownerId" => 14,
                        "sectionId" => 1,
                        "typeId" => $type->id,
                        "initialLanguageId" => 2,
                        "fields" => array(
                            new Field(
                                array(
                                    "fieldDefinitionId" => $type->fieldDefinitions[0]->id,
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
     * @return \eZ\Publish\SPI\Persistence\Content\Type\CreateStruct
     */
    protected function getTypeCreateStruct()
    {
        $struct = new TypeCreateStruct();
        $struct->created = $struct->modified = time();
        $struct->creatorId = $struct->modifierId = 14;
        $struct->name = array( 'eng-GB' => 'Article' );
        $struct->description = array( 'eng-GB' => 'Article content type' );
        $struct->identifier = 'article';
        $struct->isContainer = true;
        $struct->status = Type::STATUS_DEFINED;
        $struct->initialLanguageId = 2;
        $struct->nameSchema = "<short_title|title>";
        $struct->fieldDefinitions = array();
        $struct->groupIds = array( 1 );
        $struct->fieldDefinitions[] = $field = $this->getTypeFieldDefinition();
        return $struct;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    protected function getTypeFieldDefinition()
    {
        $field = new FieldDefinition();
        $field->identifier = 'title';
        $field->fieldType = 'ezstring';
        $field->position = 0;
        $field->isTranslatable = $field->isRequired = true;
        $field->isInfoCollector = false;
        $field->defaultValue = new FieldValue(
            array(
                "data" => "New Article"
            )
        );
        $field->name = array( 'eng-GB' => "Title" );
        $field->description = array( 'eng-GB' => "Title, used for headers, and url if short_title is empty" );
        return $field;
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

        $contentTypeHandler = $this->persistenceHandler->contentTypeHandler();
        foreach ( $this->contentTypeToDelete as $type )
        {
            try
            {
                $contentTypeHandler->delete( $type->id, $type->status );
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
            self::assertEquals( $value, $trashed->$property, "Property {$property} did not match" );
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
