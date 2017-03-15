<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\User\Gateway\DoctrineDatabaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\User\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\User\Gateway\DoctrineDatabase;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\User\Gateway\DoctrineDatabase.
 */
class DoctrineDatabaseTest extends TestCase
{
    /**
     * Database gateway to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\User\Gateway\DoctrineDatabase
     */
    protected $databaseGateway;

    /**
     * Inserts DB fixture.
     */
    public function setUp()
    {
        parent::setUp();

        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/roles.php'
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\Gateway\DoctrineDatabase::__construct
     */
    public function testCtor()
    {
        $handler = $this->getDatabaseHandler();
        $gateway = $this->getDatabaseGateway();

        $this->assertAttributeSame(
            $handler,
            'handler',
            $gateway
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\Gateway\DoctrineDatabase::removeRoleAssignmentById
     */
    public function testRemoveRoleByAssignmentId()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->removeRoleAssignmentById(38);
        $query = $this->getDatabaseHandler()->createSelectQuery();

        $this->assertQueryResult(
            [
                [
                    'contentobject_id' => '11',
                    'id' => '34',
                    'limit_identifier' => '',
                    'limit_value' => '',
                    'role_id' => '5',
                ],
                [
                    'contentobject_id' => '59',
                    'id' => '36',
                    'limit_identifier' => '',
                    'limit_value' => '',
                    'role_id' => '5',
                ],
                [
                    'contentobject_id' => '13',
                    'id' => '39',
                    'limit_identifier' => 'Section',
                    'limit_value' => '2',
                    'role_id' => '5',
                ],
            ],
            $query
                ->select('contentobject_id', 'id', 'limit_identifier', 'limit_value', 'role_id')
                ->from('ezuser_role')
                ->where($query->expr->eq('role_id', $query->bindValue('5')))
        );
    }

    /**
     * Returns a ready to test DoctrineDatabase gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\User\Gateway\DoctrineDatabase
     */
    protected function getDatabaseGateway()
    {
        if (!isset($this->databaseGateway)) {
            $this->databaseGateway = new DoctrineDatabase(
                $this->getDatabaseHandler()
            );
        }

        return $this->databaseGateway;
    }
}
