<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\SectionHandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;

use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;

/**
 * Test case for SectionHandler using in memory storage.
 */
class LanguageHandlerTest extends HandlerTest
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $handler;

    /**
     * Setup the SectionHandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->handler = $this->persistenceHandler->contentLanguageHandler();
    }

    /**
     * Removes stuff created in setUp().
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test load function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\LanguageHandler::load
     */
    public function testLoad()
    {
        $language = $this->handler->load( 2 );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language', $language );
        $this->assertEquals( 'eng-US', $language->languageCode );
        $this->assertEquals( 'English (American)', $language->name );
        $this->assertTrue( $language->isEnabled );
    }

    /**
     * Test load function by language code
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\LanguageHandler::loadByLanguageCode
     */
    public function testLoadByLanguageCode()
    {
        $language = $this->handler->loadByLanguageCode( 'eng-US' );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language', $language );
        $this->assertEquals( 'eng-US', $language->languageCode );
        $this->assertEquals( 'English (American)', $language->name );
        $this->assertTrue( $language->isEnabled );
    }

    /**
     * Test load function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\LanguageHandler::loadAll
     */
    public function testLoadAll()
    {
        $languages = $this->handler->loadAll();

        $this->assertEquals( 3, count( $languages ) );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language', $languages['eng-GB'] );
        $this->assertEquals( 'eng-GB', $languages['eng-GB']->languageCode );
        $this->assertEquals( 'English (United Kingdom)', $languages['eng-GB']->name );
        $this->assertTrue( $languages['eng-GB']->isEnabled );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language', $languages['eng-US'] );
        $this->assertEquals( 'eng-US', $languages['eng-US']->languageCode );
        $this->assertEquals( 'English (American)', $languages['eng-US']->name );
        $this->assertTrue( $languages['eng-US']->isEnabled );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language', $languages['ger-DE'] );
        $this->assertEquals( 'ger-DE', $languages['ger-DE']->languageCode );
        $this->assertEquals( 'German', $languages['ger-DE']->name );
        $this->assertTrue( $languages['ger-DE']->isEnabled );

        $struct = new CreateStruct();
        $struct->languageCode = 'nor-NB';
        $struct->name = 'Norwegian Bokmål';
        $struct->isEnabled = false;
        $this->handler->create( $struct );

        $languages = $this->handler->loadAll();

        $this->assertEquals( 4, count( $languages ) );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language', $languages['nor-NB'] );
        $this->assertEquals( 'nor-NB', $languages['nor-NB']->languageCode );
        $this->assertEquals( 'Norwegian Bokmål', $languages['nor-NB']->name );
        $this->assertFalse( $languages['nor-NB']->isEnabled );
    }

    /**
     * Test create function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\LanguageHandler::create
     */
    public function testCreate()
    {
        $struct = new CreateStruct();
        $struct->languageCode = 'nor-NB';
        $struct->name = 'Norwegian Bokmål';
        $struct->isEnabled = false;
        $language = $this->handler->create( $struct );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language', $language );
        $this->assertEquals( 9, $language->id );
        $this->assertEquals( 'nor-NB', $language->languageCode );
        $this->assertEquals( 'Norwegian Bokmål', $language->name );
        $this->assertFalse( $language->isEnabled );
    }

    /**
     * Test update function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\LanguageHandler::update
     */
    public function testUpdate()
    {
        $language = $this->handler->load( 2 );
        $language->languageCode = 'Changed';
        $language->name = 'Changed';
        $language->isEnabled = false;
        $this->handler->update( $language );

        $language = $this->handler->load( 2 );
        $this->assertEquals( 2, $language->id );
        $this->assertEquals( 'Changed', $language->name );
        $this->assertEquals( 'Changed', $language->languageCode );
        $this->assertFalse( $language->isEnabled );
    }

    /**
     * Test delete function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\LanguageHandler::delete
     */
    public function testDelete()
    {
        $this->handler->delete( 4 );
        try
        {
            $this->handler->load( 4 );
            $this->fail( "Language has not been deleted" );
        }
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test delete function throwing LogicException
     *
     * @expectedException \LogicException
     * @covers eZ\Publish\Core\Persistence\InMemory\LanguageHandler::delete
     */
    public function testDeleteThrowsLogicException()
    {
        $language = $this->handler->loadByLanguageCode( 'eng-GB' );
        $this->handler->delete( $language->id );
    }
}
