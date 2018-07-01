<?php

/**
 * File containing BasicAuthTest test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\HttpClient\Authentication;

use eZ\Publish\Core\REST\Client\HttpClient\Authentication\BasicAuth;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Client\HttpClient;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Basic Auth HTTP Client.
 */
class BasicAuthTest extends TestCase
{
    /**
     * Mock for the inner HTTP client.
     *
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $innerHttpClientMock;

    /**
     * Tests authentication without message.
     */
    public function testAuthWithoutMessage()
    {
        $innerClientMock = $this->getInnerHttpClientMock();

        $client = new BasicAuth($innerClientMock, 'sindelfingen', 's3cr3t');

        $innerClientMock->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/some/path',
                new Message(
                    array('Authorization' => 'Basic c2luZGVsZmluZ2VuOnMzY3IzdA==')
                )
            )->will($this->returnValue(new \stdClass()));

        $result = $client->request('GET', '/some/path');

        $this->assertInstanceOf(
            '\\stdClass',
            $result
        );
    }

    /**
     * Tests authentication with message.
     */
    public function testAuthWithMessage()
    {
        $innerClientMock = $this->getInnerHttpClientMock();

        $client = new BasicAuth($innerClientMock, 'sindelfingen', 's3cr3t');

        $innerClientMock->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                '/some/path',
                new Message(
                    array(
                        'X-Some-Header' => 'foobar',
                        'Authorization' => 'Basic c2luZGVsZmluZ2VuOnMzY3IzdA==',
                    ),
                    'body content'
                )
            )->will($this->returnValue(new \stdClass()));

        $result = $client->request(
            'PUT',
            '/some/path',
            new Message(
                array(
                    'X-Some-Header' => 'foobar',
                ),
                'body content'
            )
        );

        $this->assertInstanceOf(
            '\\stdClass',
            $result
        );
    }

    /**
     * Gets the inner HTTP client mock.
     *
     * @return \eZ\Publish\Core\REST\Client\HttpClient
     */
    protected function getInnerHttpClientMock()
    {
        if (!isset($this->innerHttpClientMock)) {
            $this->innerHttpClientMock = $this->createMock(HttpClient::class);
        }

        return $this->innerHttpClientMock;
    }
}
