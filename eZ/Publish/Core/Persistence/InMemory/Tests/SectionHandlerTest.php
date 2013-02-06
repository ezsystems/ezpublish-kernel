<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\SectionHandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;

use eZ\Publish\SPI\Persistence\Content\Section;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;

/**
 * Test case for SectionHandler using in memory storage.
 */
class SectionHandlerTest extends HandlerTest
{
    /**
     * @var Section
     */
    protected $section;

    /**
     * Setup the SectionHandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->section = $this->persistenceHandler->sectionHandler()->create( "Test", "test" );
    }

    /**
     * Removes stuff created in setUp().
     */
    protected function tearDown()
    {
        $sectionHandler = $this->persistenceHandler->sectionHandler();
        // Removing default objects as well as those created by
        try
        {
            $sectionHandler->delete( 2 );
            $sectionHandler->delete( 3 );
        }
        catch ( NotFound $e )
        {
        }
        parent::tearDown();
    }

    /**
     * Test load function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SectionHandler::load
     */
    public function testLoad()
    {
        $section = $this->persistenceHandler->sectionHandler()->load( $this->section->id );
        $this->assertTrue( $section instanceof Section );
        $this->assertEquals( 'Test', $section->name );
        $this->assertEquals( 'test', $section->identifier );
    }

    /**
     * Test load function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SectionHandler::loadAll
     */
    public function testLoadAll()
    {
        $section = $this->persistenceHandler->sectionHandler()->loadAll();
        $this->assertTrue( $section[0] instanceof Section );
        $this->assertEquals( 'Standard', $section[0]->name );
        $this->assertEquals( 'standard', $section[0]->identifier );
    }

    /**
     * Test load function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SectionHandler::loadByIdentifier
     */
    public function testLoadByIdentifier()
    {
        $section = $this->persistenceHandler->sectionHandler()->loadByIdentifier( $this->section->identifier );
        $this->assertTrue( $section instanceof Section );
        $this->assertEquals( 'Test', $section->name );
        $this->assertEquals( 'test', $section->identifier );
    }

    /**
     * Test create function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SectionHandler::create
     */
    public function testCreate()
    {
        $section = $this->persistenceHandler->sectionHandler()->create( 'Test2', 'test2' );
        $this->assertTrue( $section instanceof Section );
        $this->assertEquals( $this->section->id + 1, $section->id );
        $this->assertEquals( 'Test2', $section->name );
        $this->assertEquals( 'test2', $section->identifier );
    }

    /**
     * Test update function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SectionHandler::update
     */
    public function testUpdate()
    {
        $sectionHandler = $this->persistenceHandler->sectionHandler();

        $updatedSection = $sectionHandler->update( $this->section->id, 'Change', 'change' );

        $section = $sectionHandler->load( $this->section->id );
        $this->assertEquals( $this->section->id, $section->id );
        $this->assertEquals( 'Change', $section->name );
        $this->assertEquals( 'change', $section->identifier );
        $this->assertEquals( $updatedSection, $section );
    }

    /**
     * Test delete function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SectionHandler::delete
     */
    public function testDelete()
    {
        $sectionHandler = $this->persistenceHandler->sectionHandler();

        $sectionHandler->delete( $this->section->id );
        try
        {
            $sectionHandler->load( $this->section->id );
            $this->fail( "Section has not been deleted" );
        }
        catch ( NotFound $e )
        {
        }
    }
}
