<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\User\PasswordBlacklist\Gateway;

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
