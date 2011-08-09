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
    ezp\Persistence\Content\Criterion\Operator,
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
        // Removing default objects as well as those created by tests
        foreach ( $this->contentToDelete as $content )
        {
            $contentHandler->delete( $content->id );
        }
        unset( $this->contentId );
        //$contentHandler->delete( 2 );
        parent::tearDown();
    }

    /**
     * Test load function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentHandler::load
     */
    public function testLoad()
    {
        $content = $this->repositoryHandler->contentHandler()->load( $this->content->id );
        $this->assertTrue( $content instanceof Content );
        $this->assertEquals( $this->contentId, $content->id );
        $this->assertEquals( 14, $content->ownerId );
        $this->assertEquals( 'test', $content->name );
        $this->assertEquals( 1, count( $content->versionInfos ) );
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentHandler::create
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
        $this->assertEquals( 1, count( $content->versionInfos ) );

        $version = $content->versionInfos[0];
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Version', $version );
        $this->assertEquals( 2, $version->id );
        $this->assertEquals( 14, $version->creatorId );
        $this->assertEquals( Version::STATUS_DRAFT, $version->state );
        $this->assertEquals( $content->id, $version->contentId );
        $this->assertEquals( 1, count( $version->fields ) );

        $field = $version->fields[0];
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Field', $field );
        $this->assertEquals( 2, $field->id );
        $this->assertEquals( 'ezstring', $field->type );
        $this->assertEquals( 'eng-GB', $field->language );
        $this->assertEquals( 'Welcome', $field->value );
        $this->assertEquals( $version->id, $field->versionNo );
    }

    /**
     * Test delete function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentHandler::delete
     */
    public function testDelete()
    {
        $contentHandler = $this->repositoryHandler->contentHandler();
        $this->assertTrue( $contentHandler->delete( $this->content->id ) );
        $this->assertNull( $contentHandler->load( $this->content->id ) );
        $this->assertEquals( 0, count( $contentHandler->listVersions( $this->content->id ) ) );
    }

    /**
     * Test find function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentHandler::find
     */
    public function testFind()
    {
        $contentHandler = $this->repositoryHandler->contentHandler();
        $this->markTestIncomplete(
            "This test has not been implemented yet."
        );
    }
}
