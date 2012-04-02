<?php
/**
 * File containing the ContentTypeServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client\Tests\HttpClient;

use eZ\Publish\API\REST\Client\HttpClient\Stream;
use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the ContentTypeService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentTypeService
 * @group integration
 */
class StreamTest extends \PHPUnit_Framework_TestCase
{
    public function testResponseStatus()
    {
        $client = new Stream( 'http://localhost:8042' );

        $response = $client->request( 'GET', '/' );

        $this->assertSame( 200, $response->status );
    }

    public function testResponseNonEmptyBody()
    {
        $client = new Stream( 'http://localhost:8042' );

        $response = $client->request( 'GET', '/' );

        $this->assertFalse( empty( $response->body ) );
    }

    public function testResponseHeadersArray()
    {
        $client = new Stream( 'http://localhost:8042' );

        $response = $client->request( 'GET', '/' );

        $this->assertTrue( is_array( $response->headers ) );
    }

    public function testResponseXPoweredByHeader()
    {
        $client = new Stream( 'http://localhost:8042' );

        $response = $client->request( 'GET', '/' );

        $this->assertTrue( isset( $response->headers['X-Powered-By'] ) );
        $this->assertTrue( is_string( $response->headers['X-Powered-By'] ) );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Client\HttpClient\ConnectionException
     */
    public function testConnectionException()
    {
        $client = new Stream( 'http://localhost:54321' );
        $response = $client->request( 'GET', '/' );
    }
}

