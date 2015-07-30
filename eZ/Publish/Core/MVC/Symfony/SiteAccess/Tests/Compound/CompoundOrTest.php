<?php

/**
 * File containing the CompoundOrTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\Compound;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound\LogicalOr;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder;
use PHPUnit_Framework_TestCase;

class CompoundOrTest extends PHPUnit_Framework_TestCase
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
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound\LogicalOr
     */
    private function buildMatcher()
    {
        return new LogicalOr(
            array(
                array(
                    'matchers' => array(
                        'Map\\URI' => array('eng' => true),
                        'Map\\Host' => array('fr.ezpublish.dev' => true),
                    ),
                    'match' => 'fr_eng',
                ),
                array(
                    'matchers' => array(
                        'Map\\URI' => array('fre' => true),
                        'Map\\Host' => array('jp.ezpublish.dev' => true),
                    ),
                    'match' => 'fr_jp',
                ),
            )
        );
    }

    /**
     * @depends testConstruct
     */
    public function testSetMatcherBuilder(Compound $compoundMatcher)
    {
        $this->matcherBuilder
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
     * @param string $expectedMatch
     */
    public function testMatch(SimplifiedRequest $request, $expectedMatch)
    {
        $compoundMatcher = $this->buildMatcher();
        $compoundMatcher->setRequest($request);
        $compoundMatcher->setMatcherBuilder(new MatcherBuilder());
        $this->assertSame($expectedMatch, $compoundMatcher->match());
    }

    public function matchProvider()
    {
        return array(
            array(SimplifiedRequest::fromUrl('http://fr.ezpublish.dev/eng'), 'fr_eng'),
            array(SimplifiedRequest::fromUrl('http://ezpublish.dev/eng'), 'fr_eng'),
            array(SimplifiedRequest::fromUrl('http://fr.ezpublish.dev/fre'), 'fr_eng'),
            array(SimplifiedRequest::fromUrl('http://fr.ezpublish.dev/'), 'fr_eng'),
            array(SimplifiedRequest::fromUrl('http://us.ezpublish.dev/eng'), 'fr_eng'),
            array(SimplifiedRequest::fromUrl('http://us.ezpublish.dev/foo'), false),
            array(SimplifiedRequest::fromUrl('http://us.ezpublish.dev/fre'), 'fr_jp'),
            array(SimplifiedRequest::fromUrl('http://jp.ezpublish.dev/foo'), 'fr_jp'),
            array(SimplifiedRequest::fromUrl('http://ezpublish.dev/fr'), false),
        );
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
        $mapUriConfig = array('eng' => true);
        $mapHostConfig = array('fr.ezpublish.dev' => true);
        $compoundMatcher = new LogicalOr(
            array(
                array(
                    'matchers' => array(
                        'Map\URI' => $mapUriConfig,
                        'Map\Host' => $mapHostConfig,
                    ),
                    'match' => $siteAccessName,
                ),
            )
        );
        $compoundMatcher->setRequest($request);

        $matcher1 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher');
        $matcher2 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher');
        $this->matcherBuilder
            ->expects($this->exactly(2))
            ->method('buildMatcher')
            ->will(
                $this->returnValueMap(
                    array(
                        array('Map\URI', $mapUriConfig, $request, $matcher1),
                        array('Map\Host', $mapHostConfig, $request, $matcher2),
                    )
                )
            );

        $matcher1
            ->expects($this->never())
            ->method('reverseMatch');
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
        $mapUriConfig = array('eng' => true);
        $mapHostConfig = array('fr.ezpublish.dev' => true);
        $compoundMatcher = new LogicalOr(
            array(
                array(
                    'matchers' => array(
                        'Map\URI' => $mapUriConfig,
                        'Map\Host' => $mapHostConfig,
                    ),
                    'match' => $siteAccessName,
                ),
            )
        );
        $compoundMatcher->setRequest($request);

        $matcher1 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $matcher2 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $this->matcherBuilder
            ->expects($this->exactly(2))
            ->method('buildMatcher')
            ->will(
                $this->returnValueMap(
                    array(
                        array('Map\URI', $mapUriConfig, $request, $matcher1),
                        array('Map\Host', $mapHostConfig, $request, $matcher2),
                    )
                )
            );

        $matcher1
            ->expects($this->once())
            ->method('reverseMatch')
            ->with($siteAccessName)
            ->will($this->returnValue(null));
        $matcher2
            ->expects($this->once())
            ->method('reverseMatch')
            ->with($siteAccessName)
            ->will($this->returnValue(null));

        $compoundMatcher->setMatcherBuilder($this->matcherBuilder);
        $this->assertNull($compoundMatcher->reverseMatch($siteAccessName));
    }

    public function testReverseMatch1()
    {
        $request = $this->getMock('eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest');
        $siteAccessName = 'fr_eng';
        $mapUriConfig = array('eng' => true);
        $mapHostConfig = array('fr.ezpublish.dev' => true);
        $compoundMatcher = new LogicalOr(
            array(
                array(
                    'matchers' => array(
                        'Map\URI' => $mapUriConfig,
                        'Map\Host' => $mapHostConfig,
                    ),
                    'match' => $siteAccessName,
                ),
            )
        );
        $compoundMatcher->setRequest($request);

        $matcher1 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $matcher2 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $this->matcherBuilder
            ->expects($this->exactly(2))
            ->method('buildMatcher')
            ->will(
                $this->returnValueMap(
                    array(
                        array('Map\URI', $mapUriConfig, $request, $matcher1),
                        array('Map\Host', $mapHostConfig, $request, $matcher2),
                    )
                )
            );

        $reverseMatchedMatcher1 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $matcher1
            ->expects($this->once())
            ->method('reverseMatch')
            ->with($siteAccessName)
            ->will($this->returnValue($reverseMatchedMatcher1));
        $matcher2
            ->expects($this->never())
            ->method('reverseMatch');

        $compoundMatcher->setMatcherBuilder($this->matcherBuilder);
        $result = $compoundMatcher->reverseMatch($siteAccessName);
        $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound\LogicalOr', $result);
        foreach ($result->getSubMatchers() as $subMatcher) {
            $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher', $subMatcher);
        }
    }

    public function testReverseMatch2()
    {
        $request = $this->getMock('eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest');
        $siteAccessName = 'fr_eng';
        $mapUriConfig = array('eng' => true);
        $mapHostConfig = array('fr.ezpublish.dev' => true);
        $compoundMatcher = new LogicalOr(
            array(
                array(
                    'matchers' => array(
                        'Map\URI' => $mapUriConfig,
                        'Map\Host' => $mapHostConfig,
                    ),
                    'match' => $siteAccessName,
                ),
            )
        );
        $compoundMatcher->setRequest($request);

        $matcher1 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $matcher2 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $this->matcherBuilder
            ->expects($this->exactly(2))
            ->method('buildMatcher')
            ->will(
                $this->returnValueMap(
                    array(
                        array('Map\URI', $mapUriConfig, $request, $matcher1),
                        array('Map\Host', $mapHostConfig, $request, $matcher2),
                    )
                )
            );

        $matcher1
            ->expects($this->once())
            ->method('reverseMatch')
            ->with($siteAccessName)
            ->will($this->returnValue(null));
        $reverseMatchedMatcher2 = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher');
        $matcher2
            ->expects($this->once())
            ->method('reverseMatch')
            ->with($siteAccessName)
            ->will($this->returnValue($reverseMatchedMatcher2));

        $compoundMatcher->setMatcherBuilder($this->matcherBuilder);
        $result = $compoundMatcher->reverseMatch($siteAccessName);
        $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound\LogicalOr', $result);
        foreach ($result->getSubMatchers() as $subMatcher) {
            $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher', $subMatcher);
        }
    }

    public function testSerialize()
    {
        $matcher = new LogicalOr(array());
        $matcher->setRequest(new SimplifiedRequest(array('pathinfo' => '/foo/bar')));
        $sa = new SiteAccess('test', 'test', $matcher);
        $serializedSA1 = serialize($sa);

        $matcher->setRequest(new SimplifiedRequest(array('pathinfo' => '/foo/bar/baz')));
        $serializedSA2 = serialize($sa);

        $this->assertSame($serializedSA1, $serializedSA2);
    }
}
