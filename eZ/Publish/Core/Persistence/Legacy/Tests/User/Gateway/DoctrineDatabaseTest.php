<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\User\Gateway;

use Doctrine\DBAL\ParameterType;
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/roles.php'
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\Gateway\DoctrineDatabase::removeRoleAssignmentById
     */
    public function testRemoveRoleByAssignmentId(): void
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->removeRoleAssignmentById(38);
        $query = $this->getDatabaseConnection()->createQueryBuilder();

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
                ->where(
                    $query->expr()->eq(
                        'role_id',
                        $query->createPositionalParameter(5, ParameterType::INTEGER)
                    )
                )
        );
    }

    /**
     * Returns a ready to test DoctrineDatabase gateway.
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
