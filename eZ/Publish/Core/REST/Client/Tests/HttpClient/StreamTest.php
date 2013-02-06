<?php
/**
 * File containing the StreamTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\HttpClient;

use eZ\Publish\Core\REST\Client\HttpClient\Stream;
use eZ\Publish\Core\REST\Client\HttpClient\ConnectionException;

/**
 * Test case for stream HTTP client.
 */
class StreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\REST\Client\HttpClient\Stream
     */
    protected $client;

    /**
     * Sets up the testing environment
     */
    public function setUp()
    {
        $this->client = new Stream( 'http://localhost:8042' );

        try
        {
            $this->client->request( 'GET', '/' );
        }
        catch ( ConnectionException $e )
        {
            $this->markTestSkipped( 'No HTTP server at http://localhost:8042 found.' );
        }
    }

    /**
     * Tests the response status
     */
    public function testResponseStatus()
    {
        $response = $this->client->request( 'GET', '/' );

        $this->assertSame( 500, $response->headers['status'] );
    }

    /**
     * Tests that the response body is not empty
     */
    public function testResponseNonEmptyBody()
    {
        $response = $this->client->request( 'GET', '/' );

        $this->assertFalse( empty( $response->body ) );
    }

    /**
     * Tests presence of response headers
     */
    public function testResponseHeadersArray()
    {
        $response = $this->client->request( 'GET', '/' );

        $this->assertTrue( is_array( $response->headers ) );
    }

    /**
     * Test presence of X-Powered-By header
     */
    public function testResponseXPoweredByHeader()
    {
        $response = $this->client->request( 'GET', '/' );

        $this->assertTrue( isset( $response->headers['X-Powered-By'] ) );
        $this->assertTrue( is_string( $response->headers['X-Powered-By'] ) );
    }

    /**
     * Tests that ConnectionException is thrown
     *
     * @expectedException \eZ\Publish\Core\REST\Client\HttpClient\ConnectionException
     */
    public function testConnectionException()
    {
        $client = new Stream( 'http://localhost:54321' );
        $client->request( 'GET', '/' );
    }
}
