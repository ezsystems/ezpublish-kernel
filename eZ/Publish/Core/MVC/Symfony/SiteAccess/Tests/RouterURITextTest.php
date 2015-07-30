<?php

/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\RouterURITextTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use PHPUnit_Framework_TestCase;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIText as URITextMatcher;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder;

class RouterURITextTest extends PHPUnit_Framework_TestCase
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
            array(
                'URIText' => array(
                    'prefix' => 'foo',
                    'suffix' => 'bar',
                ),
                'Map\\URI' => array(
                    'first_sa' => 'first_sa',
                    'second_sa' => 'second_sa',
                ),
                'Map\\Host' => array(
                    'first_sa' => 'first_sa',
                    'first_siteaccess' => 'first_sa',
                ),
            ),
            array('first_sa', 'second_sa', 'third_sa', 'fourth_sa', 'fifth_sa', 'test')
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
        return array(
            array(SimplifiedRequest::fromUrl('http://example.com'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('https://example.com'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('https://example.com/'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com//'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('https://example.com//'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com:8080/'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/first_siteaccess/'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/?first_siteaccess'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/?first_sa'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/first_salt'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/first_sa.foo'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/test'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/test/foo/'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/test/foo/bar/'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/test/foo/bar/first_sa'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/default_sa'), 'default_sa'),

            array(SimplifiedRequest::fromUrl('http://example.com/first_sa'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/first_sa/'), 'first_sa'),
            // Double slashes shouldn't be considered as one
            array(SimplifiedRequest::fromUrl('http://example.com//first_sa//'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com///first_sa///test'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com//first_sa//foo/bar'), 'default_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/first_sa/foo'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com:82/first_sa/'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://third_siteaccess/first_sa/'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://first_sa/'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('https://first_sa/'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://first_sa:81/'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess/'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess:82/'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess:83/'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess/foo/'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess:82/foo/'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess:83/foo/'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess/foobar/'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess//foobar/'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess//footestbar/'), 'first_sa'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess/footestbar/'), 'test'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess/footestbar/foobazbar/'), 'test'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess:82/footestbar/'), 'test'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess:83/footestbar/'), 'test'),

            array(SimplifiedRequest::fromUrl('http://example.com/second_sa'), 'second_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/second_sa/'), 'second_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/second_sa?param1=foo'), 'second_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com/second_sa/foo/'), 'second_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com:82/second_sa/'), 'second_sa'),
            array(SimplifiedRequest::fromUrl('http://example.com:83/second_sa/'), 'second_sa'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess:82/second_sa/'), 'second_sa'),
            array(SimplifiedRequest::fromUrl('http://first_siteaccess:83/second_sa/'), 'second_sa'),
        );
    }

    public function testGetName()
    {
        $matcher = new URITextMatcher(array(), array());
        $this->assertSame('uri:text', $matcher->getName());
    }

    public function testAnalyseURI()
    {
        $siteAccessURI = '/footestbar';
        $semanticURI = '/something/hoho';
        $matcher = new URITextMatcher(
            array(
                'prefix' => 'foo',
                'suffix' => 'bar',
            )
        );
        $matcher->setRequest(SimplifiedRequest::fromUrl('http://phoenix-rises.fm/footestbar/blabla'));

        $this->assertSame($semanticURI, $matcher->analyseURI($siteAccessURI . $semanticURI));
    }

    public function testAnalyseLink()
    {
        $siteAccessURI = '/footestbar';
        $semanticURI = '/something/hoho';
        $matcher = new URITextMatcher(
            array(
                'prefix' => 'foo',
                'suffix' => 'bar',
            )
        );
        $matcher->setRequest(SimplifiedRequest::fromUrl('http://phoenix-rises.fm/footestbar/blabla'));

        $this->assertSame($siteAccessURI . $semanticURI, $matcher->analyseLink($semanticURI));
    }

    public function testReverseMatch()
    {
        $semanticURI = '/hihi/hoho';
        $matcher = new URITextMatcher(
            array(
                'prefix' => 'foo',
                'suffix' => 'bar',
            )
        );
        $matcher->setRequest(new SimplifiedRequest(array('pathinfo' => $semanticURI)));

        $result = $matcher->reverseMatch('something');
        $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIText', $result);
        $request = $result->getRequest();
        $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest', $request);
        $this->assertSame("/foosomethingbar{$semanticURI}", $request->pathinfo);
    }
}
