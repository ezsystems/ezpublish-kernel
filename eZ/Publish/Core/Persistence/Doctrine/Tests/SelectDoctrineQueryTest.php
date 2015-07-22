<?php

namespace eZ\Publish\Core\Persistence\Doctrine\Tests;

class SelectDoctrineQueryTest extends TestCase
{
    public function testSimpleSelect()
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            'val1',
            'val2'
        )->from(
            'query_test'
        );

        $this->assertEquals('SELECT val1, val2 FROM query_test', $query->getQuery());
    }

    public function testSelectWithWhereClause()
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            'val1',
            'val2'
        )->from(
            'query_test'
        )->where(
            'foo = bar',
            'bar = baz'
        );

        $this->assertEquals(
            'SELECT val1, val2 FROM query_test WHERE foo = bar AND bar = baz',
            $query->getQuery()
        );
    }

    public function testSelectWithMultipleFromsAndJoins()
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            '*'
        )->from(
            'query_test'
        )->innerJoin(
            'query_inner',
            'qtid',
            'qiid'
        )->from(
            'second_from'
        )->leftJoin(
            'second_inner',
            'sfid',
            'siid'
        );

        $this->assertEquals(
            'SELECT * FROM query_test INNER JOIN query_inner ON qtid = qiid, second_from LEFT JOIN second_inner ON sfid = siid',
            $query->getQuery()
        );
    }

    public function testSelectDistinct()
    {
        $query = $this->handler->createSelectQuery();
        $query->selectDistinct('val1', 'val2')->from('query_test');

        $this->assertEquals('SELECT DISTINCT val1, val2 FROM query_test', $query->getQuery());
    }

    public function testSelectGroupByHaving()
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            '*'
        )->from(
            'query_test'
        )->groupBy(
            'id'
        )->having(
            'foo = bar'
        );

        $this->assertEquals('SELECT * FROM query_test GROUP BY id HAVING foo = bar', $query->getQuery());
    }

    public function testLimitGeneration()
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            '*'
        )->from(
            'query_test'
        );

        $sql = (string)$query;
        $query->limit(10, 10);

        $limitSql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 10, 10);

        $this->assertEquals($limitSql, (string)$query);
    }

    public function testSubselect()
    {
        $query = $this->handler->createSelectQuery();

        $subselect = $query->subSelect();
        $subselect->select(
            '*'
        )->from(
            'query_test'
        );

        $query->select(
            '*'
        )->from(
            $subselect
        );

        $this->assertEquals('SELECT * FROM ( SELECT * FROM query_test )', (string)$query);
    }
}
