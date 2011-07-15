<?php
/**
 * File contains: ezp\Persistence\Tests\RepositoryHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests;
use ezp\Persistence\Tests\InMemoryEngine\RepositoryHandler,
    ezp\Base\ServiceContainer;

/**
 * Test case for RepositoryHandler using in memory storage.
 *
 */
class RepositoryHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ezp\Persistence\Interfaces\RepositoryHandler
     */
    protected $handler;

    public function __construct()
    {
        parent::__construct();
        $this->setName( "RepositoryHandler class tests" );

        // Get in memory RepositoryHandler instance
        $serviceContainer = new ServiceContainer(array(
            'repository_handler' => array( 'class' => 'ezp\\Persistence\\Tests\\InMemoryEngine\\RepositoryHandler' )
        ));
        $this->handler = $serviceContainer->get( 'repository_handler' );
    }

    /**
     * Test that instance is of correct type
     */
    public function testHandler()
    {
        $this->assertInstanceOf( 'ezp\\Persistence\\Interfaces\\RepositoryHandler', $this->handler );
        $this->assertInstanceOf( 'ezp\\Persistence\\Tests\\InMemoryEngine\\RepositoryHandler', $this->handler );
    }

    /**
     * Test that instance is of correct type
     */
    public function testContentHandler()
    {
        $contentHandler = $this->handler->contentHandler();
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Interfaces\\ContentHandler', $contentHandler );
        $this->assertInstanceOf( 'ezp\\Persistence\\Tests\\InMemoryEngine\\ContentHandler', $contentHandler );
    }

     /**
     * Test that instance is of correct type
     */
    public function testSectionHandler()
    {
        $sectionHandler = $this->handler->sectionHandler();
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Interfaces\\SectionHandler', $sectionHandler );
        $this->assertInstanceOf( 'ezp\\Persistence\\Tests\\InMemoryEngine\\SectionHandler', $sectionHandler );
    }
}
