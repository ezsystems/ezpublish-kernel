<?php

/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\RouterURIElementTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement as URIElementMatcher;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder;

class RouterURIElementTest extends TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder
     */
    private $matcherBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->matcherBuilder = new MatcherBuilder();
    }

    public function testConstruct()
    {
        return new Router(
            $this->matcherBuilder,
            $this->getMock('Psr\\Log\\LoggerInterface'),
            'default_sa',
            [
                'URIElement' => [
                    'value' => 1,
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
            ['first_sa', 'second_sa', 'first_siteaccess', 'first_salt', 'first_sa.foo', 'test', 'foo']
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider matchProvider
     */
    public function testMatch(SimplifiedRequest $request, $siteAccess, Router $router)
    {
        $sa = $router->match($request);
        $this->assertInstanceOf('eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess', $sa);
        $this->assertSame($siteAccess, $sa->name);
        $router->setSiteAccess();
    }

    public function matchProvider()
    {
        return [
            [SimplifiedRequest::fromUrl('http://example.com'), 'default_sa'],
            [SimplifiedRequest::fromUrl('https://example.com'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/'), 'default_sa'],
            [SimplifiedRequest::fromUrl('https://example.com/'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com//'), 'default_sa'],
            [SimplifiedRequest::fromUrl('https://example.com//'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:8080/'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_siteaccess/'), 'first_siteaccess'],
            [SimplifiedRequest::fromUrl('http://example.com/?first_siteaccess'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/?first_sa'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_salt'), 'first_salt'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa.foo'), 'first_sa.foo'],
            [SimplifiedRequest::fromUrl('http://example.com/test'), 'test'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/'), 'test'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/bar/'), 'test'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/bar/first_sa'), 'test'],
            [SimplifiedRequest::fromUrl('http://example.com/default_sa'), 'default_sa'],

            [SimplifiedRequest::fromUrl('http://example.com/first_sa'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa/'), 'first_sa'],
            // Double slashes shouldn't be considered as one
            [SimplifiedRequest::fromUrl('http://example.com//first_sa//'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com///first_sa///test'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com//first_sa//foo/bar'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa/foo'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:82/first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://third_siteaccess/first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('https://first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa:81/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess/foo/'), 'foo'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/foo/'), 'foo'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/foo/'), 'foo'],

            [SimplifiedRequest::fromUrl('http://example.com/second_sa'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa?param1=foo'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa/foo/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:82/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:83/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/second_sa/'), 'second_sa'],
        ];
    }

    public function testGetName()
    {
        $matcher = new URIElementMatcher([]);
        $this->assertSame('uri:element', $matcher->getName());
    }

    /**
     * @param string $uri
     * @param string $expectedFixedUpURI
     *
     * @dataProvider analyseProvider
     */
    public function testAnalyseURI($uri, $expectedFixedUpURI)
    {
        $matcher = new URIElementMatcher([1]);
        $matcher->setRequest(
            new SimplifiedRequest(['pathinfo' => $uri])
        );
        $this->assertSame($expectedFixedUpURI, $matcher->analyseURI($uri));
    }

    /**
     * @param string $fullUri
     * @param string $linkUri
     *
     * @dataProvider analyseProvider
     */
    public function testAnalyseLink($fullUri, $linkUri)
    {
        $matcher = new URIElementMatcher([1]);
        $matcher->setRequest(
            new SimplifiedRequest(['pathinfo' => $fullUri])
        );
        $this->assertSame($fullUri, $matcher->analyseLink($linkUri));
    }

    public function analyseProvider()
    {
        return [
            ['/my_siteaccess/foo/bar', '/foo/bar'],
            ['/vive/le/sucre', '/le/sucre'],
        ];
    }

    /**
     * @dataProvider reverseMatchProvider
     */
    public function testReverseMatch($siteAccessName, $originalPathinfo)
    {
        $matcher = new URIElementMatcher([1]);
        $matcher->setRequest(new SimplifiedRequest(['pathinfo' => $originalPathinfo]));
        $result = $matcher->reverseMatch($siteAccessName);
        $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement', $result);
        $this->assertSame("/{$siteAccessName}{$originalPathinfo}", $result->getRequest()->pathinfo);
        $this->assertSame("/$siteAccessName/some/linked/uri", $result->analyseLink('/some/linked/uri'));
        $this->assertSame('/foo/bar/baz', $result->analyseURI("/$siteAccessName/foo/bar/baz"));
    }

    public function reverseMatchProvider()
    {
        return [
            ['something', '/foo/bar'],
            ['something', '/'],
            ['some_thing', '/foo/bar'],
            ['another_siteaccess', '/foo/bar'],
            ['another_siteaccess_again_dont_tell_me', '/foo/bar'],
        ];
    }

    public function testSerialize()
    {
        $matcher = new URIElementMatcher([1]);
        $matcher->setRequest(new SimplifiedRequest(['pathinfo' => '/foo/bar']));
        $sa = new SiteAccess('test', 'test', $matcher);
        $serializedSA1 = serialize($sa);

        $matcher->setRequest(new SimplifiedRequest(['pathinfo' => '/foo/bar/baz']));
        $serializedSA2 = serialize($sa);

        $this->assertSame($serializedSA1, $serializedSA2);
    }
}
