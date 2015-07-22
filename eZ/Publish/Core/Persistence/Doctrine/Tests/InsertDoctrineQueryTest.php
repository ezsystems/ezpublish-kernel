<?php

namespace eZ\Publish\Core\Persistence\Doctrine\Tests;

class InsertDoctrineQueryTest extends TestCase
{
    public function testGenerateInsertQuery()
    {
        $insertQuery = $this->handler->createInsertQuery();

        $insertQuery->insertInto('query_test')
            ->set('val1', '?')
            ->set('val2', 'NULL');

        $this->assertEquals(
            'INSERT INTO query_test (val1, val2) VALUES (?, NULL)',
            $insertQuery->getQuery()
        );
    }
}
