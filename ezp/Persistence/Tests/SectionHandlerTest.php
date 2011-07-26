<?php
/**
 * File contains: ezp\Persistence\Tests\SectionHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests;
use ezp\Persistence\Content\Section;

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

        $this->section = $this->repositoryHandler->sectionHandler()->create( "Test", "test" );
    }

    /**
     * Removes stuff created in setUp().
     */
    protected function tearDown()
    {
        $sectionHandler = $this->repositoryHandler->sectionHandler();
        // Removing default objects as well as those created by
        $sectionHandler->delete( 2 );
        $sectionHandler->delete( 3 );
        parent::tearDown();
    }

    /**
     * Test load function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\SectionHandler::load
     */
    public function testLoad()
    {
        $section = $this->repositoryHandler->sectionHandler()->load( $this->section->id );
        $this->assertTrue( $section instanceof Section );
        $this->assertEquals( 'Test', $section->name );
        $this->assertEquals( 'test', $section->identifier );
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\SectionHandler::create
     */
    public function testCreate()
    {
        $section = $this->repositoryHandler->sectionHandler()->create( 'Test2', 'test2' );
        $this->assertTrue( $section instanceof Section );
        $this->assertEquals( 3, $section->id );
        $this->assertEquals( 'Test2', $section->name );
        $this->assertEquals( 'test2', $section->identifier );
    }

    /**
     * Test update function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\SectionHandler::update
     */
    public function testUpdate()
    {
        $sectionHandler = $this->repositoryHandler->sectionHandler();

        $this->assertTrue( $sectionHandler->update( $this->section->id, 'Change', 'change' ) );

        $section = $sectionHandler->load( $this->section->id );
        $this->assertEquals( 2, $section->id );
        $this->assertEquals( 'Change', $section->name );
        $this->assertEquals( 'change', $section->identifier );
    }

    /**
     * Test delete function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\SectionHandler::delete
     */
    public function testDelete()
    {
        $sectionHandler = $this->repositoryHandler->sectionHandler();

        $this->assertTrue( $sectionHandler->delete( $this->section->id ) );
        $this->assertNull( $sectionHandler->load( $this->section->id ) );
    }
}
