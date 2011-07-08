<?php
/**
 * File contains: ezp\persistence\tests\RepositoryHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage persistence_tests
 */

namespace ezp\persistence\tests;

/**
 * Test case for Location class
 *
 * @package ezp
 * @subpackage persistence_tests
 */
use \ezp\persistence\tests\in_memory_engine\RepositoryHandler, \ezp\base\ServiceContainer;
class RepositoryHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\persistence\Interfaces\RepositoryHandler
     */
    protected $handler;

    public function __construct()
    {
        parent::__construct();
        $this->setName( "RepositoryHandler class tests" );

        // Get in memory RepositoryHandler instance
        $sc = new ServiceContainer(array(
            'repository_handler' => array( 'class' => '\ezp\persistence\tests\in_memory_engine\RepositoryHandler' )
        ));
        $this->handler = $sc->get('repository_handler');
    }

    /**
     * Test that instance is of correct type
     */
    public function testInstanceType()
    {
        $this->assertInstanceOf( '\ezp\persistence\Interfaces\RepositoryHandler', $this->handler );
        $this->assertInstanceOf( '\ezp\persistence\tests\in_memory_engine\RepositoryHandler', $this->handler );
    }

    /**
     * Test that instance is of correct type
     */
    public function testContentHandlerInstanceType()
    {
        $contentHandler = $this->handler->contentHandler();
        $this->assertInstanceOf( '\ezp\persistence\content\Interfaces\ContentHandler', $contentHandler );
        $this->assertInstanceOf( '\ezp\persistence\tests\in_memory_engine\ContentHandler', $contentHandler );
    }

     /**
     * Test that instance is of correct type
     */
    public function testSectionHandlerInstanceType()
    {
        $sectionHandler = $this->handler->sectionHandler();
        $this->assertInstanceOf( '\ezp\persistence\content\Interfaces\SectionHandler', $sectionHandler );
        $this->assertInstanceOf( '\ezp\persistence\tests\in_memory_engine\SectionHandler', $sectionHandler );
    }

     /**
     * Test Section load function
     */
    public function testSectionHandlerLoad()
    {
        $sectionHandler = $this->handler->sectionHandler();
        $this->assertInstanceOf( '\ezp\persistence\content\Section', $sectionHandler->load( 0 ) );// id's starts on zero
    }

     /**
     * Test Section create / update / delete functions
     */
    public function testSectionHandlerCreateUpdateDelete()
    {
        $sectionHandler = $this->handler->sectionHandler();

        $section = $sectionHandler->create( 'Test', 'test' );
        $this->assertInstanceOf( '\ezp\persistence\content\Section', $section );
        $this->assertEquals( 1, $section->id );
        $this->assertEquals( 'Test', $section->name );
        $this->assertEquals( 'test', $section->identifier );

        $this->assertTrue( $sectionHandler->update( $section->id, 'Change', 'change' ) );

        $section = $sectionHandler->load( $section->id );
        $this->assertEquals( 1, $section->id );
        $this->assertEquals( 'Change', $section->name );
        $this->assertEquals( 'change', $section->identifier );
        $this->assertTrue( $sectionHandler->delete( $section->id ) );
    }
}
