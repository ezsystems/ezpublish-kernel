<?php

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
    protected function createDecorator(SearchService $service): SearchService
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
            'random_value_5ced05ce17d666.62764500',
        ];

        $serviceMock->expects($this->exactly(1))->method('findContent')->with(...$parameters);

        $decoratedService->findContent(...$parameters);
    }

    public function testFindContentInfoDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Query::class),
            ['random_value_5ced05ce17d6d9.76060657'],
            'random_value_5ced05ce17d6e8.25334516',
        ];

        $serviceMock->expects($this->exactly(1))->method('findContentInfo')->with(...$parameters);

        $decoratedService->findContentInfo(...$parameters);
    }

    public function testFindSingleDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Criterion::class),
            ['random_value_5ced05ce17ef80.90204500'],
            'random_value_5ced05ce17efb1.72362623',
        ];

        $serviceMock->expects($this->exactly(1))->method('findSingle')->with(...$parameters);

        $decoratedService->findSingle(...$parameters);
    }

    public function testSuggestDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce17f030.62511430',
            'random_value_5ced05ce17f044.48777415',
            'random_value_5ced05ce17f050.86508399',
            $this->createMock(Criterion::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('suggest')->with(...$parameters);

        $decoratedService->suggest(...$parameters);
    }

    public function testFindLocationsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(LocationQuery::class),
            ['random_value_5ced05ce17f647.36429312'],
            'random_value_5ced05ce17f678.24389953',
        ];

        $serviceMock->expects($this->exactly(1))->method('findLocations')->with(...$parameters);

        $decoratedService->findLocations(...$parameters);
    }

    public function testSupportsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce17f6c8.39484505'];

        $serviceMock->expects($this->exactly(1))->method('supports')->with(...$parameters);

        $decoratedService->supports(...$parameters);
    }
}
