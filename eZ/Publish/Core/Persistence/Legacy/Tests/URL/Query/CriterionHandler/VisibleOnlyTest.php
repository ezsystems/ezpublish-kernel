<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\URL\Query\CriterionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriteriaConverter;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler\VisibleOnly as VisibleOnlyHandler;

class VisibleOnlyTest extends CriterionHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function testAccept(): void
    {
        $handler = new VisibleOnlyHandler();

        $this->assertHandlerAcceptsCriterion($handler, Criterion\VisibleOnly::class);
        $this->assertHandlerRejectsCriterion($handler, Criterion::class);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler\VisibleOnly::handle
     *
     * Note: more complex case with multiple Criteria trying to join the same table multiple times
     * has been covered by integration tests.
     */
    public function testHandle(): void
    {
        $expected = 't.is_invisible = :location_is_invisible';
        $expectedQueryParameters = ['location_is_invisible' => 0];

        $criterion = new Criterion\VisibleOnly();
        $handler = new VisibleOnlyHandler();
        $converter = $this->createMock(CriteriaConverter::class);
        $queryBuilder = $this->createDoctrineQueryBuilder();

        $actual = $handler->handle($converter, $queryBuilder, $criterion);
        $this->assertEquals($expected, $actual);
        $this->assertSame($expectedQueryParameters, $queryBuilder->getParameters());
    }

    /**
     * Instantiate 3rd party ExpressionBuilder and QueryBuilder with Connection mock.
     *
     * NOTE: This is not the safest approach (all 3rd party classes should be mocked),
     * but it's the quickest way to avoid complex mocks out of test scope.
     */
    private function createDoctrineQueryBuilder(): QueryBuilder
    {
        $connection = $this->createMock(Connection::class);

        $expressionBuilder = new ExpressionBuilder($connection);
        $connection
            ->expects($this->any())
            ->method('getExpressionBuilder')
            ->willReturn($expressionBuilder);

        return new QueryBuilder($connection);
    }
}
