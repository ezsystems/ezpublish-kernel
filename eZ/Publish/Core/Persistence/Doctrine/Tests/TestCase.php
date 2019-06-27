<?php

namespace eZ\Publish\Core\Persistence\Doctrine\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\DBALException;
use eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler;

abstract class TestCase extends BaseTestCase
{
    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

    /** @var \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler */
    protected $handler;

    public function setUp()
    {
        $dsn = getenv('DATABASE');

        if (!$dsn) {
            $dsn = 'sqlite://:memory:';
        }

        $doctrineParams = ConnectionHandler::parseDSN($dsn);

        $this->connection = DriverManager::getConnection($doctrineParams);
        $this->handler = new ConnectionHandler($this->connection);
    }

    protected function createQueryTestTable()
    {
        $table = new \Doctrine\DBAL\Schema\Table('query_test');
        $table->addColumn('id', 'integer');
        $table->addColumn('val1', 'string');
        $table->addColumn('val2', 'integer');

        try {
            $this->connection->getSchemaManager()->createTable($table);
        } catch (DBALException $e) {
        }
    }
}
