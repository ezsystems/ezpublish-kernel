<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\SearchHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationRemoteId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ObjectStateId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;

/**
 * Test case for SearchHandler using in memory storage.
 */
class SearchHandlerTest extends HandlerTest
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content
     */
    protected $content;

    /**
     * @var int
     */
    protected $contentId;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content[]
     */
    protected $contentToDelete = array();

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $struct = new CreateStruct();
        $struct->name = array( 'eng-GB' => "test" );
        $struct->ownerId = 14;
        $struct->sectionId = 1;
        $struct->typeId = 2;
        $struct->initialLanguageId = 2;
        $struct->fields[] = new Field(
            array(
                "type" => "ezstring",
                // FieldValue object compatible with ezstring
                "value" => new FieldValue(
                    array(
                        "data" => "Welcome"
                    )
                ),
                "languageCode" => "eng-GB",
            )
        );

        $this->content = $this->persistenceHandler->contentHandler()->create( $struct );
        $this->contentToDelete[] = $this->content;
        $this->contentId = $this->content->versionInfo->contentInfo->id;
    }

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
        parent::tearDown();
    }

    /**
     * Test findContent function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SearchHandler::findContent
     */
    public function testFindContent()
    {
        $result = $this->persistenceHandler->searchHandler()->findContent(
            new Query(
                array(
                    'criterion' => new ContentId( $this->content->versionInfo->contentInfo->id ),
                )
            )
        );

        $this->assertInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Search\\SearchResult', $result );
        $this->assertEquals( 1, $result->totalCount );
        $this->assertInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Search\\SearchHit', $result->searchHits[0] );

        $content = $result->searchHits[0]->valueObject;
        $this->assertEquals( 14, $content->versionInfo->contentInfo->ownerId );
        $this->assertEquals( array( 'eng-GB' => 'test' ), $content->versionInfo->names );
        $this->assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo", $content->versionInfo );
    }

    /**
     * Test findSingle function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SearchHandler::findSingle
     */
    public function testFindSingle()
    {
        $content = $this->persistenceHandler->searchHandler()->findSingle( new ContentId( $this->content->versionInfo->contentInfo->id ) );
        $this->assertInstanceOf( 'eZ\Publish\SPI\Persistence\Content', $content );
        $this->assertEquals( $this->contentId, $content->versionInfo->contentInfo->id );
        $this->assertEquals( 14, $content->versionInfo->contentInfo->ownerId );
        $this->assertEquals( array( 'eng-GB' => 'test' ), $content->versionInfo->names );
        $this->assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo", $content->versionInfo );
    }

    /**
     * Test finding content by location remote ID
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SearchHandler::findSingle
     */
    public function testFindByLocationRemoteId()
    {
        $content = $this->persistenceHandler->searchHandler()->findSingle( new LocationRemoteId( 'f3e90596361e31d496d4026eb624c983' ) );
        $this->assertTrue( $content instanceof Content );
        $this->assertEquals( 1, $content->versionInfo->contentInfo->id );
    }

    /**
     * Test finding content by object state ID
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SearchHandler::findContent
     */
    public function testFindByObjectStateId()
    {
        $searchResult = $this->persistenceHandler->searchHandler()->findContent(
            new Query(
                array(
                    'criterion' => new ObjectStateId( 1 )
                )
            )
        );
        $this->assertEquals( 9, $searchResult->totalCount );
    }

    /**
     * Test finding content by language code
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SearchHandler::findContent
     */
    public function testFindByLanguageCode()
    {
        $searchResult = $this->persistenceHandler->searchHandler()->findContent(
            new Query(
                array(
                    'criterion' => new LanguageCode( 'eng-US' )
                )
            )
        );

        $contentIds = array_map(
            function( $searchHit )
            {
                return $searchHit->valueObject->versionInfo->contentInfo->id;
            },
            $searchResult->searchHits
        );

        $this->assertEquals( array( 4, 11, 42, 41, 51 ), $contentIds );
    }
}
