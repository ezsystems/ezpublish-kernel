<?php

namespace eZ\Publish\Core\Persistence\Doctrine\Tests;

class UpdateDoctrineQueryTest extends TestCase
{
    public function testGenerateUpdateQuery()
    {
        $updateQuery = $this->handler->createUpdateQuery();

        $updateQuery->update('query_test')
            ->set('val1', '?')
            ->set('val2', 'NULL')
            ->where('foo = bar');

        $this->assertEquals(
            'UPDATE query_test SET val1 = ?, val2 = NULL WHERE foo = bar',
            (string)$updateQuery
        );
    }

    public function testExceptionWhenNoTableSpecified()
    {
        $updateQuery = $this->handler->createUpdateQuery();

        $this->setExpectedException('eZ\Publish\Core\Persistence\Database\QueryException');

        $updateQuery->getQuery();
    }

    public function testExceptionWhenNoSetSpecified()
    {
        $updateQuery = $this->handler->createUpdateQuery();

        $updateQuery->update('query_test');

        $this->setExpectedException('eZ\Publish\Core\Persistence\Database\QueryException');

        $updateQuery->getQuery();
    }

    public function testExceptionWhenNoWhereSpecified()
    {
        $updateQuery = $this->handler->createUpdateQuery();

        $updateQuery->update('query_test')->set('val1', '?');

        $this->setExpectedException('eZ\Publish\Core\Persistence\Database\QueryException');

        $updateQuery->getQuery();
    }
}
