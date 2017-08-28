<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;

/**
 * This test will try to execute search queries that might be interpreted as "pure negative"
 * by the search backend and hence produce incorrect results.
 *
 * @group regression
 */
class PureNegativeQueryTest extends BaseTest
{
    public function providerForTestMatchAll()
    {
        $query = new Query(['filter' => new Criterion\Matcher\MatchAll()]);
        $result = $this->getRepository()->getSearchService()->findContent($query);
        // Sanity check
        $this->assertGreaterThan(0, $result->totalCount);
        $totalCount = $result->totalCount;
        $contentId = 12;

        return [
            [
                new Criterion\LogicalOperator\LogicalOr(
                    [
                        new Criterion\Matcher\ContentId($contentId),
                        new Criterion\Matcher\MatchNone(),
                    ]
                ),
                1,
            ],
            [
                new Criterion\LogicalOperator\LogicalAnd(
                    [
                        new Criterion\Matcher\ContentId($contentId),
                        new Criterion\Matcher\MatchNone(),
                    ]
                ),
                0,
            ],
            [
                new Criterion\LogicalOperator\LogicalOr(
                    [
                        new Criterion\Matcher\ContentId($contentId),
                        new Criterion\LogicalOperator\LogicalNot(
                            new Criterion\Matcher\MatchAll()
                        ),
                    ]
                ),
                1,
            ],
            [
                new Criterion\LogicalOperator\LogicalAnd(
                    [
                        new Criterion\Matcher\ContentId($contentId),
                        new Criterion\LogicalOperator\LogicalNot(
                            new Criterion\Matcher\MatchAll()
                        ),
                    ]
                ),
                0,
            ],
            [
                new Criterion\LogicalOperator\LogicalOr(
                    [
                        new Criterion\Matcher\ContentId($contentId),
                        new Criterion\Matcher\MatchAll(),
                    ]
                ),
                $totalCount,
            ],
            [
                new Criterion\LogicalOperator\LogicalAnd(
                    [
                        new Criterion\Matcher\ContentId($contentId),
                        new Criterion\Matcher\MatchAll(),
                    ]
                ),
                1,
            ],
            [
                new Criterion\LogicalOperator\LogicalOr(
                    [
                        new Criterion\Matcher\MatchAll(),
                        new Criterion\Matcher\MatchNone(),
                    ]
                ),
                $totalCount,
            ],
            [
                new Criterion\LogicalOperator\LogicalAnd(
                    [
                        new Criterion\Matcher\MatchAll(),
                        new Criterion\Matcher\MatchNone(),
                    ]
                ),
                0,
            ],
            [
                new Criterion\LogicalOperator\LogicalOr(
                    [
                        new Criterion\Matcher\ContentId($contentId),
                        new Criterion\LogicalOperator\LogicalNot(
                            new Criterion\Matcher\ContentId($contentId)
                        ),
                    ]
                ),
                $totalCount,
            ],
            [
                new Criterion\LogicalOperator\LogicalOr(
                    [
                        new Criterion\Matcher\ContentId($contentId),
                        new Criterion\LogicalOperator\LogicalNot(
                            new Criterion\LogicalOperator\LogicalNot(
                                new Criterion\Matcher\ContentId($contentId)
                            )
                        ),
                    ]
                ),
                1,
            ],
            [
                new Criterion\LogicalOperator\LogicalOr(
                    [
                        new Criterion\LogicalOperator\LogicalNot(
                            new Criterion\Matcher\ContentId($contentId)
                        ),
                        new Criterion\LogicalOperator\LogicalNot(
                            new Criterion\LogicalOperator\LogicalNot(
                                new Criterion\Matcher\ContentId($contentId)
                            )
                        ),
                    ]
                ),
                $totalCount,
            ],
            [
                new Criterion\LogicalOperator\LogicalOr(
                    [
                        new Criterion\LogicalOperator\LogicalNot(
                            new Criterion\Matcher\ContentId($contentId)
                        ),
                        new Criterion\LogicalOperator\LogicalNot(
                            new Criterion\Matcher\ContentId($contentId)
                        ),
                    ]
                ),
                $totalCount - 1,
            ],
            [
                new Criterion\LogicalOperator\LogicalAnd(
                    [
                        new Criterion\Matcher\ContentId($contentId),
                        new Criterion\LogicalOperator\LogicalNot(
                            new Criterion\Matcher\ContentId($contentId)
                        ),
                    ]
                ),
                0,
            ],
            [
                new Criterion\LogicalOperator\LogicalAnd(
                    [
                        new Criterion\Matcher\ContentId($contentId),
                        new Criterion\LogicalOperator\LogicalNot(
                            new Criterion\LogicalOperator\LogicalNot(
                                new Criterion\Matcher\ContentId($contentId)
                            )
                        ),
                    ]
                ),
                1,
            ],
            [
                new Criterion\LogicalOperator\LogicalAnd(
                    [
                        new Criterion\LogicalOperator\LogicalNot(
                            new Criterion\Matcher\ContentId($contentId)
                        ),
                        new Criterion\LogicalOperator\LogicalNot(
                            new Criterion\LogicalOperator\LogicalNot(
                                new Criterion\Matcher\ContentId($contentId)
                            )
                        ),
                    ]
                ),
                0,
            ],
            [
                new Criterion\LogicalOperator\LogicalAnd(
                    [
                        new Criterion\LogicalOperator\LogicalNot(
                            new Criterion\Matcher\ContentId($contentId)
                        ),
                        new Criterion\LogicalOperator\LogicalNot(
                            new Criterion\Matcher\ContentId($contentId)
                        ),
                    ]
                ),
                $totalCount - 1,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestMatchAll
     *
     * @param CriterionInterface $criterion
     * @param int $totalCount
     */
    public function testMatchAllContentInfoQuery($criterion, $totalCount)
    {
        $query = new Query(
            [
                'query' => $criterion,
            ]
        );

        $result = $this->getRepository()->getSearchService()->findContentInfo($query);

        $this->assertEquals($totalCount, $result->totalCount);
    }

    /**
     * @dataProvider providerForTestMatchAll
     *
     * @param CriterionInterface $criterion
     * @param int $totalCount
     */
    public function testMatchAllContentInfoFilter($criterion, $totalCount)
    {
        $query = new Query(
            [
                'filter' => $criterion,
            ]
        );

        $result = $this->getRepository()->getSearchService()->findContentInfo($query);

        $this->assertEquals($totalCount, $result->totalCount);
    }

    /**
     * @dataProvider providerForTestMatchAll
     *
     * @param CriterionInterface $criterion
     * @param int $totalCount
     */
    public function testMatchAllLocationQuery($criterion, $totalCount)
    {
        $query = new LocationQuery(
            [
                'query' => $criterion,
            ]
        );

        $result = $this->getRepository()->getSearchService()->findLocations($query);

        $this->assertEquals($totalCount, $result->totalCount);
    }

    /**
     * @dataProvider providerForTestMatchAll
     *
     * @param CriterionInterface $criterion
     * @param int $totalCount
     */
    public function testMatchAllLocationFilter($criterion, $totalCount)
    {
        $query = new LocationQuery(
            [
                'filter' => $criterion,
            ]
        );

        $result = $this->getRepository()->getSearchService()->findLocations($query);

        $this->assertEquals($totalCount, $result->totalCount);
    }
}
