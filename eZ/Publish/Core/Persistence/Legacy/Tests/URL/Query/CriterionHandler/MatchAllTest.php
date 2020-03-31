<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\URL\Query\CriterionHandler;

use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion\MatchAll;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriteriaConverter;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler\MatchAll as MatchAllHandler;

class MatchAllTest extends CriterionHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function testAccept()
    {
        $handler = new MatchAllHandler();

        $this->assertHandlerAcceptsCriterion($handler, MatchAll::class);
        $this->assertHandlerRejectsCriterion($handler, Criterion::class);
    }

    /**
     * {@inheritdoc}
     */
    public function testHandle()
    {
        $criterion = new MatchAll();
        $expected = '1 = 1';

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $converter = $this->createMock(CriteriaConverter::class);

        $handler = new MatchAllHandler();
        $actual = $handler->handle($converter, $queryBuilder, $criterion);

        $this->assertEquals($expected, $actual);
    }
}
