<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Notification\Gateway;

use Doctrine\DBAL\FetchMode;
use eZ\Publish\Core\Persistence\Legacy\Notification\Gateway\DoctrineDatabase;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\SPI\Persistence\Notification\CreateStruct;
use eZ\Publish\SPI\Persistence\Notification\Notification;

class DoctrineDatabaseTest extends TestCase
{
    const EXISTING_NOTIFICATION_ID = 1;
    const EXISTING_NOTIFICATION_DATA = [
        'id' => 1,
        'owner_id' => 14,
        'is_pending' => 1,
        'type' => 'Workflow:Review',
        'created' => 1529995052,
        'data' => null,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/notifications.php'
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway\DoctrineDatabase::insert()
     */
    public function testInsert()
    {
        $id = $this->getGateway()->insert(new CreateStruct([
            'ownerId' => 14,
            'isPending' => true,
            'type' => 'Workflow:Review',
            'created' => 1529995052,
            'data' => null,
        ]));

        $data = $this->loadNotification($id);

        $this->assertEquals([
            'id' => $id,
            'owner_id' => '14',
            'is_pending' => 1,
            'type' => 'Workflow:Review',
            'created' => '1529995052',
            'data' => 'null',
        ], $data);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway\DoctrineDatabase::getNotificationById()
     */
    public function testGetNotificationById()
    {
        $data = $this->getGateway()->getNotificationById(self::EXISTING_NOTIFICATION_ID);

        $this->assertEquals([
            self::EXISTING_NOTIFICATION_DATA,
        ], $data);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway\DoctrineDatabase::updateNotification()
     */
    public function testUpdateNotification()
    {
        $notification = new Notification([
            'id' => self::EXISTING_NOTIFICATION_ID,
            'ownerId' => 14,
            'isPending' => false,
            'type' => 'Workflow:Review',
            'created' => 1529995052,
            'data' => null,
        ]);

        $this->getGateway()->updateNotification($notification);

        $this->assertEquals([
            'id' => (string) self::EXISTING_NOTIFICATION_ID,
            'owner_id' => '14',
            'is_pending' => '0',
            'type' => 'Workflow:Review',
            'created' => '1529995052',
            'data' => null,
        ], $this->loadNotification(self::EXISTING_NOTIFICATION_ID));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway\DoctrineDatabase::countUserNotifications()
     */
    public function testCountUserNotifications()
    {
        $this->assertEquals(5, $this->getGateway()->countUserNotifications(
            self::EXISTING_NOTIFICATION_DATA['owner_id']
        ));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway\DoctrineDatabase::countUserPendingNotifications()
     */
    public function testCountUserPendingNotifications()
    {
        $this->assertEquals(3, $this->getGateway()->countUserPendingNotifications(
            self::EXISTING_NOTIFICATION_DATA['owner_id'])
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway\DoctrineDatabase::loadUserNotifications()
     */
    public function testLoadUserNotifications()
    {
        $userId = 14;
        $offset = 1;
        $limit = 3;

        $results = $this->getGateway()->loadUserNotifications($userId, $offset, $limit);

        $this->assertEquals([
            [
                'id' => '4',
                'owner_id' => '14',
                'is_pending' => 1,
                'type' => 'Workflow:Review',
                'created' => '1530005852',
                'data' => null,
            ],
            [
                'id' => '3',
                'owner_id' => '14',
                'is_pending' => 0,
                'type' => 'Workflow:Reject',
                'created' => '1530002252',
                'data' => null,
            ],
            [
                'id' => '2',
                'owner_id' => '14',
                'is_pending' => 0,
                'type' => 'Workflow:Approve',
                'created' => '1529998652',
                'data' => null,
            ],
        ], $results);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway\DoctrineDatabase::delete()
     */
    public function testDelete()
    {
        $this->getGateway()->delete(self::EXISTING_NOTIFICATION_ID);

        $this->assertEmpty($this->loadNotification(self::EXISTING_NOTIFICATION_ID));
    }

    /**
     * Return a ready to test DoctrineStorage gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway\DoctrineDatabase
     */
    protected function getGateway(): DoctrineDatabase
    {
        return new DoctrineDatabase(
            $this->getDatabaseConnection()
        );
    }

    private function loadNotification(int $id): array
    {
        $data = $this->connection
            ->executeQuery('SELECT * FROM eznotification WHERE id = :id', ['id' => $id])
            ->fetch(FetchMode::ASSOCIATIVE);

        return is_array($data) ? $data : [];
    }
}
