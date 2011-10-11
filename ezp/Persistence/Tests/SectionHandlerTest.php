<?php
/**
 * File contains: ezp\Persistence\Tests\SectionHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests;
use ezp\Persistence\Content\Section,
    ezp\Base\Exception\NotFound;

/**
 * Test case for SectionHandler using in memory storage.
 *
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
     * @covers ezp\Persistence\Storage\InMemory\SectionHandler::load
     */
    public function testLoad()
    {
        $section = $this->persistenceHandler->sectionHandler()->load( $this->section->id );
        $this->assertTrue( $section instanceof Section );
        $this->assertEquals( 'Test', $section->name );
        $this->assertEquals( 'test', $section->identifier );
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Storage\InMemory\SectionHandler::create
     */
    public function testCreate()
    {
        $section = $this->persistenceHandler->sectionHandler()->create( 'Test2', 'test2' );
        $this->assertTrue( $section instanceof Section );
        $this->assertEquals( $this->section->id +1, $section->id );
        $this->assertEquals( 'Test2', $section->name );
        $this->assertEquals( 'test2', $section->identifier );
    }

    /**
     * Test update function
     *
     * @covers ezp\Persistence\Storage\InMemory\SectionHandler::update
     */
    public function testUpdate()
    {
        $sectionHandler = $this->persistenceHandler->sectionHandler();

        $sectionHandler->update( $this->section->id, 'Change', 'change' );

        $section = $sectionHandler->load( $this->section->id );
        $this->assertEquals( $this->section->id, $section->id );
        $this->assertEquals( 'Change', $section->name );
        $this->assertEquals( 'change', $section->identifier );
    }

    /**
     * Test delete function
     *
     * @covers ezp\Persistence\Storage\InMemory\SectionHandler::delete
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
