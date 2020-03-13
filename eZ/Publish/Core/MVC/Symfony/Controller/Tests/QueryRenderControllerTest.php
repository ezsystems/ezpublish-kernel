<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Controller\Tests;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\MVC\Symfony\Controller\QueryRenderController;
use eZ\Publish\Core\MVC\Symfony\View\QueryView;
use eZ\Publish\Core\Pagination\Pagerfanta\AdapterFactory\SearchHitAdapterFactoryInterface;
use eZ\Publish\Core\Query\QueryFactoryInterface;
use Pagerfanta\Adapter\AdapterInterface;
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

    /** @var \eZ\Publish\Core\Query\QueryFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $queryFactory;

    /** @var \eZ\Publish\Core\Pagination\Pagerfanta\AdapterFactory\SearchHitAdapterFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $searchHitAdapterFactory;

    /** @var \eZ\Publish\Core\MVC\Symfony\Controller\QueryRenderController */
    private $controller;

    protected function setUp(): void
    {
        $this->queryFactory = $this->createMock(QueryFactoryInterface::class);
        $this->searchHitAdapterFactory = $this->createMock(SearchHitAdapterFactoryInterface::class);

        $this->controller = new QueryRenderController(
            $this->queryFactory,
            $this->searchHitAdapterFactory
        );
    }

    public function testRenderQueryWithMinOptions(): void
    {
        $adapter = $this->configureMocks(self::MIN_OPTIONS);

        $items = new Pagerfanta($adapter);
        $items->setAllowOutOfRangePages(true);

        $this->assertRenderQueryResult(
            new QueryView('example.html.twig', [
                'items' => $items,
            ]),
            self::MIN_OPTIONS
        );
    }

    public function testRenderQueryWithAllOptions(): void
    {
        $adapter = $this->configureMocks(self::ALL_OPTIONS);

        $items = new Pagerfanta($adapter);
        $items->setAllowOutOfRangePages(true);
        $items->setCurrentPage(self::EXAMPLE_CURRENT_PAGE);
        $items->setMaxPerPage(self::EXAMPLE_MAX_PER_PAGE);

        $this->assertRenderQueryResult(
            new QueryView('example.html.twig', [
                'results' => $items,
            ]),
            self::ALL_OPTIONS,
            new Request(['p' => self::EXAMPLE_CURRENT_PAGE])
        );
    }

    private function configureMocks(array $options): AdapterInterface
    {
        $query = new Query();

        $this->queryFactory
            ->method('create')
            ->with(
                $options['query']['query_type'],
                $options['query']['parameters'] ?? []
            )
            ->willReturn($query);

        $adapter = $this->createMock(AdapterInterface::class);

        $this->searchHitAdapterFactory
            ->method('createAdapter')
            ->with($query)
            ->willReturn($adapter);

        return $adapter;
    }

    private function assertRenderQueryResult(
        QueryView $expectedView,
        array $options,
        Request $request = null
    ): void {
        $this->assertEquals(
            $expectedView,
            $this->controller->renderQuery(
                $request ?? new Request(),
                $options
            )
        );
    }
}
