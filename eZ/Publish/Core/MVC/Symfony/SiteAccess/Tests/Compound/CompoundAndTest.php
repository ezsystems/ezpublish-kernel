<?php

/**
 * File containing the CompoundAndTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\Compound;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound\LogicalAnd;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder;
use PHPUnit\Framework\TestCase;

class CompoundAndTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $matcherBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->matcherBuilder = $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\MatcherBuilderInterface');
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound\LogicalAnd
     */
    public function testConstruct()
    {
        return $this->buildMatcher();
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound\LogicalAnd
     */
    private function buildMatcher()
    {
        return new LogicalAnd(
            [
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
                [
                    'matchers' => [
                        'Map\\URI' => ['de' => true],
                        'Map\\Host' => ['jp.ezpublish.dev' => true],
                    ],
                    'match' => 'de_jp',
                ],
            ]
        );
    }

    /**
     * @depends testConstruct
     */
    public function testSetMatcherBuilder(Compound $compoundMatcher)
    {
        $this
            ->matcherBuilder
            ->expects($this->any())
            ->method('buildMatcher')
            ->will($this->returnValue($this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\Matcher')));

        $compoundMatcher->setRequest($this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\Routing\\SimplifiedRequest'));
        $compoundMatcher->setMatcherBuilder($this->matcherBuilder);
        $matchers = $compoundMatcher->getSubMatchers();
        $this->assertInternalType('array', $matchers);
        foreach ($matchers as $matcher) {
            $this->assertInstanceOf('eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\Matcher', $matcher);
        }
    }

    /**
     * @dataProvider matchProvider
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request
     * @param $expectedMatch
     */
    public function testMatch(SimplifiedRequest $request, $expectedMatch)
    {
        $compoundMatcher = $this->buildMatcher();
        $compoundMatcher->setRequest($request);
        $compoundMatcher->setMatcherBuilder(new MatcherBuilder());
        $this->assertSame($expectedMatch, $compoundMatcher->match());
    }

    public function testSetRequest()
    {
        $compoundMatcher = new LogicalAnd(
            [
                [
                    'matchers' => [
                        'Map\\URI' => ['eng' => true],
                        'Map\\Host' => ['fr.ezpublish.dev' => true],
                    ],
                    'match' => 'fr_eng',
                ],
            ]
        );

        $matcher1 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher');
        $matcher2 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher');
        $this->matcherBuilder
            ->expects($this->exactly(2))
            ->method('buildMatcher')
            ->will($this->onConsecutiveCalls($matcher1, $matcher2));

        $request = $this->getMock('eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest');
        $matcher1
            ->expects($this->once())
            ->method('setRequest')
            ->with($request);
        $matcher2
            ->expects($this->once())
            ->method('setRequest')
            ->with($request);

        $compoundMatcher->setRequest($this->getMock('eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest'));
        $compoundMatcher->setMatcherBuilder($this->matcherBuilder);
        $compoundMatcher->setRequest($request);
    }

    public function matchProvider()
    {
        return [
            [SimplifiedRequest::fromUrl('http://fr.ezpublish.dev/eng'), 'fr_eng'],
            [SimplifiedRequest::fromUrl('http://ezpublish.dev/eng'), false],
            [SimplifiedRequest::fromUrl('http://fr.ezpublish.dev/fre'), false],
            [SimplifiedRequest::fromUrl('http://fr.ezpublish.dev/'), false],
            [SimplifiedRequest::fromUrl('http://us.ezpublish.dev/eng'), false],
            [SimplifiedRequest::fromUrl('http://us.ezpublish.dev/fre'), 'fr_us'],
            [SimplifiedRequest::fromUrl('http://ezpublish.dev/fr'), false],
            [SimplifiedRequest::fromUrl('http://jp.ezpublish.dev/de'), 'de_jp'],
        ];
    }

    public function testReverseMatchSiteAccessNotConfigured()
    {
        $compoundMatcher = $this->buildMatcher();
        $this->matcherBuilder
            ->expects($this->any())
            ->method('buildMatcher')
            ->will($this->returnValue($this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher')));

        $compoundMatcher->setRequest($this->getMock('eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest'));
        $compoundMatcher->setMatcherBuilder($this->matcherBuilder);
        $this->assertNull($compoundMatcher->reverseMatch('not_configured_sa'));
    }

    public function testReverseMatchNotVersatile()
    {
        $request = $this->getMock('eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest');
        $siteAccessName = 'fr_eng';
        $mapUriConfig = ['eng' => true];
        $mapHostConfig = ['fr.ezpublish.dev' => true];
        $compoundMatcher = new LogicalAnd(
            [
                [
                    'matchers' => [
                        'Map\URI' => $mapUriConfig,
                        'Map\Host' => $mapHostConfig,
                    ],
                    'match' => $siteAccessName,
                ],
            ]
        );
        $compoundMatcher->setRequest($request);

        $matcher1 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $matcher2 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher');
        $this->matcherBuilder
            ->expects($this->exactly(2))
            ->method('buildMatcher')
            ->will(
                $this->returnValueMap(
                    [
                        ['Map\URI', $mapUriConfig, $request, $matcher1],
                        ['Map\Host', $mapHostConfig, $request, $matcher2],
                    ]
                )
            );

        $matcher1
            ->expects($this->once())
            ->method('reverseMatch')
            ->with($siteAccessName)
            ->will($this->returnValue($this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher')));
        $matcher2
            ->expects($this->never())
            ->method('reverseMatch');

        $compoundMatcher->setMatcherBuilder($this->matcherBuilder);
        $this->assertNull($compoundMatcher->reverseMatch($siteAccessName));
    }

    public function testReverseMatchFail()
    {
        $request = $this->getMock('eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest');
        $siteAccessName = 'fr_eng';
        $mapUriConfig = ['eng' => true];
        $mapHostConfig = ['fr.ezpublish.dev' => true];
        $compoundMatcher = new LogicalAnd(
            [
                [
                    'matchers' => [
                        'Map\URI' => $mapUriConfig,
                        'Map\Host' => $mapHostConfig,
                    ],
                    'match' => $siteAccessName,
                ],
            ]
        );
        $compoundMatcher->setRequest($request);

        $matcher1 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $matcher2 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $this->matcherBuilder
            ->expects($this->exactly(2))
            ->method('buildMatcher')
            ->will(
                $this->returnValueMap(
                    [
                        ['Map\URI', $mapUriConfig, $request, $matcher1],
                        ['Map\Host', $mapHostConfig, $request, $matcher2],
                    ]
                )
            );

        $matcher1
            ->expects($this->once())
            ->method('reverseMatch')
            ->with($siteAccessName)
            ->will($this->returnValue($this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher')));
        $matcher2
            ->expects($this->once())
            ->method('reverseMatch')
            ->with($siteAccessName)
            ->will($this->returnValue(null));

        $compoundMatcher->setMatcherBuilder($this->matcherBuilder);
        $this->assertNull($compoundMatcher->reverseMatch($siteAccessName));
    }

    public function testReverseMatch()
    {
        $request = $this->getMock('eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest');
        $siteAccessName = 'fr_eng';
        $mapUriConfig = ['eng' => true];
        $mapHostConfig = ['fr.ezpublish.dev' => true];
        $compoundMatcher = new LogicalAnd(
            [
                [
                    'matchers' => [
                        'Map\URI' => $mapUriConfig,
                        'Map\Host' => $mapHostConfig,
                    ],
                    'match' => $siteAccessName,
                ],
            ]
        );
        $compoundMatcher->setRequest($request);

        $matcher1 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $matcher2 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $this->matcherBuilder
            ->expects($this->exactly(2))
            ->method('buildMatcher')
            ->will(
                $this->returnValueMap(
                    [
                        ['Map\URI', $mapUriConfig, $request, $matcher1],
                        ['Map\Host', $mapHostConfig, $request, $matcher2],
                    ]
                )
            );

        $reverseMatchedMatcher1 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $matcher1
            ->expects($this->once())
            ->method('reverseMatch')
            ->with($siteAccessName)
            ->will($this->returnValue($reverseMatchedMatcher1));
        $reverseMatchedMatcher2 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $matcher2
            ->expects($this->once())
            ->method('reverseMatch')
            ->with($siteAccessName)
            ->will($this->returnValue($reverseMatchedMatcher2));

        $compoundMatcher->setMatcherBuilder($this->matcherBuilder);
        $result = $compoundMatcher->reverseMatch($siteAccessName);
        $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound\LogicalAnd', $result);
        foreach ($result->getSubMatchers() as $subMatcher) {
            $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher', $subMatcher);
        }
    }

    public function testSerialize()
    {
        $matcher = new LogicalAnd([]);
        $matcher->setRequest(new SimplifiedRequest(['pathinfo' => '/foo/bar']));
        $sa = new SiteAccess('test', 'test', $matcher);
        $serializedSA1 = serialize($sa);

        $matcher->setRequest(new SimplifiedRequest(['pathinfo' => '/foo/bar/baz']));
        $serializedSA2 = serialize($sa);

        $this->assertSame($serializedSA1, $serializedSA2);
    }
}
