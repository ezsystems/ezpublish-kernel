<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\URL\Query\CriterionHandler;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion\Validity;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriteriaConverter;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler\Validity as ValidityHandler;

class ValidityTest extends CriterionHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function testAccept()
    {
        $handler = new ValidityHandler();

        $this->assertHandlerAcceptsCriterion($handler, Validity::class);
        $this->assertHandlerRejectsCriterion($handler, Criterion::class);
    }

    /**
     * {@inheritdoc}
     */
    public function testHandle()
    {
        $criterion = new Validity(true);
        $expected = 'is_valid = :is_valid';

        $expressionBuilder = $this->createMock(ExpressionBuilder::class);
        $expressionBuilder
            ->expects($this->once())
            ->method('eq')
            ->with('is_valid', ':is_valid')
            ->willReturn($expected);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder
            ->expects($this->any())
            ->method('expr')
            ->willReturn($expressionBuilder);
        $queryBuilder
            ->expects($this->any())
            ->method('createNamedParameter')
            ->with((int)$criterion->isValid, ParameterType::INTEGER, ':is_valid')
            ->willReturn(':is_valid');

        $converter = $this->createMock(CriteriaConverter::class);

        $handler = new ValidityHandler();
        $actual = $handler->handle($converter, $queryBuilder, $criterion);

        $this->assertEquals($expected, $actual);
    }
}
