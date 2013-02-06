<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\HandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;

use eZ\Publish\Core\Persistence\InMemory\Handler as InMemoryHandler;
use PHPUnit_Framework_TestCase;

/**
 * Test case for Handler using in memory storage.
 */
abstract class HandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        // Get in-memory Handler instance
        $this->persistenceHandler = new InMemoryHandler();
    }

    /**
     * Tear down test (properties)
     */
    protected function tearDown()
    {
        unset( $this->persistenceHandler );
        parent::tearDown();
    }
}
