<?php

/**
 * File containing the LocationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\Matcher\Id;

use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\Location as LocationIdMatcher;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\BaseTest;

class LocationTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\Location
     */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new LocationIdMatcher();
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\Location::matchLocation
     * @covers eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
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
        return array(
            array(
                123,
                $this->getLocationMock(array('id' => 123)),
                true,
            ),
            array(
                123,
                $this->getLocationMock(array('id' => 456)),
                false,
            ),
            array(
                array(123, 789),
                $this->getLocationMock(array('id' => 456)),
                false,
            ),
            array(
                array(123, 789),
                $this->getLocationMock(array('id' => 789)),
                true,
            ),
        );
    }

    /**
     * @dataProvider matchContentInfoProvider
     * @covers eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\Location::matchContentInfo
     * @covers eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
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
        return array(
            array(
                123,
                $this->getContentInfoMock(array('mainLocationId' => 123)),
                true,
            ),
            array(
                123,
                $this->getContentInfoMock(array('mainLocationId' => 456)),
                false,
            ),
            array(
                array(123, 789),
                $this->getContentInfoMock(array('mainLocationId' => 456)),
                false,
            ),
            array(
                array(123, 789),
                $this->getContentInfoMock(array('mainLocationId' => 789)),
                true,
            ),
        );
    }
}
