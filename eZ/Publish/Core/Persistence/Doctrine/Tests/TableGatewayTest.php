<?php

namespace eZ\Publish\Core\Persistence\Doctrine\Tests;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Doctrine\TableMetadata;
use eZ\Publish\Core\Persistence\Doctrine\TableGateway;

use Doctrine\DBAL\DriverManager;

class TableGatewayTest extends TestCase
{
    private $connection;
    private $table;

    public function getDoctrineConnection()
    {
        return DriverManager::getConnection(
            array(
                'pdo' => $this->handler->getDbHandler()
            )
        );
    }

    public function setUp()
    {
        parent::setUp();

        $this->connection = $this->getDoctrineConnection();

        $metadata = new TableMetadata( 'ezcontentclassgroup', 'ezcontentclassgroup_s' );
        $metadata->addColumn( 'created', 'integer' );
        $metadata->addColumn( 'creator_id', 'integer' );
        $metadata->addColumn( 'modified', 'integer' );
        $metadata->addColumn( 'modifier_id', 'integer' );
        $metadata->addColumn( 'id', 'integer' );
        $metadata->addColumn( 'name', 'string' );

        $this->table = new TableGateway( $this->connection, $metadata );
    }

    public function testInsertData()
    {
        $id = $this->table->insert(
            array(
                'created'     => time(),
                'creator_id'  => 1,
                'modified'    => time(),
                'modifier_id' => 1,
                'name'        => 'Test',
            )
        );

        $this->assertTrue( is_numeric( $id ) );
        $this->assertTrue( $id > 0 );

        $stmt = $this->selectStatement( $id );

        $this->assertEquals( 'Test', $stmt->fetchColumn() );
    }

    public function testUpdateData()
    {
        $id = $this->table->insert(
            array(
                'created'     => time(),
                'creator_id'  => 1,
                'modified'    => time(),
                'modifier_id' => 1,
                'name'        => 'Test',
            )
        );

        $this->table->update( array( 'name' => 'Test2' ), array( 'id' => $id ) );

        $stmt = $this->selectStatement( $id );

        $this->assertEquals( 'Test2', $stmt->fetchColumn() );
    }

    private function selectStatement( $id )
    {
        return $this->connection->createQueryBuilder()
                ->select( 'name' )
                ->from( 'ezcontentclassgroup', 'ccg' )
                ->where( 'ccg.id = :id' )
                ->setParameter( 'id', $id )
                ->execute();
    }

    public function testDeleteRow()
    {
        $id = $this->table->insert(
            array(
                'created'     => time(),
                'creator_id'  => 1,
                'modified'    => time(),
                'modifier_id' => 1,
                'name'        => 'Test',
            )
        );

        $this->table->delete( array( 'id' => $id ) );

        $stmt = $this->selectStatement( $id );

        $this->assertFalse( $stmt->fetchColumn() );
    }
}

