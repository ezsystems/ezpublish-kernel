<?php

/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\RouterHostPortURITest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\Host;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\Port;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use Psr\Log\LoggerInterface;

class RouterHostPortURITest extends RouterBaseTest
{
    public function matchProvider(): array
    {
        return [
            [SimplifiedRequest::fromUrl('http://example.com'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('https://example.com'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('https://example.com/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com//'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('https://example.com//'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:8080/'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_siteaccess/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/?first_siteaccess'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/?first_sa'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_salt'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa.foo'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/test'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/bar/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/bar/first_sa'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/default_sa'), 'fifth_sa'],

            [SimplifiedRequest::fromUrl('http://example.com/first_sa'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa//'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa///test'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa/foo'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa/foo/bar'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:82/first_sa/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('http://third_siteaccess/first_sa/'), 'third_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('https://first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa:81/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess/foo/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/foo/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/foo/'), 'first_sa'],

            [SimplifiedRequest::fromUrl('http://example.com/second_sa'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa?param1=foo'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa/foo/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:82/second_sa/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:83/second_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/second_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/second_sa/'), 'first_sa'],

            [SimplifiedRequest::fromUrl('http://first_sa:123/second_sa'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:123/second_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:123/second_sa?param1=foo'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:123/second_sa/foo/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:123/second_sa'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:123/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:123/second_sa?param1=foo'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:123/second_sa/foo/'), 'second_sa'],

            [SimplifiedRequest::fromUrl('http://example.com:81/'), 'third_sa'],
            [SimplifiedRequest::fromUrl('https://example.com:81/'), 'third_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:81/foo'), 'third_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:81/foo/bar'), 'third_sa'],

            [SimplifiedRequest::fromUrl('http://example.com:82/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('https://example.com:82/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('https://example.com:82/foo'), 'fourth_sa'],
        ];
    }

    public function testSetGetRequestMapHost()
    {
        $mapKey = 'phoenix-rises.fm';
        $request = new SimplifiedRequest(['host' => $mapKey]);
        $matcher = new Host(['foo' => $mapKey]);
        $matcher->setRequest($request);
        $this->assertSame($request, $matcher->getRequest());
        $this->assertSame($mapKey, $matcher->getMapKey());
    }

    public function testReverseHostMatchFail()
    {
        $config = ['foo' => 'bar'];
        $matcher = new Host($config);
        $this->assertNull($matcher->reverseMatch('non_existent'));
    }

    public function testReverseMatchHost()
    {
        $config = [
            'ez.no' => 'some_siteaccess',
            'something_else' => 'another_siteaccess',
            'phoenix-rises.fm' => 'ezdemo_site',
        ];
        $request = new SimplifiedRequest(['host' => 'ez.no']);
        $matcher = new Host($config);
        $matcher->setRequest($request);
        $this->assertSame('ez.no', $matcher->getMapKey());

        $result = $matcher->reverseMatch('ezdemo_site');
        $this->assertInstanceOf(Host::class, $result);
        $this->assertSame($request, $matcher->getRequest());
        $this->assertSame('phoenix-rises.fm', $result->getMapKey());
        $this->assertSame('phoenix-rises.fm', $result->getRequest()->host);
    }

    public function testSetGetRequestMapPort()
    {
        $mapKey = '8000';
        $request = new SimplifiedRequest(['port' => $mapKey]);
        $matcher = new Port(['foo' => $mapKey]);
        $matcher->setRequest($request);
        $this->assertSame($request, $matcher->getRequest());
        $this->assertSame($mapKey, $matcher->getMapKey());
    }

    public function testReversePortMatchFail()
    {
        $config = ['foo' => '8080'];
        $matcher = new Port($config);
        $this->assertNull($matcher->reverseMatch('non_existent'));
    }

    public function testReverseMatchPort()
    {
        $config = [
            '80' => 'some_siteaccess',
            '443' => 'another_siteaccess',
            8000 => 'ezdemo_site',
        ];
        $request = new SimplifiedRequest(['scheme' => 'http', 'host' => 'ez.no']);
        $matcher = new Port($config);
        $matcher->setRequest($request);
        $this->assertSame(80, $matcher->getMapKey());

        $result = $matcher->reverseMatch('ezdemo_site');
        $this->assertInstanceOf(Port::class, $result);
        $this->assertSame($request, $matcher->getRequest());
        $this->assertSame(8000, $result->getMapKey());
        $this->assertSame(8000, $result->getRequest()->port);
        $this->assertSame('http', $result->getRequest()->scheme);
    }

    protected function createRouter(): Router
    {
        return new Router(
            $this->matcherBuilder,
            $this->createMock(LoggerInterface::class),
            'default_sa',
            [
                'Map\\Host' => [
                    'first_sa' => 'first_sa',
                    'first_siteaccess' => 'first_sa',
                    'third_siteaccess' => 'third_sa',
                ],
                'Map\\Port' => [
                    80 => 'fifth_sa',
                    81 => 'third_sa',
                    82 => 'fourth_sa',
                    83 => 'first_sa',
                    85 => 'first_sa',
                    443 => 'fourth_sa',
                ],
                'Map\\URI' => [
                    'first_sa' => 'first_sa',
                    'second_sa' => 'second_sa',
                ],
            ],
            $this->siteAccessProvider
        );
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\SiteAccessSetting[]
     */
    public function getSiteAccessProviderSettings(): array
    {
        return [
            new SiteAccessSetting('first_sa', true),
            new SiteAccessSetting('second_sa', true),
            new SiteAccessSetting('third_sa', true),
            new SiteAccessSetting('fourth_sa', true),
            new SiteAccessSetting('fifth_sa', true),
        ];
    }
}
