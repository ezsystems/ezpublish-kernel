<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Pagination\Tests;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationList;
use eZ\Publish\API\Repository\Values\Filter\Filter;
use eZ\Publish\Core\Pagination\Pagerfanta\LocationFilteringAdapter;
use eZ\Publish\Core\Search\Tests\TestCase;

final class LocationFilteringAdapterTest extends TestCase
{
    private const EXAMPLE_LANGUAGE_FILTER = [
        'languages' => ['eng-GB', 'pol-PL'],
        'useAlwaysAvailable' => true,
    ];

    /** @var \eZ\Publish\API\Repository\LocationService|\PHPUnit\Framework\MockObject\MockObject */
    private $locationService;

    protected function setUp(): void
    {
        $this->locationService = $this->createMock(LocationService::class);
    }

    public function testGetNbResults(): void
    {
        $expectedNumberOfItems = 10;

        $this->locationService
            ->method('find')
            ->with(
                (new Filter())->sliceBy(0, 0), // Make sure that count query doesn't fetch results
                self::EXAMPLE_LANGUAGE_FILTER
            )
            ->willReturn($this->createExpectedLocationList($expectedNumberOfItems));

        $adapter = new LocationFilteringAdapter(
            $this->locationService,
            (new Filter())->sliceBy(10, 0),
            self::EXAMPLE_LANGUAGE_FILTER
        );

        $this->assertEquals(
            $expectedNumberOfItems,
            $adapter->getNbResults()
        );
    }

    public function testGetSlice(): void
    {
        $expectedContentList = $this->createExpectedLocationList(10);

        $filter = new Filter();
        $filter->sliceBy(20, 10);

        $this->locationService
            ->method('find')
            ->with($filter, self::EXAMPLE_LANGUAGE_FILTER)
            ->willReturn($expectedContentList);

        $adapter = new LocationFilteringAdapter(
            $this->locationService,
            $filter,
            self::EXAMPLE_LANGUAGE_FILTER
        );

        $this->assertEquals(
            $expectedContentList,
            $adapter->getSlice(10, 20)
        );
    }

    private function createExpectedLocationList(int $numberOfItems): LocationList
    {
        $items = [];
        for ($i = 0; $i < $numberOfItems; ++$i) {
            $items[] = $this->createMock(Location::class);
        }

        return new LocationList([
            'totalCount' => $numberOfItems,
            'locations' => $items,
        ]);
    }
}
