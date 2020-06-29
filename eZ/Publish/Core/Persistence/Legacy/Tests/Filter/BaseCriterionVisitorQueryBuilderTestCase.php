<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Filter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Filter\CriterionVisitor;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;
use PHPUnit\Framework\TestCase;

abstract class BaseCriterionVisitorQueryBuilderTestCase extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Filter\CriterionVisitor */
    private $criterionVisitor;

    /**
     * @return \eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder[]
     */
    abstract protected function getCriterionQueryBuilders(): iterable;

    /**
     * Data provider for {@see testVisitCriteriaProducesQuery}.
     */
    abstract public function getFilteringCriteriaQueryData(): iterable;

    protected function setUp(): void
    {
        $this->criterionVisitor = new CriterionVisitor([]);
        $this->criterionVisitor->setCriterionQueryBuilders(
            array_merge(
                $this->getBaseCriterionQueryBuilders($this->criterionVisitor),
                $this->getCriterionQueryBuilders()
            )
        );
    }

    /**
     * @dataProvider getFilteringCriteriaQueryData
     *
     * @covers \eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder::buildQueryConstraint
     * @covers \eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder::accepts
     * @covers \eZ\Publish\Core\Persistence\Legacy\Filter\CriterionVisitor::visitCriteria
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    public function testVisitCriteriaProducesQuery(
        FilteringCriterion $criterion,
        string $expectedQuery,
        array $expectedParameterValues
    ): void {
        $queryBuilder = $this->getQueryBuilder();
        $actualQuery = $this->criterionVisitor->visitCriteria($queryBuilder, $criterion);
        $criterionFQCN = get_class($criterion);
        self::assertSame(
            $expectedQuery,
            $actualQuery,
            sprintf(
                'Query Builder for %s Criterion does not produce expected query',
                $criterionFQCN
            )
        );
        self::assertSame(
            $expectedParameterValues,
            $queryBuilder->getParameters(),
            sprintf(
                'Query Builder for %s Criterion does not bind expected query parameter values',
                $criterionFQCN
            )
        );
    }

    private function getQueryBuilder(): FilteringQueryBuilder
    {
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock
            ->method('getExpressionBuilder')
            ->willReturn(
                new ExpressionBuilder($connectionMock)
            );

        return new FilteringQueryBuilder($connectionMock);
    }

    /**
     * Create Query Builders needed for every test case.
     *
     * @see getCriterionQueryBuilders
     */
    private function getBaseCriterionQueryBuilders(CriterionVisitor $criterionVisitor): iterable
    {
        return [
            new CriterionQueryBuilder\LogicalAndQueryBuilder($criterionVisitor),
            new CriterionQueryBuilder\LogicalOrQueryBuilder($criterionVisitor),
            new CriterionQueryBuilder\LogicalNotQueryBuilder($criterionVisitor),
        ];
    }
}
