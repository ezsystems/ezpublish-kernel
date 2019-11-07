<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Repository\Decorator\SearchServiceDecorator;

class SearchServiceDecoratorTest extends TestCase
{
    protected function createDecorator(MockObject $service): SearchService
    {
        return new class($service) extends SearchServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(SearchService::class);
    }

    public function testFindContentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Query::class),
            ['random_value_5ced05ce17d631.27870175'],
            true,
        ];

        $serviceMock->expects($this->once())->method('findContent')->with(...$parameters);

        $decoratedService->findContent(...$parameters);
    }

    public function testFindContentInfoDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Query::class),
            ['random_value_5ced05ce17d6d9.76060657'],
            true,
        ];

        $serviceMock->expects($this->once())->method('findContentInfo')->with(...$parameters);

        $decoratedService->findContentInfo(...$parameters);
    }

    public function testFindSingleDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Criterion::class),
            ['random_value_5ced05ce17ef80.90204500'],
            true,
        ];

        $serviceMock->expects($this->once())->method('findSingle')->with(...$parameters);

        $decoratedService->findSingle(...$parameters);
    }

    public function testSuggestDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce17f030.62511430',
            ['random_value_5ced05ce17f044.48777415'],
            10,
            $this->createMock(Criterion::class),
        ];

        $serviceMock->expects($this->once())->method('suggest')->with(...$parameters);

        $decoratedService->suggest(...$parameters);
    }

    public function testFindLocationsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(LocationQuery::class),
            ['random_value_5ced05ce17f647.36429312'],
            true,
        ];

        $serviceMock->expects($this->once())->method('findLocations')->with(...$parameters);

        $decoratedService->findLocations(...$parameters);
    }

    /**
     * @dataProvider getSearchEngineCapabilities
     *
     * @param int $capability
     */
    public function testSupportsDecorator(int $capability): void
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $serviceMock->expects($this->once())->method('supports')->with($capability);

        $decoratedService->supports($capability);
    }

    /**
     * Data provider for testSupportsDecorator.
     *
     * @see testSupportsDecorator
     *
     * @return array
     */
    public function getSearchEngineCapabilities(): array
    {
        return [
            [SearchService::CAPABILITY_SCORING],
            [SearchService::CAPABILITY_FACETS],
            [SearchService::CAPABILITY_CUSTOM_FIELDS],
            [SearchService::CAPABILITY_SPELLCHECK],
            [SearchService::CAPABILITY_HIGHLIGHT],
            [SearchService::CAPABILITY_SUGGEST],
            [SearchService::CAPABILITY_ADVANCED_FULLTEXT],
        ];
    }
}
