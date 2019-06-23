<?php

/**
 * File containing the SimplifiedRequestTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing\Tests;

use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use PHPUnit\Framework\TestCase;

class SimplifiedRequestTest extends TestCase
{
    /**
     * @param string $url
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $expectedRequest
     *
     * @dataProvider fromUrlProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest::fromUrl
     */
    public function testFromUrl($url, $expectedRequest)
    {
        self::assertEquals(
            $expectedRequest,
            SimplifiedRequest::fromUrl($url)
        );
    }

    public function fromUrlProvider()
    {
        return [
            [
                'http://www.example.com/foo/bar',
                new SimplifiedRequest(
                    [
                        'scheme' => 'http',
                        'host' => 'www.example.com',
                        'pathinfo' => '/foo/bar',
                    ]
                ),
            ],
            [
                'https://www.example.com/',
                new SimplifiedRequest(
                    [
                        'scheme' => 'https',
                        'host' => 'www.example.com',
                        'pathinfo' => '/',
                    ]
                ),
            ],
            [
                'http://www.example.com/foo?param=value&this=that',
                new SimplifiedRequest(
                    [
                        'scheme' => 'http',
                        'host' => 'www.example.com',
                        'pathinfo' => '/foo',
                        'queryParams' => ['param' => 'value', 'this' => 'that'],
                    ]
                ),
            ],
        ];
    }
}
