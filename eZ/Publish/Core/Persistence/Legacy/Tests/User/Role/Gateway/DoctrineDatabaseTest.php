<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\User\Role\Gateway;

use Doctrine\DBAL\ParameterType;
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
     *
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->insertDatabaseFixture(
            __DIR__ . '/../../_fixtures/roles.php'
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway\DoctrineDatabase::createRole
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testCreateRole(): void
    {
        $gateway = $this->getDatabaseGateway();

        $spiRole = new Role([
            'identifier' => 'new_role',
            'status' => Role::STATUS_DRAFT,
        ]);
        $gateway->createRole($spiRole);
        $query = $this->getDatabaseConnection()->createQueryBuilder();

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
                ->where(
                    $query->expr()->eq(
                        'name',
                        $query->createPositionalParameter('new_role', ParameterType::STRING)
                    )
                )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway\DoctrineDatabase::loadRoleAssignment
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testLoadRoleAssignment(): void
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
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testLoadRoleAssignmentsByGroupId(): void
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
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testLoadRoleAssignmentsByRoleId(): void
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
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getDatabaseGateway(): DoctrineDatabase
    {
        if (!isset($this->databaseGateway)) {
            $this->databaseGateway = new DoctrineDatabase(
                $this->getDatabaseConnection()
            );
        }

        return $this->databaseGateway;
    }
}
