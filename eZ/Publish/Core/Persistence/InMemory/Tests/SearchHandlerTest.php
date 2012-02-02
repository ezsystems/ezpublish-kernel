<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\SearchHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;
use eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Query\Criterion\ContentId,
    ezp\Content\FieldType\TextLine\Value as TextLineValue,
    ezp\Base\Exception\NotFound;

/**
 * Test case for SearchHandler using in memory storage.
 *
 */
class SearchHandlerTest extends HandlerTest
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
                "type" => "ezstring",
                // FieldValue object compatible with ezstring
                "value" => new FieldValue(
                    array(
                        "data" => new TextLineValue( "Welcome" )
                    )
                ),
                "language" => "eng-GB",
            )
        );

        $this->content = $this->persistenceHandler->contentHandler()->create( $struct );
        $this->contentToDelete[] = $this->content;
        $this->contentId = $this->content->id;
    }

    protected function tearDown()
    {
        $contentHandler = $this->persistenceHandler->contentHandler();

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
     * Test findSingle function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SearchHandler::findSingle
     */
    public function testFindSingle()
    {
        $content = $this->persistenceHandler->searchHandler()->findSingle( new ContentId( $this->content->id ) );
        $this->assertTrue( $content instanceof Content );
        $this->assertEquals( $this->contentId, $content->id );
        $this->assertEquals( 14, $content->ownerId );
        $this->assertEquals( "test", $content->version->name );
        $this->assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\Version", $content->version );
    }
}
