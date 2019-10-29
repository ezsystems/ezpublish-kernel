<?php

/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\RouterHostElementTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\HostElement;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\Host as HostMapMatcher;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use Psr\Log\LoggerInterface;

class RouterHostElementTest extends RouterBaseTest
{
    public function matchProvider(): array
    {
        return [
            [SimplifiedRequest::fromUrl('http://www.example.com'), 'example'],
            [SimplifiedRequest::fromUrl('https://www.example.com'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com/'), 'example'],
            [SimplifiedRequest::fromUrl('https://www.example.com/'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com//'), 'example'],
            [SimplifiedRequest::fromUrl('https://www.example.com//'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com:8080/'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com/first_siteaccess/'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com/?first_siteaccess'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com/?first_sa'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com/first_salt'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com/first_sa.foo'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com/test'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com/test/foo/'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com/test/foo/bar/'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com/test/foo/bar/first_sa'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com/default_sa'), 'example'],

            [SimplifiedRequest::fromUrl('http://www.example.com/first_sa'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com/first_sa/'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com//first_sa//'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com///first_sa///test'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com//first_sa//foo/bar'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com/first_sa/foo'), 'example'],
            [SimplifiedRequest::fromUrl('http://www.example.com:82/first_sa/'), 'example'],
            [SimplifiedRequest::fromUrl('http://third_siteaccess/first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('https://first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa:81/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa:82/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa:83/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa/foo/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa:82/foo/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa:83/foo/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa/foobar/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://second_sa:82/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://second_sa:83/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://second_sa/foo/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://second_sa:82/foo/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://second_sa:83/foo/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://second_sa/foobar/'), 'second_sa'],

            [SimplifiedRequest::fromUrl('http://dev.example.com/second_sa'), 'example'],
            [SimplifiedRequest::fromUrl('http://dev.example.com/second_sa/'), 'example'],
            [SimplifiedRequest::fromUrl('http://dev.example.com/second_sa?param1=foo'), 'example'],
            [SimplifiedRequest::fromUrl('http://dev.example.com/second_sa/foo/'), 'example'],
            [SimplifiedRequest::fromUrl('http://dev.example.com:82/second_sa/'), 'example'],
            [SimplifiedRequest::fromUrl('http://dev.example.com:83/second_sa/'), 'example'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/second_sa/'), 'second_sa'],
        ];
    }

    public function testGetName()
    {
        $matcher = new HostMapMatcher(['host' => 'foo'], []);
        $this->assertSame('host:map', $matcher->getName());

        $matcherHostElement = new HostElement([1]);
        $this->assertSame('host:element', $matcherHostElement->getName());
    }

    /**
     * @dataProvider reverseMatchProvider
     */
    public function testReverseMatch($siteAccessName, $elementNumber, SimplifiedRequest $request, $expectedHost)
    {
        $matcher = new HostElement([$elementNumber]);
        $matcher->setRequest($request);
        $result = $matcher->reverseMatch($siteAccessName);
        $this->assertInstanceOf(HostElement::class, $result);
        $this->assertSame($expectedHost, $result->getRequest()->host);
    }

    public function reverseMatchProvider()
    {
        return [
            ['foo', 1, SimplifiedRequest::fromUrl('http://bar.example.com/'), 'foo.example.com'],
            ['ezdemo_site', 1, SimplifiedRequest::fromUrl('http://ezflow_site.ez.no/'), 'ezdemo_site.ez.no'],
            ['metalfrance', 2, SimplifiedRequest::fromUrl('http://www.lolart.net/'), 'www.metalfrance.net'],
            ['fm', 3, SimplifiedRequest::fromUrl('http://www.phoenix-rises.fr/'), 'www.phoenix-rises.fm'],
        ];
    }

    public function testReverseMatchFail()
    {
        $matcher = new HostElement([3]);
        $matcher->setRequest(new SimplifiedRequest(['host' => 'ez.no']));
        $this->assertNull($matcher->reverseMatch('foo'));
    }

    public function testSerialize()
    {
        $matcher = new HostElement([1]);
        $matcher->setRequest(new SimplifiedRequest(['host' => 'ez.no', 'pathinfo' => '/foo/bar']));
        $sa = new SiteAccess('test', 'test', $matcher);
        $serializedSA1 = serialize($sa);

        $matcher->setRequest(new SimplifiedRequest(['host' => 'ez.no', 'pathinfo' => '/foo/bar/baz']));
        $serializedSA2 = serialize($sa);

        $this->assertSame($serializedSA1, $serializedSA2);
    }

    protected function createRouter(): Router
    {
        return new Router(
            $this->matcherBuilder,
            $this->createMock(LoggerInterface::class),
            'default_sa',
            [
                'HostElement' => [
                    'value' => 2,
                ],
                'Map\\URI' => [
                    'first_sa' => 'first_sa',
                    'second_sa' => 'second_sa',
                ],
                'Map\\Host' => [
                    'first_sa' => 'first_sa',
                    'first_siteaccess' => 'first_sa',
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
            new SiteAccessSetting('example', true),
        ];
    }
}
