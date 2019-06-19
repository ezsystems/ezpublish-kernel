<?php

/**
 * File containing the FacettedSearchesProvider trait.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Common;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * Provider for facet tests against SearchService.
 *
 * Depends on:
 * - class const: QUERY_CLASS
 * - method: getFixtureDir
 *
 * @see \eZ\Publish\API\Repository\Tests\SearchServiceTest
 * @see \eZ\Publish\API\Repository\Tests\SearchServiceLocationTest
 */
trait FacetedSearchProvider
{
    /**
     * @return array[] Each array in the array supports 3 arguments: query, fixture, closure  (optional)
     */
    public function getFacetedSearches()
    {
        $queryClass = static::QUERY_CLASS;
        $fixtureDir = $this->getFixtureDir();

        return [
            [
                new $queryClass(
                    [
                        'filter' => new Criterion\SectionId([1]),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => [
                            new FacetBuilder\ContentTypeFacetBuilder(
                                [
                                    'name' => 'type',
                                ]
                            ),
                        ],
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                ),
                $fixtureDir . '/FacetContentType.php',
            ],
            [
                new $queryClass(
                    [
                        'filter' => new Criterion\SectionId([1]),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => [
                            new FacetBuilder\ContentTypeFacetBuilder(
                                [
                                    'name' => 'type',
                                    'minCount' => 3,
                                ]
                            ),
                        ],
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                ),
                $fixtureDir . '/FacetContentTypeMinCount.php',
            ],
            [
                new $queryClass(
                    [
                        'filter' => new Criterion\SectionId([1]),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => [
                            new FacetBuilder\ContentTypeFacetBuilder(
                                [
                                    'name' => 'type',
                                    'limit' => 5,
                                ]
                            ),
                        ],
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                ),
                $fixtureDir . '/FacetContentTypeMinLimit.php',
            ],
            [
                new $queryClass(
                    [
                        'filter' => new Criterion\SectionId([1]),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => [
                            new FacetBuilder\SectionFacetBuilder(
                                [
                                    'name' => 'section',
                                ]
                            ),
                        ],
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                ),
                $fixtureDir . '/FacetSection.php',
            ],
            [
                new $queryClass(
                    [
                        'filter' => new Criterion\SectionId([1]),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => [
                            new FacetBuilder\UserFacetBuilder(
                                [
                                    'name' => 'creator',
                                ]
                            ),
                        ],
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                ),
                $fixtureDir . '/FacetUser.php',
            ],
            [
                new $queryClass(
                    [
                        'filter' => new Criterion\SectionId([1]),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => [
                            new FacetBuilder\TermFacetBuilder(),
                        ],
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                ),
                $fixtureDir . '/FacetTerm.php',
            ],
            /* @todo: It needs to be defined how this one is supposed to work.
            array(
                new $queryClass(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\CriterionFacetBuilder()
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetCriterion.php',
            ), // */
            /* @todo: Add sane ranges here:
            array(
                new $queryClass(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\DateRangeFacetBuilder( array() )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetDateRange.php',
            ), // */
            [
                new $queryClass(
                    [
                        'filter' => new Criterion\SectionId([1]),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => [
                            new FacetBuilder\FieldFacetBuilder(
                                [
                                    'fieldPaths' => ['article/title'],
                                ]
                            ),
                        ],
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                ),
                $fixtureDir . '/FacetFieldSimple.php',
            ],
            [
                new $queryClass(
                    [
                        'filter' => new Criterion\SectionId([1]),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => [
                            new FacetBuilder\FieldFacetBuilder(
                                [
                                    'fieldPaths' => ['article/title'],
                                    'regex' => '(a|b|c)',
                                ]
                            ),
                        ],
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                ),
                $fixtureDir . '/FacetFieldRegexp.php',
            ],
            [
                new $queryClass(
                    [
                        'filter' => new Criterion\SectionId([1]),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => [
                            new FacetBuilder\FieldFacetBuilder(
                                [
                                    'fieldPaths' => ['article/title'],
                                    'regex' => '(a|b|c)',
                                    'sort' => FacetBuilder\FieldFacetBuilder::TERM_DESC,
                                ]
                            ),
                        ],
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                ),
                $fixtureDir . '/FacetFieldRegexpSortTerm.php',
            ],
            [
                new $queryClass(
                    [
                        'filter' => new Criterion\SectionId([1]),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => [
                            new FacetBuilder\FieldFacetBuilder(
                                [
                                    'fieldPaths' => ['article/title'],
                                    'regex' => '(a|b|c)',
                                    'sort' => FacetBuilder\FieldFacetBuilder::COUNT_DESC,
                                ]
                            ),
                        ],
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                ),
                $fixtureDir . '/FacetFieldRegexpSortCount.php',
            ],
            /* @todo: Add sane ranges here:
            array(
                new $queryClass(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\FieldRangeFacetBuilder( array(
                                'fieldPath' => 'product/price',
                            ) )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetFieldRegexpSortCount.php',
            ), // */
        ];
    }
}
