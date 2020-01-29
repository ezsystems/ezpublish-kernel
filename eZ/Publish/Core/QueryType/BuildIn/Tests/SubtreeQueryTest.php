<?php

declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\Tests;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location\Depth;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location\Priority;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\QueryType\BuildIn\SubtreeQueryType;
use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\Core\Repository\Values\Content\Location;

final class SubtreeQueryTest extends AbstractQueryTypeTest
{
    private const EXAMPLE_LOCATION_ID = 54;
    private const EXAMPLE_LOCATION_PATH_STRING = '/1/2/54/';
    private const EXAMPLE_LOCATION_DEPTH = 3;

    public function dataProviderForGetQuery(): iterable
    {
        $location = new Location([
            'id' => self::EXAMPLE_LOCATION_ID,
            'depth' => self::EXAMPLE_LOCATION_DEPTH,
            'pathString' => self::EXAMPLE_LOCATION_PATH_STRING,
        ]);

        yield 'basic' => [
            [
                'location' => $location,
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new Subtree(self::EXAMPLE_LOCATION_PATH_STRING),
                    new Visibility(Visibility::VISIBLE),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
            ]),
        ];

        yield 'filter by relative depth' => [
            [
                'location' => $location,
                'depth' => 2,
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new LogicalAnd([
                        new Subtree(self::EXAMPLE_LOCATION_PATH_STRING),
                        new Depth(Operator::LTE, self::EXAMPLE_LOCATION_DEPTH + 2),
                    ]),
                    new Visibility(Visibility::VISIBLE),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
            ]),
        ];

        yield 'filter by visibility' => [
            [
                'location' => $location,
                'filter' => [
                    'visible_only' => false,
                ],
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new Subtree(self::EXAMPLE_LOCATION_PATH_STRING),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
            ]),
        ];

        yield 'filter by content type' => [
            [
                'location' => $location,
                'filter' => [
                    'content_type' => [
                        'article',
                        'blog_post',
                        'folder',
                    ],
                ],
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new Subtree(self::EXAMPLE_LOCATION_PATH_STRING),
                    new Visibility(Visibility::VISIBLE),
                    new ContentTypeIdentifier([
                        'article',
                        'blog_post',
                        'folder',
                    ]),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
            ]),
        ];

        yield 'filter by siteaccess' => [
            [
                'location' => $location,
                'filter' => [
                    'siteaccess_aware' => false,
                ],
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new Subtree(self::EXAMPLE_LOCATION_PATH_STRING),
                    new Visibility(Visibility::VISIBLE),
                ]),
            ]),
        ];

        yield 'limit and offset' => [
            [
                'location' => $location,
                'limit' => 10,
                'offset' => 100,
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new Subtree(self::EXAMPLE_LOCATION_PATH_STRING),
                    new Visibility(Visibility::VISIBLE),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
                'limit' => 10,
                'offset' => 100,
            ]),
        ];

        yield 'basic sort' => [
            [
                'location' => $location,
                'sort' => [
                    'target' => 'Location\Priority',
                ],
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new Subtree(self::EXAMPLE_LOCATION_PATH_STRING),
                    new Visibility(Visibility::VISIBLE),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
                'sortClauses' => [
                    new Priority(Query::SORT_ASC),
                ],
            ]),
        ];

        yield 'sort by custom clause' => [
            [
                'location' => $location,
                'sort' => [
                    'target' => '\eZ\Publish\Core\QueryType\BuildIn\Tests\CustomSortClause',
                    'direction' => 'desc',
                    'data' => ['foo', 'bar', 'baz'],
                ],
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new Subtree(self::EXAMPLE_LOCATION_PATH_STRING),
                    new Visibility(Visibility::VISIBLE),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
                'sortClauses' => [
                    new CustomSortClause('foo', 'bar', 'baz', Query::SORT_DESC),
                ],
            ]),
        ];
    }

    protected function createQueryType(Repository $repository, ConfigResolverInterface $configResolver): QueryType
    {
        return new SubtreeQueryType($repository, $configResolver);
    }

    protected function getExpectedName(): string
    {
        return 'eZ:Subtree';
    }

    protected function getExpectedSupportedParameters(): array
    {
        return ['filter', 'offset', 'limit', 'sort', 'location', 'content', 'depth'];
    }
}
