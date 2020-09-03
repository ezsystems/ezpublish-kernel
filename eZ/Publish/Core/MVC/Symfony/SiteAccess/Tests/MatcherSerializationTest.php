<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\SerializerTrait;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use PHPUnit\Framework\TestCase;

class MatcherSerializationTest extends TestCase
{
    use SerializerTrait;

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher|null $expected
     *
     * @dataProvider matcherProvider
     */
    public function testDeserialize(Matcher $matcher, $expected = null)
    {
        $serializedMatcher = $this->serializeMatcher($matcher);
        $unserializedMatcher = $this->deserializeMatcher($serializedMatcher, get_class($matcher));
        $expected = $expected === null ? $matcher : $expected;

        $this->assertEquals($expected, $unserializedMatcher);
    }

    /**
     * @return string
     */
    private function serializeMatcher(Matcher $matcher)
    {
        return $this->getSerializer()->serialize(
            $matcher,
            'json'
        );
    }

    /**
     * @param string $serializedMatcher
     * @param string $matcherFQCN
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher|object
     */
    private function deserializeMatcher($serializedMatcher, $matcherFQCN)
    {
        return $this->getSerializer()->deserialize(
            $serializedMatcher,
            $matcherFQCN,
            'json'
        );
    }

    public function matcherProvider()
    {
        $subMatchers = [
            'Map\URI' => [
                'map' => ['key' => 'value'],
            ],
            'Map\Host' => [
                'map' => ['key' => 'value'],
            ],
        ];
        $logicalAnd = new Matcher\Compound\LogicalAnd(
            [
                [
                    'match' => 'site_access_name',
                ],
            ]
        );
        $logicalAnd->setSubMatchers($subMatchers);
        $expectedLogicalAnd = new Matcher\Compound\LogicalAnd([]);
        $expectedLogicalAnd->setSubMatchers($subMatchers);

        $logicalOr = new Matcher\Compound\LogicalOr(
            [
                [
                    'match' => 'site_access_name',
                ],
            ]
        );
        $logicalOr->setSubMatchers($subMatchers);
        $expectedLogicalOr = new Matcher\Compound\LogicalOr([]);
        $expectedLogicalOr->setSubMatchers($subMatchers);

        $expectedMapURI = new Matcher\Map\URI([]);
        $expectedMapURI->setMapKey('site');

        return [
            'URIText' => [
                new Matcher\URIText([
                    'prefix' => 'foo',
                    'suffix' => 'bar',
                ]),
            ],
            'HostText' => [
                new Matcher\HostText([
                    'prefix' => 'foo',
                    'suffix' => 'bar',
                ]),
            ],
            'RegexHost' => [
                new Matcher\Regex\Host([
                    'regex' => 'foo',
                    'itemNumber' => 2,
                ]),
            ],
            'RegexURI' => [
                new Matcher\Regex\URI([
                    'regex' => 'foo',
                    'itemNumber' => 2,
                ]),
            ],
            'URIElement' => [
                new Matcher\URIElement([
                    'elementNumber' => 2,
                ]),
            ],
            'HostElement' => [
                new Matcher\HostElement([
                    'elementNumber' => 2,
                ]),
            ],
            'MapURI' => $this->getMapURIMatcherTestCase(),
            'MapPort' => $this->getMapPortMatcherTestCase(),
            'MapHost' => $this->getMapHostMatcherTestCase(),
            'CompoundAnd' => [
                $logicalAnd,
                $expectedLogicalAnd,
            ],
            'CompoundOr' => [
                $logicalOr,
                $expectedLogicalOr,
            ],
        ];
    }

    private function getMapPortMatcherTestCase()
    {
        $matcherBeforeSerialization = new Matcher\Map\Port(['map' => ['key' => 'value']]);
        $matcherBeforeSerialization->setMapKey('map');

        $matcherAfterDeserialization = new Matcher\Map\Port([]);
        $matcherAfterDeserialization->setMapKey('map');

        return [$matcherBeforeSerialization, $matcherAfterDeserialization];
    }

    private function getMapHostMatcherTestCase()
    {
        $matcherBeforeSerialization = new Matcher\Map\Host(['map' => ['key' => 'value']]);
        $matcherBeforeSerialization->setMapKey('map');

        $matcherAfterDeserialization = new Matcher\Map\Host([]);
        $matcherAfterDeserialization->setMapKey('map');

        return [$matcherBeforeSerialization, $matcherAfterDeserialization];
    }

    private function getMapURIMatcherTestCase()
    {
        $matcherBeforeSerialization = new Matcher\Map\URI(['map' => ['key' => 'value']]);
        $matcherBeforeSerialization->setMapKey('map');

        $matcherAfterDeserialization = new Matcher\Map\URI([]);
        $matcherAfterDeserialization->setMapKey('map');

        return [$matcherBeforeSerialization, $matcherAfterDeserialization];
    }
}
