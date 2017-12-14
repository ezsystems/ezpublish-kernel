<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\URL\Query\CriterionHandler;

use eZ\Publish\API\Repository\Values\URL\Query\Criterion\VisibleOnly;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\Expression;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriteriaConverter;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler\VisibleOnly as VisibleOnlyHandler;

class VisibleOnlyTest extends CriterionHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function testAccept()
    {
        $handler = new VisibleOnlyHandler();

        $this->assertHandlerAcceptsCriterion($handler, VisibleOnly::class);
        $this->assertHandlerRejectsCriterion($handler, Criterion::class);
    }

    /**
     * {@inheritdoc}
     */
    public function testHandle()
    {
        $criterion = new VisibleOnly();
        $expected = 'ezurl.id IN (SUBQUERY)';

        $expr = $this->createMock(Expression::class);
        $expr
            ->expects($this->once())
            ->method('in')
            ->with('ezurl.id', '(SUBQUERY)')
            ->willReturn($expected);

        $query = $this->createMock(SelectQuery::class);
        $query->expr = $expr;

        $converter = $this->createMock(CriteriaConverter::class);

        $handler = $this
            ->getMockBuilder(VisibleOnlyHandler::class)
            ->setMethods(['getVisibleOnlySubQuery'])
            ->getMock();
        $handler
            ->expects($this->once())
            ->method('getVisibleOnlySubQuery')
            ->with($query)
            ->willReturn('(SUBQUERY)');

        $actual = $handler->handle($converter, $query, $criterion);

        $this->assertEquals($expected, $actual);
    }
}
