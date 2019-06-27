<?php

/**
 * File containing the ContentTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\Matcher\Id;

use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\Content as ContentIdMatcher;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\BaseTest;

class ContentTest extends BaseTest
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\Content */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new ContentIdMatcher();
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\Content::matchLocation
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
     *
     * @param int|int[] $matchingConfig
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
                123,
                $this->generateLocationForContentId(123),
                true,
            ],
            [
                123,
                $this->generateLocationForContentId(456),
                false,
            ],
            [
                [123, 789],
                $this->generateLocationForContentId(456),
                false,
            ],
            [
                [123, 789],
                $this->generateLocationForContentId(789),
                true,
            ],
        ];
    }

    /**
     * Generates a Location mock in respect of a given content Id.
     *
     * @param int $contentId
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function generateLocationForContentId($contentId)
    {
        $location = $this->getLocationMock();
        $location
            ->expects($this->any())
            ->method('getContentInfo')
            ->will(
                $this->returnValue(
                    $this->getContentInfoMock(['id' => $contentId])
                )
            );

        return $location;
    }

    /**
     * @dataProvider matchContentInfoProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\Content::matchContentInfo
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
     *
     * @param int|int[] $matchingConfig
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
                123,
                $this->getContentInfoMock(['id' => 123]),
                true,
            ],
            [
                123,
                $this->getContentInfoMock(['id' => 456]),
                false,
            ],
            [
                [123, 789],
                $this->getContentInfoMock(['id' => 456]),
                false,
            ],
            [
                [123, 789],
                $this->getContentInfoMock(['id' => 789]),
                true,
            ],
        ];
    }
}
