<?php
/**
 * File contains: ezp\Persistence\Tests\ContentHandlerRelationTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests;
use ezp\Persistence\Content,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\Relation as RelationValue,
    ezp\Persistence\Content\Criterion\ContentId,
    ezp\Base\Exception\NotFound,
    ezp\Content\Relation,
    ezp\Content\FieldType\TextLine\Value as TextLineValue,
    ezp\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * Test case for relations operation in ContentHandler using in memory storage.
 *
 */
class ContentHandlerRelationTest extends HandlerTest
{
    /**
     * @var \ezp\Content
     */
    protected $content;

    /**
     * @var int
     */
    protected $contentId;

    /**
     * @var \ezp\Content[]
     */
    protected $contentToDelete = array();

    /**
     * @var int
     */
    protected $lastRelationId;

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $struct = new CreateStruct();
        $struct->name = "test";
        $struct->ownerId = 14;
        $struct->sectionId = 1;
        $struct->typeId = 2;
        $struct->fields[] = new Field(
            array(
                'type' => 'ezstring',
                // FieldValue object compatible with ezstring
                'value' => new FieldValue(
                    array(
                        'data' => new TextLineValue( 'Welcome' )
                    )
                ),
                'language' => 'eng-GB',
            )
        );

        $this->content = $this->repositoryHandler->contentHandler()->create( $struct );
        $this->contentToDelete[] = $this->content;
        $this->contentId = $this->content->id;

        $this->lastRelationId = $this->repositoryHandler
            ->contentHandler()
            ->addRelation( new RelationCreateStruct( array(
                                   'sourceContentId' => 1,
                                   'destinationContentId' => $this->contentId,
                                   'type' => Relation::COMMON | Relation::EMBED
                                   ) ) )->id;
    }

    /**
     * Removes stuff created in setUp().
     */
    protected function tearDown()
    {
        $contentHandler = $this->repositoryHandler->contentHandler();

        try
        {
            // Removing default objects as well as those created by tests
            foreach ( $this->contentToDelete as $content )
            {
                $contentHandler->delete( $content->id );
            }
        }
        catch ( NotFound $e )
        {
        }
        unset( $this->contentId );
        parent::tearDown();
    }

    /**
     * Test addRelation function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::addRelation
     */
    public function testAddRelation1()
    {
        $relation = $this->repositoryHandler->contentHandler()->addRelation( new RelationCreateStruct( array(
                                   'sourceContentId' => 14,
                                   'destinationContentId' => 10,
                                   'type' => Relation::COMMON
                                   ) ) );
        $this->assertEquals( $this->lastRelationId + 1, $relation->id );
        $this->assertEquals( 14, $relation->sourceContentId );
        $this->assertNull( $relation->sourceContentVersion );
        $this->assertEquals( 10, $relation->destinationContentId );
    }

    /**
     * Test addRelation function with a version
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::addRelation
     */
    public function testAddRelation2()
    {
        $relation = $this->repositoryHandler->contentHandler()->addRelation( new RelationCreateStruct( array(
                                   'sourceContentId' => 14,
                                   'sourceContentVersion' => 1,
                                   'destinationContentId' => 10,
                                   'type' => Relation::COMMON
                                   ) ) );
        $this->assertEquals( $this->lastRelationId + 1, $relation->id );
        $this->assertEquals( 14, $relation->sourceContentId );
        $this->assertEquals( 1, $relation->sourceContentVersion );
        $this->assertEquals( 10, $relation->destinationContentId );
    }

    /**
     * Test addRelation function with unexisting source content ID
     *
     * @expectedException ezp\Base\Exception\NotFound
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::addRelation
     */
    public function testAddRelationSourceDoesNotExist1()
    {
        $this->repositoryHandler->contentHandler()->addRelation( new RelationCreateStruct( array(
                                   'sourceContentId' => 123456,
                                   'sourceContentVersion' => null,
                                   'destinationContentId' => 10,
                                   'type' => Relation::COMMON
                                   ) ) );
    }

    /**
     * Test addRelation function with unexisting source content version
     *
     * @expectedException ezp\Base\Exception\NotFound
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::addRelation
     */
    public function testAddRelationSourceDoesNotExist2()
    {
        $this->repositoryHandler->contentHandler()->addRelation( new RelationCreateStruct( array(
                                   'sourceContentId' => 14,
                                   'sourceContentVersion' => 123456,
                                   'destinationContentId' => 10,
                                   'type' => Relation::COMMON
                                   ) ) );
    }

    /**
     * Test loadRelations function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::loadRelations
     */
    public function testLoadRelations()
    {
        $relations = $this->repositoryHandler->contentHandler()->loadRelations( 1 );
        $this->assertEquals( 1, count( $relations ) );
        $this->assertEquals( 1, $relations[0]->sourceContentId );
        $this->assertNull( $relations[0]->sourceContentVersion );
        $this->assertEquals( $this->contentId, $relations[0]->destinationContentId );
        $this->assertEquals( Relation::COMMON | Relation::EMBED, $relations[0]->type );
    }

    /**
     * Test loadRelations function with a type
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::loadRelations
     */
    public function testLoadRelationsWithType1()
    {
        $relations = $this->repositoryHandler->contentHandler()->loadRelations( 1, null, Relation::EMBED );
        $this->assertEquals( 1, count( $relations ) );
        $this->assertEquals( 1, $relations[0]->sourceContentId );
        $this->assertNull( $relations[0]->sourceContentVersion );
        $this->assertEquals( $this->contentId, $relations[0]->destinationContentId );
        $this->assertEquals( Relation::COMMON | Relation::EMBED, $relations[0]->type );
    }

    /**
     * Test loadRelations function with combined types
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::loadRelations
     */
    public function testLoadRelationsWithType2()
    {
        $relations = $this->repositoryHandler->contentHandler()->loadRelations( 1, null, Relation::COMMON | Relation::EMBED );
        $this->assertEquals( 1, count( $relations ) );
        $this->assertEquals( 1, $relations[0]->sourceContentId );
        $this->assertNull( $relations[0]->sourceContentVersion );
        $this->assertEquals( $this->contentId, $relations[0]->destinationContentId );
        $this->assertEquals( Relation::COMMON | Relation::EMBED, $relations[0]->type );
    }

    /**
     * Test loadRelations function with no associated results
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::loadRelations
     */
    public function testLoadRelationsWithTypeNoResult1()
    {
        $this->assertEmpty(
            $this->repositoryHandler->contentHandler()->loadRelations(
                1,
                null,
                Relation::COMMON | Relation::EMBED | Relation::LINK
            )
        );
    }

    /**
     * Test loadRelations function with no associated results
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::loadRelations
     */
    public function testLoadRelationsWithTypeNoResult2()
    {
        $this->assertEmpty(
            $this->repositoryHandler->contentHandler()->loadRelations(
                1,
                null,
                Relation::LINK
            )
        );
    }
}
