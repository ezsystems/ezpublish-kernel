<?php
/**
 * File contains: ezp\Persistence\Tests\SectionHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage persistence_tests
 */

namespace ezp\Persistence\Tests;

/**
 * Test case for SectionHandler using in memory storage.
 *
 * @package ezp
 * @subpackage persistence_tests
 */
class SectionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Persistence\Interfaces\RepositoryHandler
     */
    protected $handler;

    public function __construct()
    {
        parent::__construct();
        $this->setName( "SectionHandler class tests" );

        // Get in memory RepositoryHandler instance
        $serviceContainer = new \ezp\Base\ServiceContainer(array(
            'repository_handler' => array( 'class' => '\ezp\Persistence\Tests\InMemoryEngine\RepositoryHandler' )
        ));
        $this->handler = $serviceContainer->get( 'repository_handler' );
    }

     /**
     * Test load function
     */
    public function testLoad()
    {
        $handler = $this->handler->sectionHandler();
        $this->assertInstanceOf( '\ezp\Persistence\Content\Section', $handler->load( 1 ) );
    }

     /**
     * Test create / update / delete functions
     */
    public function testCreateUpdateDelete()
    {
        $handler = $this->handler->sectionHandler();

        $section = $handler->create( 'Test', 'test' );
        $this->assertInstanceOf( '\ezp\Persistence\Content\Section', $section );
        $this->assertEquals( 2, $section->id );
        $this->assertEquals( 'Test', $section->name );
        $this->assertEquals( 'test', $section->identifier );

        $this->assertTrue( $handler->update( $section->id, 'Change', 'change' ) );

        $section = $handler->load( $section->id );
        $this->assertEquals( 2, $section->id );
        $this->assertEquals( 'Change', $section->name );
        $this->assertEquals( 'change', $section->identifier );
        $this->assertTrue( $handler->delete( $section->id ) );
    }
}
