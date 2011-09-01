<?php
/**
 * File contains: ezp\Persistence\Tests\ContentHandlerTest class
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
    ezp\Persistence\Content\Criterion\ContentId,
    ezp\Base\Exception\NotFound,
    ezp\Content\Version;

/**
 * Test case for ContentHandler using in memory storage.
 *
 */
class ContentHandlerTest extends HandlerTest
{
    /**
     * @var \ezp\Content
     */
    protected $content;

    /**
     *
     * @var int
     */
    protected $contentId;

    /**
     * @var \ezp\Content[]
     */
    protected $contentToDelete = array();

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
                // @todo Use FieldValue object
                'value' => 'Welcome',
                'language' => 'eng-GB',
            )
        );

        $this->content = $this->repositoryHandler->contentHandler()->create( $struct );
        $this->contentToDelete[] = $this->content;
        $this->contentId = $this->content->id;
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
        //$contentHandler->delete( 2 );
        parent::tearDown();
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::create
     */
    public function testCreate()
    {
        $struct = new CreateStruct();
        $struct->name = "test";
        $struct->ownerId = 14;
        $struct->sectionId = 1;
        $struct->typeId = 2;
        $struct->fields[] = new Field(
            array(
                'type' => 'ezstring',
                // @todo Use FieldValue object
                'value' => 'Welcome',
                'language' => 'eng-GB',
            )
        );

        $content = $this->repositoryHandler->contentHandler()->create( $struct );
        $this->contentToDelete[] = $content;
        $this->assertTrue( $content instanceof Content );
        $this->assertEquals( $this->contentId + 1, $content->id );
        $this->assertEquals( 14, $content->ownerId );
        $this->assertEquals( 'test', $content->name );

        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Version', $content->version );
        $this->assertEquals( 14, $content->version->creatorId );
        $this->assertEquals( Version::STATUS_DRAFT, $content->version->state );
        $this->assertEquals( $content->id, $content->version->contentId );
        $this->assertEquals( 1, count( $content->version->fields ) );

        $field = $content->version->fields[0];
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Field', $field );
        $this->assertEquals( 'ezstring', $field->type );
        $this->assertEquals( 'eng-GB', $field->language );
        $this->assertEquals( 'Welcome', $field->value );
        $this->assertEquals( $content->version->versionNo, $field->versionNo );
    }

    /**
     * Test delete function
     *
     * @covers \ezp\Persistence\Storage\InMemory\ContentHandler::delete
     */
    public function testDelete()
    {
        $contentHandler = $this->repositoryHandler->contentHandler();
        $contentHandler->delete( $this->content->id );

        try
        {
            $this->repositoryHandler->searchHandler()->findSingle( new ContentId( $this->content->id ) );
            $this->fail( "Content not removed correctly" );
        }
        catch ( NotFound $e )
        {
        }

        try
        {
            $contentHandler->listVersions( $this->content->id );
            $this->fail( "No version should have been returned but a NotFound exception!" );
        }
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test copy function
     *
     * @covers \ezp\Persistence\Storage\InMemory\ContentHandler::copy
     */
    public function testCopyVersion1()
    {
        $time = time();
        $contentHandler = $this->repositoryHandler->contentHandler();
        $copy = $contentHandler->copy( 1, 1 );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $copy->name );
        $this->assertEquals( 1, $copy->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->typeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->ownerId, "Owner ID does not match" );
        $this->assertEquals( 1, $copy->currentVersionNo, "Current version no does not match" );
        $this->assertEmpty( $copy->locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $copy->id );
        $this->assertEquals( 1, count( $versions ) );
        $this->assertEquals( 1, $versions[0]->versionNo, "Version number does not match" );
        $this->assertEquals( 14, $versions[0]->creatorId, "Creator ID does not match" );
        $this->assertEquals( $copy->id, $versions[0]->contentId );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->modified );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->created );
    }

    /**
     * Test copy function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::copy
     */
    public function testCopyVersion2()
    {
        $time = time();
        $contentHandler = $this->repositoryHandler->contentHandler();
        $copy = $contentHandler->copy( 1, 2 );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $copy->name );
        $this->assertEquals( 1, $copy->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->typeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->ownerId, "Owner ID does not match" );
        $this->assertEquals( 1, $copy->currentVersionNo, "Current version no does not match" );
        $this->assertEmpty( $copy->locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $copy->id );
        $this->assertEquals( 1, count( $versions ) );
        $this->assertEquals( 2, $versions[0]->versionNo, "Version number does not match" );
        $this->assertEquals( 14, $versions[0]->creatorId, "Creator ID does not match" );
        $this->assertEquals( $copy->id, $versions[0]->contentId );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->modified );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->created );
    }

    /**
     * Test copy function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::copy
     */
    public function testCopyAllVersions()
    {
        $time = time();
        $contentHandler = $this->repositoryHandler->contentHandler();
        $copy = $contentHandler->copy( 1, false );
        $this->assertEquals( array( "eng-GB" => "eZ Publish" ), $copy->name );
        $this->assertEquals( 1, $copy->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->typeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->ownerId, "Owner ID does not match" );
        $this->assertEquals( 1, $copy->currentVersionNo, "Current version no does not match" );
        $this->assertEmpty( $copy->locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $copy->id );
        $this->assertEquals( 2, count( $versions ) );
        $this->assertEquals( 1, $versions[0]->versionNo );
        $this->assertEquals( 2, $versions[1]->versionNo );
        $this->assertEquals( 14, $versions[0]->creatorId );
        $this->assertEquals( 14, $versions[1]->creatorId );
        $this->assertEquals( $copy->id, $versions[0]->contentId );
        $this->assertEquals( $copy->id, $versions[1]->contentId );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->modified );
        $this->assertGreaterThanOrEqual( $time, $versions[1]->modified );
        $this->assertGreaterThanOrEqual( $time, $versions[0]->created );
        $this->assertGreaterThanOrEqual( $time, $versions[1]->created );
    }

    /**
     * Test update function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::update
     */
    public function testUpdate()
    {
        $struct = new UpdateStruct;
        $struct->id = $this->contentId;
        $struct->versionNo = 2;
        $struct->name = array( "eng-GB" => "New name", "fre-FR" => "Nouveau nom" );
        $struct->userId = 10;
        $struct->fields[] = new Field(
            array(
                "type" => "ezstring",
                // @todo Use FieldValue object
                "value" => "Welcome2",
                "language" => "eng-GB",
            )
        );

        $content = $this->repositoryHandler->contentHandler()->update( $struct );
        $this->assertTrue( $content instanceof Content );
        $this->assertEquals( $this->contentId, $content->id );
        $this->assertEquals( 10, $content->ownerId );
        $this->assertEquals( array( "eng-GB" => "New name", "fre-FR" => "Nouveau nom" ), $content->name );

        // @todo Test fields!
    }
}
