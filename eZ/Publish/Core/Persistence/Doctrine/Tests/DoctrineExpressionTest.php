<?php

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
