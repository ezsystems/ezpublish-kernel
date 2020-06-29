<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Tests\Persistence\Filter\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use PHPUnit\Framework\TestCase;

class FilteringQueryBuilderTest extends TestCase
{
    /** @var \eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder */
    private $queryBuilder;

    protected function setUp(): void
    {
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->method('getExpressionBuilder')->willReturn(
            new ExpressionBuilder($connectionMock)
        );
        $this->queryBuilder = new FilteringQueryBuilder($connectionMock);
    }

    /**
     * @covers \eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder::joinOnce
     */
    public function testJoinOnce(): void
    {
        $this->queryBuilder
            ->select('f.id')->from('foo', 'f')
            ->joinOnce('f', 'bar', 'b', 'f.id = b.foo_id');

        $expr = $this->queryBuilder->expr();
        // should not be joined again
        $this->queryBuilder->joinOnce('f', 'bar', 'b', $expr->eq('f.id', 'b.foo_id'));
        // can be joined
        $this->queryBuilder->joinOnce('f', 'bar', 'b2', $expr->eq('f.id', 'b2.foo_id'));

        self::assertSame(
            'SELECT f.id FROM foo f ' .
            'INNER JOIN bar b ON f.id = b.foo_id ' .
            'INNER JOIN bar b2 ON f.id = b2.foo_id',
            $this->queryBuilder->getSQL()
        );
    }

    public function testJoinOnceThrowsDatabaseError(): void
    {
        $this
            ->queryBuilder
            ->select('f.id')->from('foo', 'f')
            ->joinOnce('f', 'bar', 'b', 'f.id = b.foo_id');

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/^FilteringQueryBuilder: .*f.id = b.foo_id/');

        // different condition should cause Runtime DatabaseException as automatic error recovery is not possible
        $this->queryBuilder->joinOnce('f', 'bar', 'b', 'f.bar_id = b.id');
    }
}
