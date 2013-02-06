<?php
/**
 * File containing the SimplifiedRequestTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Routing\Tests;

use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;

class SimplifiedRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $url
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $expectedRequest
     *
     * @dataProvider fromUrlProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest::fromUrl
     */
    public function testFromUrl( $url, $expectedRequest )
    {
        self::assertEquals(
            $expectedRequest,
            SimplifiedRequest::fromUrl( $url )
        );
    }

    public function fromUrlProvider()
    {
        return array(
            array(
                'http://www.example.com/foo/bar',
                new SimplifiedRequest(
                    array(
                        'scheme'       => 'http',
                        'host'         => 'www.example.com',
                        'pathinfo'     => '/foo/bar'
                    )
                )
            ),
            array(
                'https://www.example.com/',
                new SimplifiedRequest(
                    array(
                        'scheme'       => 'https',
                        'host'         => 'www.example.com',
                        'pathinfo'     => '/'
                    )
                )
            ),
            array(
                'http://www.example.com/foo?param=value&this=that',
                new SimplifiedRequest(
                    array(
                        'scheme'       => 'http',
                        'host'         => 'www.example.com',
                        'pathinfo'     => '/foo',
                        'queryParams'    => array( 'param' => 'value', 'this' => 'that' )
                    )
                )
            )
        );
    }
}
