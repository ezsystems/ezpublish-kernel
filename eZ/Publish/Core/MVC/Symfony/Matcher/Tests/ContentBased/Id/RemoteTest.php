<?php

/**
 * File containing the RemoteTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\Matcher\Id;

use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\Remote as RemoteIdMatcher;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\BaseTest;

class RemoteTest extends BaseTest
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\Remote */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new RemoteIdMatcher();
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\Remote::matchLocation
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
     *
     * @param string|string[] $matchingConfig
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param bool $expectedResult
     */
    public function testMatchLocation($matchingConfig, Location $location, $expectedResult)
    {
        $this->matcher->setMatchingConfig($matchingConfig);
        $this->assertSame($expectedResult, $this->matcher->matchLocation($location));
    }

    public function matchLocationProvider()
    {
        return [
            [
                'foo',
                $this->getLocationMock(['remoteId' => 'foo']),
                true,
            ],
            [
                'foo',
                $this->getLocationMock(['remoteId' => 'bar']),
                false,
            ],
            [
                ['foo', 'baz'],
                $this->getLocationMock(['remoteId' => 'bar']),
                false,
            ],
            [
                ['foo', 'baz'],
                $this->getLocationMock(['remoteId' => 'baz']),
                true,
            ],
        ];
    }

    /**
     * @dataProvider matchContentInfoProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\Remote::matchContentInfo
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
     *
     * @param string|string[] $matchingConfig
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param bool $expectedResult
     */
    public function testMatchContentInfo($matchingConfig, ContentInfo $contentInfo, $expectedResult)
    {
        $this->matcher->setMatchingConfig($matchingConfig);
        $this->assertSame($expectedResult, $this->matcher->matchContentInfo($contentInfo));
    }

    public function matchContentInfoProvider()
    {
        return [
            [
                'foo',
                $this->getContentInfoMock(['remoteId' => 'foo']),
                true,
            ],
            [
                'foo',
                $this->getContentInfoMock(['remoteId' => 'bar']),
                false,
            ],
            [
                ['foo', 'baz'],
                $this->getContentInfoMock(['remoteId' => 'bar']),
                false,
            ],
            [
                ['foo', 'baz'],
                $this->getContentInfoMock(['remoteId' => 'baz']),
                true,
            ],
        ];
    }
}
