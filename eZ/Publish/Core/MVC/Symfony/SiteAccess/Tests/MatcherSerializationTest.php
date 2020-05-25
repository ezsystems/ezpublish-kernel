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
     * @dataProvider matcherProvider
     */
    public function testDeserialize(Matcher $matcher)
    {
        $serializedMatcher = $this->serializeMatcher($matcher);
        $unserializedMatcher = $this->deserializeMatcher($serializedMatcher, get_class($matcher));

        $this->assertEquals($matcher, $unserializedMatcher);
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher $matcher
     *
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
            'MapURI' => [
                new Matcher\Map\URI([
                    'map' => ['key' => 'value'],
                ]),
            ],
            'MapPort' => [
                new Matcher\Map\Port([
                    'map' => ['key' => 'value'],
                ]),
            ],
            'MapHost' => [
                new Matcher\Map\Host([
                    'map' => ['key' => 'value'],
                ]),
            ],
            'CompoundAnd' => [
                new Matcher\Compound\LogicalAnd(
                    [
                        [
                            'matchers' => [
                                'Map\URI' => [
                                    'map' => ['key' => 'value'],
                                ],
                                'Map\Host' => [
                                    'map' => ['key' => 'value'],
                                ],
                            ],
                            'match' => 'site_access_name',
                        ],
                    ]
                ),
            ],
            'CompoundOr' => [
                new Matcher\Compound\LogicalOr(
                    [
                        [
                            'matchers' => [
                                'Map\URI' => [
                                    'map' => ['key' => 'value'],
                                ],
                                'Map\Host' => [
                                    'map' => ['key' => 'value'],
                                ],
                            ],
                            'match' => 'site_access_name',
                        ],
                    ]
                ),
            ],
        ];
    }
}
