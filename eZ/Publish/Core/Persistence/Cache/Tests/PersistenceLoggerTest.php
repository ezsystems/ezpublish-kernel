<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;
use PHPUnit\Framework\TestCase;

/**
 * Test case for PersistenceLogger.
 */
class PersistenceLoggerTest extends TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Cache\PersistenceLogger
     */
    protected $logger;

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->logger = new PersistenceLogger();
    }

    /**
     * Tear down test (properties).
     */
    protected function tearDown()
    {
        unset($this->logger);
        parent::tearDown();
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\PersistenceLogger::getName
     */
    public function testGetName()
    {
        $this->assertEquals(PersistenceLogger::NAME, $this->logger->getName());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\PersistenceLogger::getCount
     */
    public function testGetCount()
    {
        $this->assertEquals(0, $this->logger->getCount());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\PersistenceLogger::getCalls
     */
    public function testGetCalls()
    {
        $this->assertEquals([], $this->logger->getCalls());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\PersistenceLogger::logCall
     */
    public function testLogCall()
    {
        $this->assertNull($this->logger->logCall(__METHOD__));
        $this->logger->logCall(__METHOD__);
        $this->logger->logCall(__METHOD__);
        $this->logger->logCall(__METHOD__, [33]);

        return $this->logger;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\PersistenceLogger::getCount
     * @depends testLogCall
     *
     * @param \eZ\Publish\Core\Persistence\Cache\PersistenceLogger $logger
     */
    public function testGetCountValues($logger)
    {
        $this->assertEquals(4, $logger->getCount());

        return $logger;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\PersistenceLogger::getCalls
     * @depends testGetCountValues
     *
     * @param \eZ\Publish\Core\Persistence\Cache\PersistenceLogger $logger
     */
    public function testGetCallValues($logger)
    {
        $method = __CLASS__ . '::testLogCall';
        $this->assertEquals(
            [
                ['method' => $method, 'arguments' => []],
                ['method' => $method, 'arguments' => []],
                ['method' => $method, 'arguments' => []],
                ['method' => $method, 'arguments' => [33]],
            ],
            $logger->getCalls()
        );
    }
}
