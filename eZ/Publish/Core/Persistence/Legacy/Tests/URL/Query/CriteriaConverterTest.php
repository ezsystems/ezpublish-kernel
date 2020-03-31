<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\URL\Query;

use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriteriaConverter;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler;
use PHPUnit\Framework\TestCase;

class CriteriaConverterTest extends TestCase
{
    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    public function testConvertCriteriaSuccess(): void
    {
        $fooCriterionHandler = $this->createMock(CriterionHandler::class);
        $barCriterionHandler = $this->createMock(CriterionHandler::class);

        $criteriaConverter = new CriteriaConverter([
            $fooCriterionHandler,
            $barCriterionHandler,
        ]);

        $barCriterion = $this->createMock(Criterion::class);

        $selectQuery = $this->createMock(QueryBuilder::class);

        $fooCriterionHandler
            ->expects($this->once())
            ->method('accept')
            ->with($barCriterion)
            ->willReturn(false);

        $fooCriterionHandler
            ->expects($this->never())
            ->method('handle');

        $barCriterionHandler
            ->expects($this->once())
            ->method('accept')
            ->with($barCriterion)
            ->willReturn(true);

        $sqlExpression = 'SQL EXPRESSION';
        $barCriterionHandler
            ->expects($this->once())
            ->method('handle')
            ->with($criteriaConverter, $selectQuery, $barCriterion)
            ->willReturn($sqlExpression);

        $this->assertEquals(
            $sqlExpression,
            $criteriaConverter->convertCriteria(
                $selectQuery,
                $barCriterion
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\URL\Query\CriteriaConverter::convertCriteria
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    public function testConvertCriteriaFailure(): void
    {
        $this->expectException(NotImplementedException::class);

        $criteriaConverter = new CriteriaConverter();
        $criteriaConverter->convertCriteria(
            $this->createMock(QueryBuilder::class),
            $this->createMock(Criterion::class)
        );
    }
}
