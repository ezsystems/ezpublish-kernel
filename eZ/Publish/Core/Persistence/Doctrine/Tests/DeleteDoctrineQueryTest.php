<?php

namespace eZ\Publish\Core\Persistence\Doctrine\Tests;

class DeleteDoctrineQueryTest extends TestCase
{
    public function testGenerateDeleteQuery()
    {
        $deleteQuery = $this->handler->createDeleteQuery();

        $deleteQuery->deleteFrom( 'query_test' )->where( 'foo = bar' );

        $this->assertEquals( 'DELETE FROM query_test WHERE foo = bar', (string)$deleteQuery );
    }

    public function testExceptionWithoutTable()
    {
        $deleteQuery = $this->handler->createDeleteQuery();

        $this->setExpectedException( 'eZ\Publish\Core\Persistence\Database\QueryException' );

        $deleteQuery->getQuery();
    }
}
