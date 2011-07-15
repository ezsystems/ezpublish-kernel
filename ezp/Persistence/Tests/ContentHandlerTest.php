<?php
/**
 * File contains: ezp\Persistence\Tests\ContentHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests;
use ezp\Persistence\Content\ContentCreateStruct,
    ezp\Persistence\Content\Field,
    ezp\Content\Version,
    ezp\Base\ServiceContainer;

/**
 * Test case for ContentHandler using in memory storage.
 *
 */
class ContentHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ezp\Persistence\Interfaces\RepositoryHandler
     */
    protected $handler;

    public function __construct()
    {
        parent::__construct();
        $this->setName( "ContentHandler class tests" );

        // Get in memory RepositoryHandler instance
        $serviceContainer = new ServiceContainer(array(
            'repository_handler' => array( 'class' => 'ezp\\Persistence\\Tests\\InMemoryEngine\\RepositoryHandler' )
        ));
        $this->handler = $serviceContainer->get( 'repository_handler' );
    }

     /**
     * Test load function
     */
    public function testLoad()
    {
        $handler = $this->handler->contentHandler();
        $this->assertEquals( null, $handler->load( 1 ) );
    }

     /**
     * Test create / delete functions
     */
    public function testCreateLoadDelete()
    {
        $handler = $this->handler->contentHandler();
        $struct = new ContentCreateStruct();
        $struct->name = "test";
        $struct->ownerId = 14;
        $struct->sectionId = 1;
        $struct->typeId = 2;
        $struct->fields[] = new Field( array(
            'type' => 'ezstring',
            'value' => 'Welcome', // @todo Use FieldValue object
            'language' => 'eng-GB',
        ) );

        $content = $handler->create( $struct );
        $this->assertInstanceOf( 'ezp\\Persistence\\Content', $content );
        $this->assertEquals( 1, $content->id );
        $this->assertEquals( 14, $content->ownerId );
        $this->assertEquals( 'test', $content->name );
        $this->assertEquals( 1, count( $content->versionInfos ) );

        $version = $content->versionInfos[0];
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Version', $version );
        $this->assertEquals( 1, $version->id );
        $this->assertEquals( 14, $version->creatorId );
        $this->assertEquals( Version::STATUS_DRAFT, $version->state );
        $this->assertEquals( $content->id, $version->contentId );
        $this->assertEquals( 1, count( $version->fields ) );

        $field = $version->fields[0];
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Field', $field );
        $this->assertEquals( 1, $field->id );
        $this->assertEquals( 'ezstring', $field->type );
        $this->assertEquals( 'eng-GB', $field->language );
        $this->assertEquals( 'Welcome', $field->value );
        $this->assertEquals( $version->id, $field->versionId);

        $content = $handler->load( $content->id );
        $this->assertInstanceOf( 'ezp\\Persistence\\Content', $content );
        $this->assertEquals( 1, $content->id );
        $this->assertEquals( 14, $content->ownerId );
        $this->assertEquals( 'test', $content->name );
        $this->assertEquals( 1, count( $content->versionInfos ) );

        $version = $content->versionInfos[0];
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Version', $version );
        $this->assertEquals( 1, $version->id );
        $this->assertEquals( 14, $version->creatorId );
        $this->assertEquals( Version::STATUS_DRAFT, $version->state );
        $this->assertEquals( $content->id, $version->contentId );
        $this->assertEquals( 1, count( $version->fields ) );

        $field = $version->fields[0];
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Field', $field );
        $this->assertEquals( 1, $field->id );
        $this->assertEquals( 'ezstring', $field->type );
        $this->assertEquals( 'eng-GB', $field->language );
        $this->assertEquals( 'Welcome', $field->value );
        $this->assertEquals( $version->id, $field->versionId);

        $this->assertTrue( $handler->delete( $content->id ) );
        $this->assertEquals( null, $handler->load( $content->id ) );
        $this->assertEquals( 0, count( $handler->listVersions( $content->id ) ) );

    }
}
