<?php

namespace eZ\Publish\Core\Persistence\Doctrine\Tests;

use eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler;
use PHPUnit\Framework\TestCase;

class ConnectionHandlerTest extends TestCase
{
    /**
     * @dataProvider dataDsn
     */
    public function testParseDSN($expected, $dsn)
    {
        $this->assertEquals($expected, ConnectionHandler::parseDSN($dsn));
    }

    /**
     * @return array
     */
    public static function dataDsn()
    {
        return array(
            array(
                array(
                    'driver' => 'pdo_pgsql',
                    'user' => 'postgres',
                    'host' => 'localhost',
                    'dbname' => 'eztest',
                ),
                'pgsql://postgres@localhost/eztest',
            ),
            array(
                array(
                    'driver' => 'pdo_mysql',
                    'user' => 'root',
                    'host' => 'localhost',
                    'dbname' => 'eztest',
                ),
                'mysql://root@localhost/eztest',
            ),
            array(
                array(
                    'driver' => 'pdo_sqlite',
                    'memory' => true,
                ),
                'sqlite://:memory:',
            ),
            array(
                array(
                    'driver' => 'pdo_sqlite',
                    'path' => 'foo.db',
                ),
                'sqlite:///foo.db',
            ),
        );
    }

    public function testSqliteConnectionSubtype()
    {
        $handler = ConnectionHandler::createFromDSN('sqlite://:memory:');

        $this->assertInstanceOf(ConnectionHandler\SqliteConnectionHandler::class, $handler);
    }
}
