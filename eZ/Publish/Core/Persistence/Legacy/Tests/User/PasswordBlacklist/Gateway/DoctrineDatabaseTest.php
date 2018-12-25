<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\User\PasswordBlacklist\Gateway;

use Doctrine\DBAL\FetchMode;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Gateway\DoctrineDatabase;

class DoctrineDatabaseTest extends TestCase
{
    /**
     * Inserts DB fixture.
     */
    public function setUp()
    {
        parent::setUp();

        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/passwordblacklist.php'
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Gateway\DoctrineDatabase::isBlacklisted
     */
    public function testIsBookmarked()
    {
        $this->assertTrue($this->getGateway()->isBlacklisted('publish'));
        $this->assertFalse($this->getGateway()->isBlacklisted('H@xi0R!'));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Gateway\DoctrineDatabase::removeAll
     */
    public function testRemoveAll()
    {
        $this->getGateway()->removeAll();

        $this->assertEquals(0, (int)$this->connection->executeQuery(
            'SELECT COUNT(*) FROM ezpasswordblacklist'
        )->fetchColumn());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Gateway\DoctrineDatabase::removeAll
     */
    public function testInsert()
    {
        $passwords = ['publish', 'publish', 'abc123', '111111', 'master'];

        $this->getGateway()->insert($passwords);

        $actualResult = $this->connection->executeQuery(
            'SELECT password FROM ezpasswordblacklist ORDER BY password ASC'
        )->fetchAll(FetchMode::COLUMN);

        $this->assertEquals([
            '111111',
            '123456',
            'abc123',
            'master',
            'publish',
            'qwerty',
        ], $actualResult);
    }

    /**
     * Return a ready to test DoctrineStorage gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Gateway\DoctrineDatabase
     */
    protected function getGateway(): DoctrineDatabase
    {
        return new DoctrineDatabase(
            $this->getDatabaseHandler()->getConnection()
        );
    }
}
