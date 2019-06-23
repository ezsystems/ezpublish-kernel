<?php

/**
 * File containing the RouterMapURITest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\URI as URIMapMatcher;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use PHPUnit\Framework\TestCase;

class RouterMapURITest extends TestCase
{
    /**
     * @param array  $config
     * @param string $pathinfo
     * @param string $expectedMapKey
     *
     * @dataProvider setRequestProvider
     */
    public function testSetGetRequest($config, $pathinfo, $expectedMapKey)
    {
        $request = new SimplifiedRequest(['pathinfo' => $pathinfo]);
        $matcher = new URIMapMatcher($config);
        $matcher->setRequest($request);
        $this->assertSame($request, $matcher->getRequest());
        $this->assertSame($expectedMapKey, $matcher->getMapKey());
    }

    /**
     * @param string $uri
     * @param string $expectedFixedUpURI
     *
     * @dataProvider fixupURIProvider
     */
    public function testAnalyseURI($uri, $expectedFixedUpURI)
    {
        $matcher = new URIMapMatcher([]);
        $matcher->setRequest(
            new SimplifiedRequest(['pathinfo' => $uri])
        );
        $this->assertSame($expectedFixedUpURI, $matcher->analyseURI($uri));
        // Unserialized matcher should have the same behavior
        $unserializedMatcher = unserialize(serialize($matcher));
        $this->assertSame($expectedFixedUpURI, $unserializedMatcher->analyseURI($uri));
    }

    /**
     * @param string $fullUri
     * @param string $linkUri
     *
     * @dataProvider fixupURIProvider
     */
    public function testAnalyseLink($fullUri, $linkUri)
    {
        $matcher = new URIMapMatcher([]);
        $matcher->setRequest(
            new SimplifiedRequest(['pathinfo' => $fullUri])
        );
        $this->assertSame($fullUri, $matcher->analyseLink($linkUri));
        // Unserialized matcher should have the same behavior
        $unserializedMatcher = unserialize(serialize($matcher));
        $this->assertSame($fullUri, $unserializedMatcher->analyseLink($linkUri));
    }

    public function setRequestProvider()
    {
        return [
            [['foo' => 'bar'], '/bar/baz', 'bar'],
            [['foo' => 'Äpfel'], '/%C3%84pfel/foo', 'Äpfel'],
        ];
    }

    public function fixupURIProvider()
    {
        return [
            ['/foo', '/'],
            ['/Äpfel', '/'],
            ['/my_siteaccess/foo/bar', '/foo/bar'],
            ['/foo/foo/bar', '/foo/bar'],
            ['/foo/foo/bar?something=foo&bar=toto', '/foo/bar?something=foo&bar=toto'],
            ['/vive/le/sucre', '/le/sucre'],
            ['/ezdemo_site/some/thing?foo=ezdemo_site&bar=toto', '/some/thing?foo=ezdemo_site&bar=toto'],
        ];
    }

    public function testReverseMatchFail()
    {
        $config = ['foo' => 'bar'];
        $matcher = new URIMapMatcher($config);
        $this->assertNull($matcher->reverseMatch('non_existent'));
    }

    public function testReverseMatch()
    {
        $config = [
            'some_uri' => 'some_siteaccess',
            'something_else' => 'another_siteaccess',
            'toutouyoutou' => 'ezdemo_site',
        ];
        $request = new SimplifiedRequest(['pathinfo' => '/foo']);
        $matcher = new URIMapMatcher($config);
        $matcher->setRequest($request);

        $result = $matcher->reverseMatch('ezdemo_site');
        $this->assertInstanceOf(URIMapMatcher::class, $result);
        $this->assertSame($request, $matcher->getRequest());
        $this->assertSame('toutouyoutou', $result->getMapKey());
        $this->assertSame('/toutouyoutou/foo', $result->getRequest()->pathinfo);
    }
}
