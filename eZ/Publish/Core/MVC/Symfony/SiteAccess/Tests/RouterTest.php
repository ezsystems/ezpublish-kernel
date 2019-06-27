<?php

/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\RouterTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilderInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class RouterTest extends TestCase
{
    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder */
    private $matcherBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->matcherBuilder = new MatcherBuilder();
    }

    protected function tearDown()
    {
        putenv('EZPUBLISH_SITEACCESS');
        parent::tearDown();
    }

    public function testConstructDebug()
    {
        return $this->testConstruct(true);
    }

    public function testConstruct($debug = false)
    {
        return new Router(
            $this->matcherBuilder,
            $this->createMock(LoggerInterface::class),
            'default_sa',
            [
                'Map\\URI' => [
                    'first_sa' => 'first_sa',
                    'second_sa' => 'second_sa',
                ],
                'Map\\Host' => [
                    'first_sa' => 'first_sa',
                    'first_siteaccess' => 'first_sa',
                    'third_siteaccess' => 'third_sa',
                ],
                'Map\\Port' => [
                    81 => 'third_sa',
                    82 => 'fourth_sa',
                    83 => 'first_sa',
                    85 => 'first_sa',
                ],
                'Compound\\LogicalAnd' => [
                    [
                        'matchers' => [
                            'Map\\URI' => ['eng' => true],
                            'Map\\Host' => ['fr.ezpublish.dev' => true],
                        ],
                        'match' => 'fr_eng',
                    ],
                    [
                        'matchers' => [
                            'Map\\URI' => ['fre' => true],
                            'Map\\Host' => ['us.ezpublish.dev' => true],
                        ],
                        'match' => 'fr_us',
                    ],
                ],
            ],
            ['first_sa', 'second_sa', 'third_sa', 'fourth_sa', 'headerbased_sa', 'fr_eng', 'fr_us'],
            null,
            $debug
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider matchProvider
     */
    public function testMatch(SimplifiedRequest $request, $siteAccess, Router $router)
    {
        $sa = $router->match($request);
        $this->assertInstanceOf(SiteAccess::class, $sa);
        $this->assertSame($siteAccess, $sa->name);
        // SiteAccess must be serializable as a whole
        // See https://jira.ez.no/browse/EZP-21613
        $this->assertInternalType('string', serialize($sa));
        $router->setSiteAccess();
    }

    /**
     * @depends testConstructDebug
     * @expectedException \eZ\Publish\Core\MVC\Exception\InvalidSiteAccessException
     * @expectedExceptionMessageRegExp /^Invalid siteaccess 'foobar_sa', matched by .+\. Valid siteaccesses are/
     */
    public function testMatchWithDevEnvFail(Router $router)
    {
        $saName = 'foobar_sa';
        putenv("EZPUBLISH_SITEACCESS=$saName");
        $router->match(new SimplifiedRequest());
    }

    /**
     * @depends testConstruct
     * @expectedException \eZ\Publish\Core\MVC\Exception\InvalidSiteAccessException
     * @expectedExceptionMessageRegExp /^Invalid siteaccess 'foobar_sa', matched by .+\.$/
     */
    public function testMatchWithProdEnvFail(Router $router)
    {
        $saName = 'foobar_sa';
        putenv("EZPUBLISH_SITEACCESS=$saName");
        $router->match(new SimplifiedRequest());
    }

    /**
     * @depends testConstruct
     */
    public function testMatchWithEnv(Router $router)
    {
        $saName = 'first_sa';
        putenv("EZPUBLISH_SITEACCESS=$saName");
        $sa = $router->match(new SimplifiedRequest());
        $this->assertInstanceOf(SiteAccess::class, $sa);
        $this->assertSame($saName, $sa->name);
        $this->assertSame('env', $sa->matchingType);
        $router->setSiteAccess();
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess\Router $router
     *
     * @depends testConstruct
     */
    public function testMatchWithRequestHeader(Router $router)
    {
        $saName = 'headerbased_sa';
        $request = Request::create('/foo/bar');
        $request->headers->set('X-Siteaccess', $saName);
        $sa = $router->match(
            new SimplifiedRequest(
                [
                    'headers' => $request->headers->all(),
                ]
            )
        );
        $this->assertInstanceOf(SiteAccess::class, $sa);
        $this->assertSame($saName, $sa->name);
        $this->assertSame('header', $sa->matchingType);
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
            [SimplifiedRequest::fromUrl('http://example.com/first_siteaccess/'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/?first_siteaccess'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/?first_sa'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_salt'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa.foo'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/test'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/bar/'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/bar/first_sa'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/default_sa'), 'default_sa'],

            [SimplifiedRequest::fromUrl('http://example.com/first_sa'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa//'), 'first_sa'],
            // Double slashes shouldn't be considered as one
            [SimplifiedRequest::fromUrl('http://example.com//first_sa//'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa///test'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa/foo'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa/foo/bar'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:82/first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://third_siteaccess/first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('https://first_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa:81/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess/foo/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/foo/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/foo/'), 'first_sa'],

            [SimplifiedRequest::fromUrl('http://example.com/second_sa'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa?param1=foo'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa/foo/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:82/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:83/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/second_sa/'), 'second_sa'],

            [SimplifiedRequest::fromUrl('http://example.com:81/'), 'third_sa'],
            [SimplifiedRequest::fromUrl('https://example.com:81/'), 'third_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:81/foo'), 'third_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:81/foo/bar'), 'third_sa'],

            [SimplifiedRequest::fromUrl('http://example.com:82/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('https://example.com:82/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('https://example.com:82/foo'), 'fourth_sa'],

            [SimplifiedRequest::fromUrl('http://fr.ezpublish.dev/eng'), 'fr_eng'],
            [SimplifiedRequest::fromUrl('http://us.ezpublish.dev/fre'), 'fr_us'],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMatchByNameInvalidSiteAccess()
    {
        $matcherBuilder = $this->createMock(MatcherBuilderInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $router = new Router($matcherBuilder, $logger, 'default_sa', [], ['foo', 'default_sa']);
        $router->matchByName('bar');
    }

    public function testMatchByName()
    {
        $matcherBuilder = $this->createMock(MatcherBuilderInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $matcherClass = 'Map\Host';
        $matchedSiteAccess = 'foo';
        $matcherConfig = [
            'phoenix-rises.fm' => $matchedSiteAccess,
        ];
        $config = [
            'Map\URI' => ['default' => 'default_sa'],
            $matcherClass => $matcherConfig,
        ];

        $router = new Router($matcherBuilder, $logger, 'default_sa', $config, [$matchedSiteAccess, 'default_sa']);
        $matcherInitialSA = $this->createMock(SiteAccess\URILexer::class);
        $router->setSiteAccess(new SiteAccess('test', 'test', $matcherInitialSA));
        $matcherInitialSA
            ->expects($this->once())
            ->method('analyseURI');

        $matcher = $this->createMock(VersatileMatcher::class);
        $matcherBuilder
            ->expects($this->exactly(2))
            ->method('buildMatcher')
            ->will(
                $this->onConsecutiveCalls(
                    $this->createMock(Matcher::class),
                    $matcher
                )
            );

        $reverseMatchedMatcher = $this->createMock(VersatileMatcher::class);
        $matcher
            ->expects($this->once())
            ->method('reverseMatch')
            ->with($matchedSiteAccess)
            ->will($this->returnValue($reverseMatchedMatcher));

        $siteAccess = $router->matchByName($matchedSiteAccess);
        $this->assertInstanceOf(SiteAccess::class, $siteAccess);
        $this->assertSame($reverseMatchedMatcher, $siteAccess->matcher);
        $this->assertSame($matchedSiteAccess, $siteAccess->name);
    }

    public function testMatchByNameNoVersatileMatcher()
    {
        $matcherBuilder = $this->createMock(MatcherBuilderInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $matcherClass = 'Map\Host';
        $defaultSiteAccess = 'default_sa';
        $matcherConfig = [
            'phoenix-rises.fm' => 'foo',
        ];
        $config = [$matcherClass => $matcherConfig];

        $router = new Router($matcherBuilder, $logger, $defaultSiteAccess, $config, [$defaultSiteAccess, 'foo']);
        $router->setSiteAccess(new SiteAccess('test', 'test'));
        $request = $router->getRequest();
        $matcherBuilder
            ->expects($this->once())
            ->method('buildMatcher')
            ->with($matcherClass, $matcherConfig, $request)
            ->will($this->returnValue($this->createMock(Matcher::class)));

        $logger
            ->expects($this->once())
            ->method('notice');
        $this->assertEquals(new SiteAccess($defaultSiteAccess, 'default'), $router->matchByName($defaultSiteAccess));
    }
}
