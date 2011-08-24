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
     * Test findSingle function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::findSingle
     */
    public function testFindSingle()
    {
        $content = $this->repositoryHandler->contentHandler()->findSingle( new ContentId( $this->content->id ) );
        $this->assertTrue( $content instanceof Content );
        $this->assertEquals( $this->contentId, $content->id );
        $this->assertEquals( 14, $content->ownerId );
        $this->assertEquals( 'test', $content->name );
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Version', $content->version );
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
        $this->assertEquals( 4, $content->version->id );
        $this->assertEquals( 14, $content->version->creatorId );
        $this->assertEquals( Version::STATUS_DRAFT, $content->version->state );
        $this->assertEquals( $content->id, $content->version->contentId );
        $this->assertEquals( 1, count( $content->version->fields ) );

        $field = $content->version->fields[0];
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Field', $field );
        $this->assertEquals( 2, $field->id );
        $this->assertEquals( 'ezstring', $field->type );
        $this->assertEquals( 'eng-GB', $field->language );
        $this->assertEquals( 'Welcome', $field->value );
        $this->assertEquals( $content->version->id, $field->versionNo );
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
            $contentHandler->findSingle( new ContentId( $this->content->id ) );
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
     * Test find function
     *
     * @covers \ezp\Persistence\Storage\InMemory\ContentHandler::find
     */
    public function testFind()
    {
        $contentHandler = $this->repositoryHandler->contentHandler();
        $this->markTestIncomplete(
            "This test has not been implemented yet."
        );
    }

    /**
     * Test copy function
     *
     * @covers \ezp\Persistence\Storage\InMemory\ContentHandler::copy
     */
    public function testCopyVersion1()
    {
        // To be updated if new tests data is created
        $newContentId = 3;

        $contentHandler = $this->repositoryHandler->contentHandler();
        $time = time();
        $copy = $contentHandler->copy( 1, 1 );
        $this->assertEquals( $newContentId, $copy->id );
        $this->assertEquals( "eZ Publish", $copy->name );
        $this->assertEquals( 1, $copy->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->typeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->ownerId, "Owner ID does not match" );
        $this->assertEquals( 1, $copy->currentVersionNo, "Current version no does not match" );
        $this->assertEmpty( $copy->locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $newContentId );
        $this->assertEquals( 1, count( $versions ) );
        $this->assertEquals( 1, $versions[0]->versionNo, "Version number does not match" );
        $this->assertEquals( 14, $versions[0]->creatorId, "Creator ID does not match" );
        $this->assertEquals( $newContentId, $versions[0]->contentId );
        $this->assertGreaterThanOrEqual( $newContentId, $versions[0]->modified );
        $this->assertGreaterThanOrEqual( $newContentId, $versions[0]->created );
    }

    /**
     * Test copy function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::copy
     */
    public function testCopyVersion2()
    {
        // To be updated if new tests data is created
        $newContentId = 3;

        $contentHandler = $this->repositoryHandler->contentHandler();
        $time = time();
        $copy = $contentHandler->copy( 1, 2 );
        $this->assertEquals( $newContentId, $copy->id );
        $this->assertEquals( "eZ Publish", $copy->name );
        $this->assertEquals( 1, $copy->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->typeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->ownerId, "Owner ID does not match" );
        $this->assertEquals( 1, $copy->currentVersionNo, "Current version no does not match" );
        $this->assertEmpty( $copy->locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $newContentId );
        $this->assertEquals( 1, count( $versions ) );
        $this->assertEquals( 2, $versions[0]->versionNo, "Version number does not match" );
        $this->assertEquals( 14, $versions[0]->creatorId, "Creator ID does not match" );
        $this->assertEquals( $newContentId, $versions[0]->contentId );
        $this->assertGreaterThanOrEqual( $newContentId, $versions[0]->modified );
        $this->assertGreaterThanOrEqual( $newContentId, $versions[0]->created );
    }

    /**
     * Test copy function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::copy
     */
    public function testCopyAllVersions()
    {
        // To be updated if new tests data is created
        $newContentId = 3;

        $contentHandler = $this->repositoryHandler->contentHandler();
        $copy = $contentHandler->copy( 1, false );
        $this->assertEquals( $newContentId, $copy->id );
        $this->assertEquals( "eZ Publish", $copy->name );
        $this->assertEquals( 1, $copy->sectionId, "Section ID does not match" );
        $this->assertEquals( 1, $copy->typeId, "Type ID does not match" );
        $this->assertEquals( 14, $copy->ownerId, "Owner ID does not match" );
        $this->assertEquals( 1, $copy->currentVersionNo, "Current version no does not match" );
        $this->assertEmpty( $copy->locations, "Locations must be empty" );

        $versions = $contentHandler->listVersions( $newContentId );
        $this->assertEquals( 2, count( $versions ) );
        $this->assertEquals( 1, $versions[0]->versionNo );
        $this->assertEquals( 2, $versions[1]->versionNo );
        $this->assertEquals( 14, $versions[0]->creatorId );
        $this->assertEquals( 14, $versions[1]->creatorId );
        $this->assertEquals( $newContentId, $versions[0]->contentId );
        $this->assertEquals( $newContentId, $versions[1]->contentId );
        $this->assertGreaterThanOrEqual( $newContentId, $versions[0]->modified );
        $this->assertGreaterThanOrEqual( $newContentId, $versions[1]->modified );
        $this->assertGreaterThanOrEqual( $newContentId, $versions[0]->created );
        $this->assertGreaterThanOrEqual( $newContentId, $versions[1]->created );
    }
}
