<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement as URIElementMatcher;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use Psr\Log\LoggerInterface;

class RouterURIElement2Test extends RouterBaseTest
{
    public function matchProvider(): array
    {
        return [
            [SimplifiedRequest::fromUrl('http://example.com'), 'default_sa'],
            [SimplifiedRequest::fromUrl('https://example.com'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/'), 'default_sa'],
            [SimplifiedRequest::fromUrl('https://example.com/'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com//'), 'default_sa'],
            [SimplifiedRequest::fromUrl('https://example.com//'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:8080/'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_siteaccess/'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/?first_siteaccess'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/?first_sa'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_salt'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa.foo'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/test'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/'), 'test_foo'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/bar/'), 'test_foo'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/bar/first_sa'), 'test_foo'],
            [SimplifiedRequest::fromUrl('http://example.com/default_sa'), 'default_sa'],

            [SimplifiedRequest::fromUrl('http://example.com/first_sa'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa/'), 'first_sa'],
            // Double slashes shouldn't be considered as one
            [SimplifiedRequest::fromUrl('http://example.com//first_sa//'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com///first_sa///test'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com//first_sa//foo/bar'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/foo//bar/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa/foo'), 'first_sa_foo'],
            [SimplifiedRequest::fromUrl('http://example.com:82/first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://third_siteaccess/first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('https://first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa:81/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess/foo/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/foo/baz/'), 'foo_baz'],

            [SimplifiedRequest::fromUrl('http://example.com/second_sa'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa?param1=foo'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa/foo/'), 'second_sa_foo'],
            [SimplifiedRequest::fromUrl('http://example.com:82/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:83/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/second_sa/'), 'second_sa'],
        ];
    }

    /**
     * @param int $level
     * @param string $uri
     * @param string $expectedFixedUpURI
     *
     * @dataProvider analyseProvider
     */
    public function testAnalyseURI($level, $uri, $expectedFixedUpURI)
    {
        $matcher = new URIElementMatcher([$level]);
        $matcher->setRequest(
            new SimplifiedRequest(['pathinfo' => $uri])
        );
        $this->assertSame($expectedFixedUpURI, $matcher->analyseURI($uri));
    }

    /**
     * @param int $level
     * @param string $uri
     * @param string $expectedFixedUpURI
     *
     * @dataProvider analyseProvider
     */
    public function testAnalyseURILevelAsInt($level, $uri, $expectedFixedUpURI)
    {
        $matcher = new URIElementMatcher($level);
        $matcher->setRequest(
            new SimplifiedRequest(['pathinfo' => $uri])
        );
        $this->assertSame($expectedFixedUpURI, $matcher->analyseURI($uri));
    }

    /**
     * @param int $level
     * @param string $fullUri
     * @param string $linkUri
     *
     * @dataProvider analyseProvider
     */
    public function testAnalyseLink($level, $fullUri, $linkUri)
    {
        $matcher = new URIElementMatcher([$level]);
        $matcher->setRequest(
            new SimplifiedRequest(['pathinfo' => $fullUri])
        );
        $this->assertSame($fullUri, $matcher->analyseLink($linkUri));
    }

    public function analyseProvider()
    {
        return [
            [2, '/my/siteaccess/foo/bar', '/foo/bar'],
            [2, '/vive/le/sucre/en-poudre', '/sucre/en-poudre'],
            // Issue https://jira.ez.no/browse/EZP-20125
            [1, '/fre/content/edit/104/1/fre-FR', '/content/edit/104/1/fre-FR'],
            [1, '/fre/utf8-with-accent/é/fre/à/à/fre/é', '/utf8-with-accent/é/fre/à/à/fre/é'],
            [2, '/é/fre/utf8-with-accent/é/fre/à/à/fre/é', '/utf8-with-accent/é/fre/à/à/fre/é'],
            [2, '/prefix/fre/url/alias/prefix/fre/prefix/fre/url', '/url/alias/prefix/fre/prefix/fre/url'],
            // regression after the first fix of EZP-20125
            [1, '/sitaccess', ''],
            [1, '/sitaccess/', '/'],
            [2, '/prefix/siteaccess', ''],
            [2, '/prefix/siteaccess/', '/'],
        ];
    }

    /**
     * @dataProvider reverseMatchProvider
     */
    public function testReverseMatch($siteAccessName, $originalPathinfo)
    {
        $expectedSiteAccessPath = str_replace('_', '/', $siteAccessName);
        $matcher = new URIElementMatcher([2]);
        $matcher->setRequest(new SimplifiedRequest(['pathinfo' => $originalPathinfo]));

        $result = $matcher->reverseMatch($siteAccessName);
        $this->assertInstanceOf(URIElement::class, $result);
        $this->assertSame("/{$expectedSiteAccessPath}{$originalPathinfo}", $result->getRequest()->pathinfo);
        $this->assertSame("/$expectedSiteAccessPath/some/linked/uri", $result->analyseLink('/some/linked/uri'));
        $this->assertSame('/foo/bar/baz', $result->analyseURI("/$expectedSiteAccessPath/foo/bar/baz"));
    }

    public function reverseMatchProvider()
    {
        return [
            ['some_thing', '/foo/bar'],
            ['another_siteaccess', '/foo/bar'],
        ];
    }

    public function testReverseMatchFail()
    {
        $matcher = new URIElementMatcher([2]);
        $matcher->setRequest(new SimplifiedRequest(['pathinfo' => '/my/siteaccess/foo/bar']));
        $this->assertNull($matcher->reverseMatch('another_siteaccess_again_dont_tell_me'));
    }

    public function testSerialize()
    {
        $matcher = new URIElementMatcher([2]);
        $matcher->setRequest(new SimplifiedRequest(['pathinfo' => '/foo/bar']));
        $sa = new SiteAccess('test', 'test', $matcher);
        $serializedSA1 = serialize($sa);

        $matcher->setRequest(new SimplifiedRequest(['pathinfo' => '/foo/bar/baz']));
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
                'URIElement' => [
                    'value' => 2,
                ],
                'Map\\URI' => [
                    'first_sa' => 'first_sa',
                    'second_sa' => 'second_sa',
                ],
                'Map\\Host' => [
                    'first_sa' => 'first_sa',
                    'first_siteaccess' => 'first_sa',
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
            new SiteAccessSetting('test_foo', true),
            new SiteAccessSetting('first_sa_foo', true),
            new SiteAccessSetting('second_sa_foo', true),
            new SiteAccessSetting('foo_baz', true),
        ];
    }
}
