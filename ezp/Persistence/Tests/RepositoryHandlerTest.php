<?php
/**
 * File contains: ezp\Persistence\Tests\RepositoryHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests;

/**
 * Test case for RepositoryHandler using in memory storage.
 *
 */
class RepositoryHandlerTest extends HandlerTest
{
    /**
     * Test that instance is of correct type
     *
     * @covers ezp\Persistence\Storage\InMemory\RepositoryHandler::__construct
     */
    public function testHandler()
    {
        $this->assertInstanceOf( 'ezp\\Persistence\\Repository\\Handler', $this->repositoryHandler );
        $this->assertInstanceOf( 'ezp\\Persistence\\Storage\\InMemory\\RepositoryHandler', $this->repositoryHandler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentHandler::__construct
     */
    public function testContentHandler()
    {
        $contentHandler = $this->repositoryHandler->contentHandler();
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Handler', $contentHandler );
        $this->assertInstanceOf( 'ezp\\Persistence\\Storage\\InMemory\\ContentHandler', $contentHandler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers ezp\Persistence\Storage\InMemory\SectionHandler::__construct
     */
    public function testSectionHandler()
    {
        $sectionHandler = $this->repositoryHandler->sectionHandler();
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Section\\Handler', $sectionHandler );
        $this->assertInstanceOf( 'ezp\\Persistence\\Storage\\InMemory\\SectionHandler', $sectionHandler );
    }
}
