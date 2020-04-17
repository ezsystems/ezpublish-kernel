<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Doctrine\Tests;

use eZ\Publish\Core\Persistence\Doctrine\DoctrineExpression;

class DoctrineExpressionTest extends TestCase
{
    public function testLOr()
    {
        $expression = new DoctrineExpression($this->connection);

        $this->assertEquals('( 1 AND 1 )', $expression->lAnd('1', '1'));
    }
}
