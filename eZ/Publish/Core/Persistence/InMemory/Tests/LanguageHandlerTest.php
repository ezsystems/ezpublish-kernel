<?php
/**
 * File contains: ezp\Persistence\Tests\SectionHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests;
use ezp\Persistence\Content\Language,
    ezp\Persistence\Content\Language\CreateStruct,
    ezp\Persistence\Tests\HandlerTest,
    ezp\Base\Exception\NotFound;

/**
 * Test case for SectionHandler using in memory storage.
 *
 */
class LanguageHandlerTest extends HandlerTest
{
    /**
     * @var \ezp\Persistence\Content\Language
     */
    protected $language;

    /**
     * @var \ezp\Persistence\Content\Language\Handler
     */
    protected $handler;

    /**
     * Setup the SectionHandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->handler = $this->persistenceHandler->contentLanguageHandler();

        foreach ( $this->handler->loadAll() as $item )
        {
            $this->handler->delete( $item->id );
        }

        $struct = new CreateStruct();
        $struct->locale = 'eng-GB';
        $struct->name = 'English (United Kingdom)';
        $struct->isEnabled = true;
        $this->handler->create( $struct );

        $struct = new CreateStruct();
        $struct->locale = 'eng-US';
        $struct->name = 'English (American)';
        $struct->isEnabled = true;
        $this->language = $this->handler->create( $struct );
    }

    /**
     * Removes stuff created in setUp().
     */
    protected function tearDown()
    {
        try
        {
            foreach ( $this->handler->loadAll() as $item )
            {
                $this->handler->delete( $item->id );
            }
            unset( $this->language );
        }
        catch ( NotFound $e )
        {
        }
        parent::tearDown();
    }

    /**
     * Test load function
     *
     * @covers ezp\Persistence\Storage\InMemory\LanguageHandler::load
     */
    public function testLoad()
    {
        $language = $this->handler->load( $this->language->id );
        $this->assertInstanceOf( 'ezp\Persistence\Content\Language', $language );
        $this->assertEquals( 'eng-US', $language->locale );
        $this->assertEquals( 'English (American)', $language->name );
        $this->assertTrue( $language->isEnabled );
    }

    /**
     * Test load function
     *
     * @covers ezp\Persistence\Storage\InMemory\LanguageHandler::loadAll
     */
    public function testLoadAll()
    {
        $languages = $this->handler->loadAll();

        $this->assertEquals( 2, count( $languages ) );
        $this->assertInstanceOf( 'ezp\Persistence\Content\Language', $languages['eng-GB'] );
        $this->assertEquals( 'eng-GB', $languages['eng-GB']->locale );
        $this->assertEquals( 'English (United Kingdom)', $languages['eng-GB']->name );
        $this->assertTrue( $languages['eng-GB']->isEnabled );

        $this->assertInstanceOf( 'ezp\Persistence\Content\Language', $languages['eng-US'] );
        $this->assertEquals( 'eng-US', $languages['eng-US']->locale );
        $this->assertEquals( 'English (American)', $languages['eng-US']->name );
        $this->assertTrue( $languages['eng-US']->isEnabled );

        $struct = new CreateStruct();
        $struct->locale = 'nor-NB';
        $struct->name = 'Norwegian Bokmål';
        $struct->isEnabled = false;
        $this->handler->create( $struct );

        $languages = $this->handler->loadAll();

        $this->assertEquals( 3, count( $languages ) );
        $this->assertInstanceOf( 'ezp\Persistence\Content\Language', $languages['nor-NB'] );
        $this->assertEquals( 'nor-NB', $languages['nor-NB']->locale );
        $this->assertEquals( 'Norwegian Bokmål', $languages['nor-NB']->name );
        $this->assertFalse( $languages['nor-NB']->isEnabled );
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Storage\InMemory\LanguageHandler::create
     */
    public function testCreate()
    {
        $struct = new CreateStruct();
        $struct->locale = 'nor-NB';
        $struct->name = 'Norwegian Bokmål';
        $struct->isEnabled = false;
        $language = $this->handler->create( $struct );

        $this->assertInstanceOf( 'ezp\Persistence\Content\Language', $language );
        $this->assertEquals( $this->language->id +1, $language->id );
        $this->assertEquals( 'nor-NB', $language->locale );
        $this->assertEquals( 'Norwegian Bokmål', $language->name );
        $this->assertFalse( $language->isEnabled );
    }

    /**
     * Test update function
     *
     * @covers ezp\Persistence\Storage\InMemory\LanguageHandler::update
     */
    public function testUpdate()
    {
        $language = $this->handler->load( $this->language->id );
        $language->locale = 'Changed';
        $language->name = 'Changed';
        $language->isEnabled = false;
        $this->handler->update( $language );

        $language = $this->handler->load( $this->language->id );
        $this->assertEquals( $this->language->id, $language->id );
        $this->assertEquals( 'Changed', $language->name );
        $this->assertEquals( 'Changed', $language->locale );
        $this->assertFalse( $language->isEnabled );
    }

    /**
     * Test delete function
     *
     * @covers ezp\Persistence\Storage\InMemory\LanguageHandler::delete
     */
    public function testDelete()
    {
        $this->handler->delete( $this->language->id );
        try
        {
            $this->handler->load( $this->language->id );
            $this->fail( "Language has not been deleted" );
        }
        catch ( NotFound $e )
        {
        }
    }
}
