<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\User\Role\Gateway\DoctrineDatabaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\User\Role\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway\DoctrineDatabase;
use eZ\Publish\SPI\Persistence\User\Role;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway\DoctrineDatabase.
 */
class DoctrineDatabaseTest extends TestCase
{
    /**
     * Database gateway to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway\DoctrineDatabase
     */
    protected $databaseGateway;

    /**
     * Inserts DB fixture.
     */
    public function setUp()
    {
        parent::setUp();

        $this->insertDatabaseFixture(
            __DIR__ . '/../../_fixtures/roles.php'
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway\DoctrineDatabase::__construct
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
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway\DoctrineDatabase::createRole
     */
    public function testCreateRole()
    {
        $gateway = $this->getDatabaseGateway();

        $spiRole = new Role([
            'identifier' => 'new_role',
            'status' => Role::STATUS_DRAFT,
        ]);
        $gateway->createRole($spiRole);
        $query = $this->getDatabaseHandler()->createSelectQuery();

        $this->assertQueryResult(
            [
                [
                    'id' => '6',
                    'name' => 'new_role',
                    'version' => -1,
                ],
            ],
            $query
                ->select('id', 'name', 'version')
                ->from('ezrole')
                ->where($query->expr->eq('name', $query->bindValue('new_role')))
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway\DoctrineDatabase::loadRoleAssignment
     */
    public function testLoadRoleAssignment()
    {
        $gateway = $this->getDatabaseGateway();

        $this->assertEquals(
            [
                [
                    'contentobject_id' => '12',
                    'id' => '25',
                    'limit_identifier' => '',
                    'limit_value' => '',
                    'role_id' => '2',
                ],
            ],
            $gateway->loadRoleAssignment(25)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway\DoctrineDatabase::loadRoleAssignmentsByGroupId
     */
    public function testLoadRoleAssignmentsByGroupId()
    {
        $gateway = $this->getDatabaseGateway();

        $this->assertEquals(
            [
                [
                    'contentobject_id' => '11',
                    'id' => '28',
                    'limit_identifier' => '',
                    'limit_value' => '',
                    'role_id' => '1',
                ],
                [
                    'contentobject_id' => '11',
                    'id' => '34',
                    'limit_identifier' => '',
                    'limit_value' => '',
                    'role_id' => '5',
                ],
                [
                    'contentobject_id' => '11',
                    'id' => '40',
                    'limit_identifier' => 'Section',
                    'limit_value' => '3',
                    'role_id' => '4',
                ],
            ],
            $gateway->loadRoleAssignmentsByGroupId(11)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway\DoctrineDatabase::loadRoleAssignmentsByRoleId
     */
    public function testLoadRoleAssignmentsByRoleId()
    {
        $gateway = $this->getDatabaseGateway();

        $this->assertEquals(
            [
                [
                    'contentobject_id' => '11',
                    'id' => '28',
                    'limit_identifier' => '',
                    'limit_value' => '',
                    'role_id' => '1',
                ],
                [
                    'contentobject_id' => '42',
                    'id' => '31',
                    'limit_identifier' => '',
                    'limit_value' => '',
                    'role_id' => '1',
                ],
                [
                    'contentobject_id' => '59',
                    'id' => '37',
                    'limit_identifier' => '',
                    'limit_value' => '',
                    'role_id' => '1',
                ],
            ],
            $gateway->loadRoleAssignmentsByRoleId(1)
        );
    }

    /**
     * Returns a ready to test DoctrineDatabase gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway\DoctrineDatabase
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
