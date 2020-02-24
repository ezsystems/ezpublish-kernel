<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\Tests;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\QueryType\BuildIn\SortClausesFactoryInterface;
use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;

abstract class AbstractQueryTypeTest extends TestCase
{
    protected const ROOT_LOCATION_ID = 2;
    protected const ROOT_LOCATION_PATH_STRING = '/1/2/';

    /** @var \eZ\Publish\API\Repository\Repository|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $configResolver;

    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortClausesFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $sortClausesFactory;

    /** @var \eZ\Publish\Core\QueryType\QueryType */
    private $queryType;

    protected function setUp(): void
    {
        $rootLocation = new Location([
            'id' => self::ROOT_LOCATION_ID,
            'pathString' => self::ROOT_LOCATION_PATH_STRING,
        ]);

        $locationService = $this->createMock(LocationService::class);
        $locationService
            ->method('loadLocation')
            ->with(self::ROOT_LOCATION_ID)
            ->willReturn($rootLocation);

        $this->repository = $this->createMock(Repository::class);
        $this->repository->method('getLocationService')->willReturn($locationService);

        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->configResolver
            ->method('getParameter')
            ->with('content.tree_root.location_id')
            ->willReturn(self::ROOT_LOCATION_ID);

        $this->sortClausesFactory = $this->createMock(SortClausesFactoryInterface::class);

        $this->queryType = $this->createQueryType(
            $this->repository,
            $this->configResolver,
            $this->sortClausesFactory
        );
    }

    /**
     * @dataProvider dataProviderForGetQuery
     */
    final public function testGetQuery(array $parameters, Query $expectedQuery): void
    {
        $this->assertEquals($expectedQuery, $this->queryType->getQuery($parameters));
    }

    final public function testGetName(): void
    {
        $this->assertEquals(
            $this->getExpectedName(),
            $this->queryType->getName()
        );
    }

    final public function testGetSupportedParameters(): void
    {
        $this->assertEqualsCanonicalizing(
            $this->getExpectedSupportedParameters(),
            $this->queryType->getSupportedParameters()
        );
    }

    abstract public function dataProviderForGetQuery(): iterable;

    abstract protected function createQueryType(
        Repository $repository,
        ConfigResolverInterface $configResolver,
        SortClausesFactoryInterface $sortClausesFactory
    ): QueryType;

    abstract protected function getExpectedName(): string;

    abstract protected function getExpectedSupportedParameters(): array;
}
