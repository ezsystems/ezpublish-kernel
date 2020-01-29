<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\Tests;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\FieldRelation;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentName;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\QueryType\BuildIn\RelatedToContentQueryType;
use eZ\Publish\Core\QueryType\QueryType;

final class RelatedToContentQueryTypeTest extends AbstractQueryTypeTest
{
    private const EXAMPLE_CONTENT_ID = 52;
    private const EXAMPLE_FIELD = 'related';

    public function dataProviderForGetQuery(): iterable
    {
        yield 'basic' => [
            [
                'content' => self::EXAMPLE_CONTENT_ID,
                'field' => self::EXAMPLE_FIELD,
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new FieldRelation(self::EXAMPLE_FIELD, Operator::CONTAINS, self::EXAMPLE_CONTENT_ID),
                    new Visibility(Visibility::VISIBLE),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
            ]),
        ];

        yield 'filter by visibility' => [
            [
                'content' => self::EXAMPLE_CONTENT_ID,
                'field' => self::EXAMPLE_FIELD,
                'filter' => [
                    'visible_only' => false,
                ],
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new FieldRelation(self::EXAMPLE_FIELD, Operator::CONTAINS, self::EXAMPLE_CONTENT_ID),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
            ]),
        ];

        yield 'filter by content type' => [
            [
                'content' => self::EXAMPLE_CONTENT_ID,
                'field' => self::EXAMPLE_FIELD,
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
                    new FieldRelation(self::EXAMPLE_FIELD, Operator::CONTAINS, self::EXAMPLE_CONTENT_ID),
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
                'content' => self::EXAMPLE_CONTENT_ID,
                'field' => self::EXAMPLE_FIELD,
                'filter' => [
                    'siteaccess_aware' => false,
                ],
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new FieldRelation(self::EXAMPLE_FIELD, Operator::CONTAINS, self::EXAMPLE_CONTENT_ID),
                    new Visibility(Visibility::VISIBLE),
                ]),
            ]),
        ];

        yield 'limit and offset' => [
            [
                'content' => self::EXAMPLE_CONTENT_ID,
                'field' => self::EXAMPLE_FIELD,
                'limit' => 10,
                'offset' => 100,
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new FieldRelation(self::EXAMPLE_FIELD, Operator::CONTAINS, self::EXAMPLE_CONTENT_ID),
                    new Visibility(Visibility::VISIBLE),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
                'limit' => 10,
                'offset' => 100,
            ]),
        ];

        yield 'basic sort' => [
            [
                'content' => self::EXAMPLE_CONTENT_ID,
                'field' => self::EXAMPLE_FIELD,
                'sort' => [
                    'target' => 'ContentName',
                ],
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new FieldRelation(self::EXAMPLE_FIELD, Operator::CONTAINS, self::EXAMPLE_CONTENT_ID),
                    new Visibility(Visibility::VISIBLE),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
                'sortClauses' => [
                    new ContentName(Query::SORT_ASC),
                ],
            ]),
        ];

        yield 'sort by custom clause' => [
            [
                'content' => self::EXAMPLE_CONTENT_ID,
                'field' => self::EXAMPLE_FIELD,
                'sort' => [
                    'target' => '\eZ\Publish\Core\QueryType\BuildIn\Tests\CustomSortClause',
                    'direction' => 'desc',
                    'data' => ['foo', 'bar', 'baz'],
                ],
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new FieldRelation(self::EXAMPLE_FIELD, Operator::CONTAINS, self::EXAMPLE_CONTENT_ID),
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
        return new RelatedToContentQueryType($repository, $configResolver);
    }

    protected function getExpectedName(): string
    {
        return 'eZ:RelatedToContent';
    }

    protected function getExpectedSupportedParameters(): array
    {
        return ['filter', 'offset', 'limit', 'sort', 'content', 'field'];
    }
}
