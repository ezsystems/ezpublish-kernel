<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Controller\Tests;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\MVC\Symfony\Controller\QueryRenderController;
use eZ\Publish\Core\MVC\Symfony\View\QueryView;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchHitAdapter;
use eZ\Publish\Core\Pagination\Pagerfanta\LocationSearchHitAdapter;
use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class QueryRenderControllerTest extends TestCase
{
    private const EXAMPLE_CURRENT_PAGE = 3;
    private const EXAMPLE_MAX_PER_PAGE = 100;

    private const MIN_OPTIONS = [
        'query' => [
            'query_type' => 'ExampleQuery',
        ],
        'template' => 'example.html.twig',
    ];

    private const ALL_OPTIONS = [
        'query' => [
            'query_type' => 'ExampleQuery',
            'parameters' => [
                'foo' => 'foo',
                'bar' => 'bar',
                'baz' => 'baz',
            ],
            'assign_results_to' => 'results',
        ],
        'template' => 'example.html.twig',
        'pagination' => [
            'enabled' => true,
            'limit' => self::EXAMPLE_MAX_PER_PAGE,
            'page_param' => 'p',
        ],
    ];

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\Core\QueryType\QueryTypeRegistry */
    private $queryTypeRegistry;

    /** @var \eZ\Publish\Core\MVC\Symfony\Controller\QueryRenderController */
    private $controller;

    protected function setUp(): void
    {
        $this->searchService = $this->createMock(SearchService::class);
        $this->queryTypeRegistry = $this->createMock(QueryTypeRegistry::class);

        $this->controller = new QueryRenderController(
            $this->searchService,
            $this->queryTypeRegistry
        );
    }

    public function testRenderContentQueryActionWithMinOptions(): void
    {
        $query = $this->configureQueryTypeRegistryMock(self::MIN_OPTIONS);

        $items = new Pagerfanta(new ContentSearchHitAdapter($query, $this->searchService));
        $items->setAllowOutOfRangePages(true);

        $this->assertContentQueryRenderResult(
            new QueryView('example.html.twig', [
                'items' => $items,
            ]),
            self::MIN_OPTIONS
        );
    }

    public function testRenderContentQueryActionWithAllOptions(): void
    {
        $query = $this->configureQueryTypeRegistryMock(self::ALL_OPTIONS);

        $items = new Pagerfanta(new ContentSearchHitAdapter($query, $this->searchService));
        $items->setAllowOutOfRangePages(true);
        $items->setCurrentPage(self::EXAMPLE_CURRENT_PAGE);
        $items->setMaxPerPage(self::EXAMPLE_MAX_PER_PAGE);

        $this->assertContentQueryRenderResult(
            new QueryView('example.html.twig', [
                'results' => $items,
            ]),
            self::ALL_OPTIONS,
            new Request(['p' => self::EXAMPLE_CURRENT_PAGE])
        );
    }

    public function testRenderContentInfoQueryActionWithMinOptions(): void
    {
        $query = $this->configureQueryTypeRegistryMock(self::MIN_OPTIONS);

        $items = new Pagerfanta(new ContentSearchHitAdapter($query, $this->searchService));
        $items->setAllowOutOfRangePages(true);

        $this->assertContentInfoQueryRenderResult(
            new QueryView('example.html.twig', [
                'items' => $items,
            ]),
            self::MIN_OPTIONS
        );
    }

    public function testRenderContentInfoQueryActionWithAllOptions(): void
    {
        $query = $this->configureQueryTypeRegistryMock(self::ALL_OPTIONS, new LocationQuery());

        $items = new Pagerfanta(new LocationSearchHitAdapter($query, $this->searchService));
        $items->setAllowOutOfRangePages(true);
        $items->setCurrentPage(self::EXAMPLE_CURRENT_PAGE);
        $items->setMaxPerPage(self::EXAMPLE_MAX_PER_PAGE);

        $this->assertContentInfoQueryRenderResult(
            new QueryView('example.html.twig', [
                'results' => $items,
            ]),
            self::ALL_OPTIONS,
            new Request(['p' => self::EXAMPLE_CURRENT_PAGE])
        );
    }

    public function testRenderLocationQueryActionWithMinOptions(): void
    {
        $query = $this->configureQueryTypeRegistryMock(self::MIN_OPTIONS, new LocationQuery());

        $items = new Pagerfanta(new LocationSearchHitAdapter($query, $this->searchService));
        $items->setAllowOutOfRangePages(true);

        $this->assertContentInfoQueryRenderResult(
            new QueryView('example.html.twig', [
                'items' => $items,
            ]),
            self::MIN_OPTIONS
        );
    }

    public function testRenderLocationQueryActionWithAllOptions(): void
    {
        $query = $this->configureQueryTypeRegistryMock(self::ALL_OPTIONS, new LocationQuery());

        $items = new Pagerfanta(new LocationSearchHitAdapter($query, $this->searchService));
        $items->setAllowOutOfRangePages(true);
        $items->setCurrentPage(self::EXAMPLE_CURRENT_PAGE);
        $items->setMaxPerPage(self::EXAMPLE_MAX_PER_PAGE);

        $this->assertLocationQueryRenderResult(
            new QueryView('example.html.twig', [
                'results' => $items,
            ]),
            self::ALL_OPTIONS,
            new Request(['p' => self::EXAMPLE_CURRENT_PAGE])
        );
    }

    private function configureQueryTypeRegistryMock(array $options, ?Query $query = null): Query
    {
        if ($query === null) {
            $query = new Query();
        }

        $queryType = $this->createMock(QueryType::class);
        $queryType
            ->method('getQuery')
            ->with($options['query']['parameters'] ?? [])
            ->willReturn($query);

        $this->queryTypeRegistry
            ->method('getQueryType')
            ->with($options['query']['query_type'] ?? null)
            ->willReturn($queryType);

        return $query;
    }

    private function assertContentQueryRenderResult(
        QueryView $expectedView,
        array $options,
        Request $request = null
    ): void {
        $this->assertEquals(
            $expectedView,
            $this->controller->renderContentQueryAction(
                $request ?? new Request(),
                $options
            )
        );
    }

    private function assertContentInfoQueryRenderResult(
        QueryView $expectedView,
        array $options,
        Request $request = null
    ): void {
        $this->assertEquals(
            $expectedView,
            $this->controller->renderContentInfoQueryAction(
                $request ?? new Request(),
                $options
            )
        );
    }

    private function assertLocationQueryRenderResult(
        QueryView $expectedView,
        array $options,
        Request $request = null
    ): void {
        $this->assertEquals(
            $expectedView,
            $this->controller->renderLocationQueryAction(
                $request ?? new Request(),
                $options
            )
        );
    }
}
