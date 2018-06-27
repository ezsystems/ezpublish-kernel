<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Notification;

use eZ\Publish\API\Repository\Values\Notification\Notification as APINotification;
use eZ\Publish\Core\Persistence\Legacy\Notification\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Notification\Mapper;
use eZ\Publish\Core\Persistence\Legacy\Notification\Handler;
use eZ\Publish\SPI\Persistence\Notification\CreateStruct;
use eZ\Publish\SPI\Persistence\Notification\Notification;
use eZ\Publish\SPI\Persistence\Notification\UpdateStruct;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    const NOTIFICATION_ID = 1;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway|\PHPUnit\Framework\MockObject\MockObject */
    private $gateway;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Notification\Mapper|\PHPUnit\Framework\MockObject\MockObject */
    private $mapper;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Notification\Handler */
    private $handler;

    protected function setUp()
    {
        $this->gateway = $this->createMock(Gateway::class);
        $this->mapper = $this->createMock(Mapper::class);
        $this->handler = new Handler($this->gateway, $this->mapper);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Handler::createNotification
     */
    public function testCreateNotification()
    {
        $createStruct = new CreateStruct([
            'ownerId' => 5,
            'type' => 'TEST',
            'isPending' => true,
            'data' => [],
            'created' => 0,
        ]);

        $this->gateway
            ->expects($this->once())
            ->method('insert')
            ->with($createStruct)
            ->willReturn(self::NOTIFICATION_ID);

        $this->mapper
            ->expects($this->once())
            ->method('extractNotificationsFromRows')
            ->willReturn([new Notification([
                'id' => self::NOTIFICATION_ID,
            ])]);

        $notification = $this->handler->createNotification($createStruct);

        $this->assertEquals($notification->id, self::NOTIFICATION_ID);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Handler::countPendingNotifications
     */
    public function testCountPendingNotifications()
    {
        $ownerId = 10;
        $expectedCount = 12;

        $this->gateway
            ->expects($this->once())
            ->method('countUserPendingNotifications')
            ->with($ownerId)
            ->willReturn($expectedCount);

        $this->assertEquals($expectedCount, $this->handler->countPendingNotifications($ownerId));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Handler::getNotificationById
     */
    public function testGetNotificationById()
    {
        $rows = [
            [
                'id' => 1, /* ... */
            ],
        ];

        $object = new Notification([
            'id' => 1, /* ... */
        ]);

        $this->gateway
            ->expects($this->once())
            ->method('getNotificationById')
            ->with(self::NOTIFICATION_ID)
            ->willReturn($rows);

        $this->mapper
            ->expects($this->once())
            ->method('extractNotificationsFromRows')
            ->with($rows)
            ->willReturn([$object]);

        $this->assertEquals($object, $this->handler->getNotificationById(self::NOTIFICATION_ID));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Handler::updateNotification
     */
    public function testUpdateNotification()
    {
        $updateStruct = new UpdateStruct([
            'isPending' => false,
        ]);

        $data = [
            'id' => self::NOTIFICATION_ID,
            'ownerId' => null,
            'isPending' => true,
            'type' => null,
            'created' => null,
            'data' => [],
        ];

        $apiNotification = new APINotification($data);
        $spiNotification = new Notification($data);

        $this->mapper
            ->expects($this->once())
            ->method('createNotificationFromUpdateStruct')
            ->with($updateStruct)
            ->willReturn($spiNotification);

        $this->gateway
            ->expects($this->once())
            ->method('updateNotification')
            ->with($spiNotification);

        $this->mapper
            ->expects($this->once())
            ->method('extractNotificationsFromRows')
            ->willReturn([new Notification([
                'id' => self::NOTIFICATION_ID,
            ])]);

        $this->handler->updateNotification($apiNotification, $updateStruct);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Handler::countNotifications
     */
    public function testCountNotifications()
    {
        $ownerId = 10;
        $expectedCount = 12;

        $this->gateway
            ->expects($this->once())
            ->method('countUserNotifications')
            ->with($ownerId)
            ->willReturn($expectedCount);

        $this->assertEquals($expectedCount, $this->handler->countNotifications($ownerId));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Handler::loadUserNotifications
     */
    public function testLoadUserNotifications()
    {
        $ownerId = 9;
        $limit = 5;
        $offset = 0;

        $rows = [
            ['id' => 1/* ... */],
            ['id' => 2/* ... */],
            ['id' => 3/* ... */],
        ];

        $objects = [
            new Notification(['id' => 1/* ... */]),
            new Notification(['id' => 2/* ... */]),
            new Notification(['id' => 3/* ... */]),
        ];

        $this->gateway
            ->expects($this->once())
            ->method('loadUserNotifications')
            ->with($ownerId, $offset, $limit)
            ->willReturn($rows);

        $this->mapper
            ->expects($this->once())
            ->method('extractNotificationsFromRows')
            ->with($rows)
            ->willReturn($objects);

        $this->assertEquals($objects, $this->handler->loadUserNotifications($ownerId, $offset, $limit));
    }

    public function testDelete()
    {
        $notification = new APINotification([
            'id' => self::NOTIFICATION_ID, /* ... */
        ]);

        $this->gateway
            ->expects($this->once())
            ->method('delete')
            ->with($notification->id);

        $this->handler->delete($notification);
    }
}
