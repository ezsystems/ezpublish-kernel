<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\ContentHandlerRelationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;

use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct as TypeCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * Test case for relations operation in ContentHandler using in memory storage.
 */
class ContentHandlerRelationTest extends HandlerTest
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content
     */
    protected $content;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content
     */
    protected $content2;

    /**
     * @var int
     */
    protected $contentId;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content[]
     */
    protected $contentToDelete = array();

    /**
     * @var int
     */
    protected $lastRelationId;

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

        $struct = $this->createContentStruct( "test", "Welcome", $type );

        $this->content = $this->persistenceHandler->contentHandler()->create( $struct );
        $this->contentToDelete[] = $this->content;
        $this->contentId = $this->content->versionInfo->contentInfo->id;

        $this->lastRelationId = $this->persistenceHandler
            ->contentHandler()
            ->addRelation(
                new RelationCreateStruct(
                    array(
                        'sourceContentId' => 1,
                        'destinationContentId' => $this->contentId,
                        'type' => Relation::COMMON | Relation::EMBED
                    )
                )
            )->id;

        $this->content2 = $this->persistenceHandler->contentHandler()->create(
            $this->createContentStruct( "Second object", "Do you relate to me?", $type )
        );
        $this->contentToDelete[] = $this->content2;
    }

    /**
     * Removes stuff created in setUp().
     */
    protected function tearDown()
    {
        $contentHandler = $this->persistenceHandler->contentHandler();

        try
        {
            // Removing default objects as well as those created by tests
            foreach ( $this->contentToDelete as $content )
            {
                $contentHandler->deleteContent( $content->versionInfo->contentInfo->id );
            }
        }
        catch ( NotFound $e )
        {
        }
        unset( $this->contentId );

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

        parent::tearDown();
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
     *
     *
     * @param $name
     * @param $textValue
     * @param $type \eZ\Publish\SPI\Persistence\Content\Type
     *
     * @return \eZ\Publish\SPI\Persistence\Content\CreateStruct
     */
    protected function createContentStruct( $name, $textValue, Type $type )
    {
        $struct = new CreateStruct();
        $struct->name = $name;
        $struct->ownerId = 14;
        $struct->sectionId = 1;
        $struct->typeId = $type->id;
        $struct->initialLanguageId = 2;
        $struct->fields[] = new Field(
            array(
                'fieldDefinitionId' => $type->fieldDefinitions[0]->id,
                'type' => 'ezstring',
                // FieldValue object compatible with ezstring
                'value' => new FieldValue(
                    array(
                        'data' => $textValue
                    )
                ),
                'languageCode' => 'eng-GB',
            )
        );
        return $struct;
    }

    /**
     * Test addRelation function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::addRelation
     */
    public function testAddRelation1()
    {
        $relation = $this->persistenceHandler->contentHandler()->addRelation(
            new RelationCreateStruct(
                array(
                    'sourceContentId' => 14,
                    'destinationContentId' => 10,
                    'type' => Relation::COMMON
                )
            )
        );
        $this->assertEquals( $this->lastRelationId + 1, $relation->id );
        $this->assertEquals( 14, $relation->sourceContentId );
        $this->assertNull( $relation->sourceContentVersionNo );
        $this->assertEquals( 10, $relation->destinationContentId );
    }

    /**
     * Test addRelation function with a version
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::addRelation
     */
    public function testAddRelation2()
    {
        $relation = $this->persistenceHandler->contentHandler()->addRelation(
            new RelationCreateStruct(
                array(
                    'sourceContentId' => 14,
                    'sourceContentVersionNo' => 1,
                    'destinationContentId' => 10,
                    'type' => Relation::COMMON
                )
            )
        );
        $this->assertEquals( $this->lastRelationId + 1, $relation->id );
        $this->assertEquals( 14, $relation->sourceContentId );
        $this->assertEquals( 1, $relation->sourceContentVersionNo );
        $this->assertEquals( 10, $relation->destinationContentId );
    }

    /**
     * Test addRelation function with unexisting source content ID
     *
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::addRelation
     */
    public function testAddRelationSourceDoesNotExist1()
    {
        $this->persistenceHandler->contentHandler()->addRelation(
            new RelationCreateStruct(
                array(
                    'sourceContentId' => 123456,
                    'sourceContentVersionNo' => null,
                    'destinationContentId' => 10,
                    'type' => Relation::COMMON
                )
            )
        );
    }

    /**
     * Test addRelation function with unexisting source content version
     *
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::addRelation
     */
    public function testAddRelationSourceDoesNotExist2()
    {
        $this->persistenceHandler->contentHandler()->addRelation(
            new RelationCreateStruct(
                array(
                    'sourceContentId' => 14,
                    'sourceContentVersionNo' => 123456,
                    'destinationContentId' => 10,
                    'type' => Relation::COMMON
                )
            )
        );
    }

    /**
     * Test loadRelations function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::loadRelations
     */
    public function testLoadRelations()
    {
        $relations = $this->persistenceHandler->contentHandler()->loadRelations( 1 );
        $this->assertEquals( 1, count( $relations ) );
        $this->assertEquals( 1, $relations[0]->sourceContentId );
        $this->assertNull( $relations[0]->sourceContentVersionNo );
        $this->assertEquals( $this->contentId, $relations[0]->destinationContentId );
        $this->assertEquals( Relation::COMMON | Relation::EMBED, $relations[0]->type );
    }

    /**
     * Test loadRelations function with a type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::loadRelations
     */
    public function testLoadRelationsWithType1()
    {
        $relations = $this->persistenceHandler->contentHandler()->loadRelations( 1, null, Relation::EMBED );
        $this->assertEquals( 1, count( $relations ) );
        $this->assertEquals( 1, $relations[0]->sourceContentId );
        $this->assertNull( $relations[0]->sourceContentVersionNo );
        $this->assertEquals( $this->contentId, $relations[0]->destinationContentId );
        $this->assertEquals( Relation::COMMON | Relation::EMBED, $relations[0]->type );
    }

    /**
     * Test loadRelations function with combined types
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::loadRelations
     */
    public function testLoadRelationsWithType2()
    {
        $relations = $this->persistenceHandler->contentHandler()->loadRelations( 1, null, Relation::COMMON | Relation::EMBED );
        $this->assertEquals( 1, count( $relations ) );
        $this->assertEquals( 1, $relations[0]->sourceContentId );
        $this->assertNull( $relations[0]->sourceContentVersionNo );
        $this->assertEquals( $this->contentId, $relations[0]->destinationContentId );
        $this->assertEquals( Relation::COMMON | Relation::EMBED, $relations[0]->type );
    }

    /**
     * Test loadRelations function with no associated results
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::loadRelations
     */
    public function testLoadRelationsWithTypeNoResult1()
    {
        $this->assertEmpty(
            $this->persistenceHandler->contentHandler()->loadRelations(
                1,
                null,
                Relation::COMMON | Relation::EMBED | Relation::LINK
            )
        );
    }

    /**
     * Test loadRelations function with no associated results
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::loadRelations
     */
    public function testLoadRelationsWithTypeNoResult2()
    {
        $this->assertEmpty(
            $this->persistenceHandler->contentHandler()->loadRelations(
                1,
                null,
                Relation::LINK
            )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::loadReverseRelations
     */
    public function testLoadReverseRelationsOneEntry()
    {
        $newRelation = $this->persistenceHandler->contentHandler()->addRelation(
            new RelationCreateStruct(
                array(
                    'sourceContentId' => $this->contentId,
                    'destinationContentId' => $this->content2->versionInfo->contentInfo->id,
                    'type' => Relation::COMMON
                )
            )
        );

        $reverseRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations( $this->content2->versionInfo->contentInfo->id );
        self::assertEquals( 1, count( $reverseRelations ) );
        self::assertEquals( $this->contentId, $reverseRelations[0]->sourceContentId );
        self::assertNull( $reverseRelations[0]->sourceContentVersionNo );
        self::assertEquals( $this->content2->versionInfo->contentInfo->id, $reverseRelations[0]->destinationContentId );
        self::assertEquals( Relation::COMMON, $reverseRelations[0]->type );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::loadReverseRelations
     */
    public function testLoadReverseRelationsOneEntryMatchingType()
    {
        $newRelation = $this->persistenceHandler->contentHandler()->addRelation(
            new RelationCreateStruct(
                array(
                    'sourceContentId' => $this->contentId,
                    'destinationContentId' => $this->content2->versionInfo->contentInfo->id,
                    'type' => Relation::COMMON
                )
            )
        );

        $reverseRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations( $this->content2->versionInfo->contentInfo->id, Relation::COMMON );
        self::assertEquals( 1, count( $reverseRelations ) );
        self::assertEquals( $this->contentId, $reverseRelations[0]->sourceContentId );
        self::assertNull( $reverseRelations[0]->sourceContentVersionNo );
        self::assertEquals( $this->content2->versionInfo->contentInfo->id, $reverseRelations[0]->destinationContentId );
        self::assertEquals( Relation::COMMON, $reverseRelations[0]->type );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::loadReverseRelations
     */
    public function testLoadReverseRelationsOneEntryNoMatchingType()
    {
        $newRelation = $this->persistenceHandler->contentHandler()->addRelation(
            new RelationCreateStruct(
                array(
                    'sourceContentId' => $this->contentId,
                    'destinationContentId' => $this->content2->versionInfo->contentInfo->id,
                    'type' => Relation::COMMON
                )
            )
        );

        $reverseRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations( $this->content2->versionInfo->contentInfo->id, Relation::EMBED );
        self::assertEmpty( $reverseRelations );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::loadReverseRelations
     */
    public function testLoadReverseRelationsTwoEntries()
    {
        $newRelation = $this->persistenceHandler->contentHandler()->addRelation(
            new RelationCreateStruct(
                array(
                    'sourceContentId' => $this->contentId,
                    'destinationContentId' => $this->content2->versionInfo->contentInfo->id,
                    'type' => Relation::COMMON
                )
            )
        );

        $newRelation2 = $this->persistenceHandler->contentHandler()->addRelation(
            new RelationCreateStruct(
                array(
                    'sourceContentId' => 1,
                    'destinationContentId' => $this->content2->versionInfo->contentInfo->id,
                    'type' => Relation::COMMON
                )
            )
        );

        $reverseRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations( $this->content2->versionInfo->contentInfo->id );
        self::assertEquals( 2, count( $reverseRelations ) );

        $approvedRelatedObjectIds = array( $this->contentId, 1 );

        foreach ( $reverseRelations as $revRel )
        {
            self::assertContains( $revRel->sourceContentId, $approvedRelatedObjectIds );
        }
    }

    /**
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::loadReverseRelations
     */
    public function testLoadReverseRelationsTwoEntriesDifferentTypes()
    {
        $newRelation = $this->persistenceHandler->contentHandler()->addRelation(
            new RelationCreateStruct(
                array(
                    'sourceContentId' => $this->contentId,
                    'destinationContentId' => $this->content2->versionInfo->contentInfo->id,
                    'type' => Relation::COMMON
                )
            )
        );

        $newRelation2 = $this->persistenceHandler->contentHandler()->addRelation(
            new RelationCreateStruct(
                array(
                    'sourceContentId' => 1,
                    'destinationContentId' => $this->content2->versionInfo->contentInfo->id,
                    'type' => Relation::FIELD
                )
            )
        );

        $reverseRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations( $this->content2->versionInfo->contentInfo->id );
        self::assertEquals( 2, count( $reverseRelations ) );

        $reverseRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations( $this->content2->versionInfo->contentInfo->id, Relation::FIELD );
        self::assertEquals( 1, count( $reverseRelations ) );
        self::assertEquals( Relation::FIELD, current( $reverseRelations )->type );

        $reverseRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations( $this->content2->versionInfo->contentInfo->id, Relation::COMMON );
        self::assertEquals( 1, count( $reverseRelations ) );
        self::assertEquals( Relation::COMMON, current( $reverseRelations )->type );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::removeRelation
     */
    public function testRemoveRelation()
    {
        $newRelation = $this->persistenceHandler->contentHandler()->addRelation(
            new RelationCreateStruct(
                array(
                    'sourceContentId' => $this->contentId,
                    'destinationContentId' => $this->content2->versionInfo->contentInfo->id,
                    'type' => Relation::COMMON
                )
            )
        );

        $relations = $this->persistenceHandler->contentHandler()->loadRelations( $this->contentId );
        self::assertEquals( 1, count( $relations ) );
        self::assertEquals( $newRelation->id, $relations[0]->id );

        $this->persistenceHandler->contentHandler()->removeRelation( $newRelation->id, Relation::COMMON );
        $relations = $this->persistenceHandler->contentHandler()->loadRelations( $this->contentId );
        self::assertEmpty( $relations );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::removeRelation
     */
    public function testRemoveRelationDoesNotExist()
    {
        $newRelation = $this->persistenceHandler->contentHandler()->addRelation(
            new RelationCreateStruct(
                array(
                    'sourceContentId' => $this->contentId,
                    'destinationContentId' => $this->content2->versionInfo->contentInfo->id,
                    'type' => Relation::COMMON
                )
            )
        );

        $this->persistenceHandler->contentHandler()->removeRelation( 42, Relation::COMMON );
    }
}
