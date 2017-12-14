<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\URL\Query\CriterionHandler;

use eZ\Publish\API\Repository\Values\URL\Query\Criterion\Validity;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\Expression;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
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

        $expr = $this->createMock(Expression::class);
        $expr
            ->expects($this->once())
            ->method('eq')
            ->with('is_valid', ':is_valid')
            ->willReturn($expected);

        $query = $this->createMock(SelectQuery::class);
        $query->expr = $expr;
        $query
            ->expects($this->once())
            ->method('bindValue')
            ->with($criterion->isValid)
            ->willReturn(':is_valid');

        $converter = $this->createMock(CriteriaConverter::class);

        $handler = new ValidityHandler();
        $actual = $handler->handle($converter, $query, $criterion);

        $this->assertEquals($expected, $actual);
    }
}
