<?php
/**
 * File contains: ezp\Persistence\Tests\HandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests;
use ezp\Base\ServiceContainer,
    PHPUnit_Framework_TestCase;

/**
 * Test case for Handler using in memory storage.
 *
 */
abstract class HandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        // Get in memory RepositoryHandler instance
        $this->persistenceHandler = new \ezp\Persistence\Storage\InMemory\Handler();
    }
}
