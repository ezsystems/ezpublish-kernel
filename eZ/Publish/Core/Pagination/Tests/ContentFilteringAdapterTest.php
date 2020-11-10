<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Pagination\Tests;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentList;
use eZ\Publish\API\Repository\Values\Filter\Filter;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentFilteringAdapter;
use eZ\Publish\Core\Search\Tests\TestCase;

final class ContentFilteringAdapterTest extends TestCase
{
    private const EXAMPLE_LANGUAGE_FILTER = [
        'languages' => ['eng-GB', 'pol-PL'],
        'useAlwaysAvailable' => true,
    ];

    /** @var \eZ\Publish\API\Repository\ContentService|\PHPUnit\Framework\MockObject\MockObject */
    private $contentService;

    protected function setUp(): void
    {
        $this->contentService = $this->createMock(ContentService::class);
    }

    public function testGetNbResults(): void
    {
        $expectedNumberOfItems = 10;

        $this->contentService
            ->method('find')
            ->with(
                (new Filter())->sliceBy(0, 0), // Make sure that count query doesn't fetch results
                self::EXAMPLE_LANGUAGE_FILTER
            )
            ->willReturn($this->createExpectedContentList($expectedNumberOfItems));

        $adapter = new ContentFilteringAdapter(
            $this->contentService,
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
        $expectedContentList = $this->createExpectedContentList(10);

        $filter = new Filter();
        $filter->sliceBy(20, 10);

        $this->contentService
            ->method('find')
            ->with($filter, self::EXAMPLE_LANGUAGE_FILTER)
            ->willReturn($expectedContentList);

        $adapter = new ContentFilteringAdapter(
            $this->contentService,
            $filter,
            self::EXAMPLE_LANGUAGE_FILTER
        );

        $this->assertEquals(
            $expectedContentList,
            $adapter->getSlice(10, 20)
        );
    }

    private function createExpectedContentList(int $numberOfItems): ContentList
    {
        $items = [];
        for ($i = 0; $i < $numberOfItems; ++$i) {
            $items[] = $this->createMock(Content::class);
        }

        return new ContentList($numberOfItems, $items);
    }
}
