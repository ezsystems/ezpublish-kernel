<?php

/**
 * File containing the ParentLocationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\Matcher\Id;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\ParentLocation as ParentLocationIdMatcher;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\BaseTest;
use eZ\Publish\API\Repository\Repository;

class ParentLocationTest extends BaseTest
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\ParentLocation */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new ParentLocationIdMatcher();
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\ParentLocation::matchLocation
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
                $this->getLocationMock(['parentLocationId' => 123]),
                true,
            ],
            [
                123,
                $this->getLocationMock(['parentLocationId' => 456]),
                false,
            ],
            [
                [123, 789],
                $this->getLocationMock(['parentLocationId' => 456]),
                false,
            ],
            [
                [123, 789],
                $this->getLocationMock(['parentLocationId' => 789]),
                true,
            ],
        ];
    }

    /**
     * @dataProvider matchContentInfoProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\ParentLocation::matchContentInfo
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
     * @covers \eZ\Publish\Core\MVC\RepositoryAware::setRepository
     *
     * @param int|int[] $matchingConfig
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param bool $expectedResult
     */
    public function testMatchContentInfo($matchingConfig, Repository $repository, $expectedResult)
    {
        $this->matcher->setRepository($repository);
        $this->matcher->setMatchingConfig($matchingConfig);
        $this->assertSame(
            $expectedResult,
            $this->matcher->matchContentInfo($this->getContentInfoMock(['mainLocationId' => 42]))
        );
    }

    public function matchContentInfoProvider()
    {
        return [
            [
                123,
                $this->generateRepositoryMockForParentLocationId(123),
                true,
            ],
            [
                123,
                $this->generateRepositoryMockForParentLocationId(456),
                false,
            ],
            [
                [123, 789],
                $this->generateRepositoryMockForParentLocationId(456),
                false,
            ],
            [
                [123, 789],
                $this->generateRepositoryMockForParentLocationId(789),
                true,
            ],
        ];
    }

    /**
     * Returns a Repository mock configured to return the appropriate Location object with given parent location Id.
     *
     * @param int $parentLocationId
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function generateRepositoryMockForParentLocationId($parentLocationId)
    {
        $locationServiceMock = $this->createMock(LocationService::class);
        $locationServiceMock->expects($this->once())
            ->method('loadLocation')
            ->with(42)
            ->will(
                $this->returnValue(
                    $this->getLocationMock(['parentLocationId' => $parentLocationId])
                )
            );

        $repository = $this->getRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('getLocationService')
            ->will($this->returnValue($locationServiceMock));
        $repository
            ->expects($this->any())
            ->method('getPermissionResolver')
            ->will($this->returnValue($this->getPermissionResolverMock()));

        return $repository;
    }
}
