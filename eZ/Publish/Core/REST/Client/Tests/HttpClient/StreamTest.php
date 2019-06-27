<?php

/**
 * File containing the StreamTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\HttpClient;

use eZ\Publish\Core\REST\Client\HttpClient\Stream;
use eZ\Publish\Core\REST\Client\HttpClient\ConnectionException;
use PHPUnit\Framework\TestCase;

/**
 * Test case for stream HTTP client.
 */
class StreamTest extends TestCase
{
    /** @var \eZ\Publish\Core\REST\Client\HttpClient\Stream */
    protected $client;

    /**
     * Sets up the testing environment.
     */
    public function setUp()
    {
        $this->client = new Stream('http://localhost:8042');

        try {
            $this->client->request('GET', '/');
        } catch (ConnectionException $e) {
            $this->markTestSkipped('No HTTP server at http://localhost:8042 found.');
        }
    }

    /**
     * Tests the response status.
     */
    public function testResponseStatus()
    {
        $response = $this->client->request('GET', '/');

        $this->assertSame(500, $response->headers['status']);
    }

    /**
     * Tests that the response body is not empty.
     */
    public function testResponseNonEmptyBody()
    {
        $response = $this->client->request('GET', '/');

        $this->assertFalse(empty($response->body));
    }

    /**
     * Tests presence of response headers.
     */
    public function testResponseHeadersArray()
    {
        $response = $this->client->request('GET', '/');

        $this->assertTrue(is_array($response->headers));
    }

    /**
     * Test presence of X-Powered-By header.
     */
    public function testResponseXPoweredByHeader()
    {
        $response = $this->client->request('GET', '/');

        $this->assertTrue(isset($response->headers['X-Powered-By']));
        $this->assertTrue(is_string($response->headers['X-Powered-By']));
    }

    /**
     * Tests that ConnectionException is thrown.
     *
     * @expectedException \eZ\Publish\Core\REST\Client\HttpClient\ConnectionException
     */
    public function testConnectionException()
    {
        $client = new Stream('http://localhost:54321');
        $client->request('GET', '/');
    }
}
