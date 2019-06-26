<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\UserPreference;

use eZ\Publish\Core\Persistence\Legacy\UserPreference\Gateway;
use eZ\Publish\Core\Persistence\Legacy\UserPreference\Mapper;
use eZ\Publish\Core\Persistence\Legacy\UserPreference\Handler;
use eZ\Publish\SPI\Persistence\UserPreference\UserPreferenceSetStruct;
use eZ\Publish\SPI\Persistence\UserPreference\UserPreference;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    const USER_PREFERENCE_ID = 1;

    /** @var \eZ\Publish\Core\Persistence\Legacy\UserPreference\Gateway|\PHPUnit\Framework\MockObject\MockObject */
    private $gateway;

    /** @var \eZ\Publish\Core\Persistence\Legacy\UserPreference\Mapper|\PHPUnit\Framework\MockObject\MockObject */
    private $mapper;

    /** @var \eZ\Publish\Core\Persistence\Legacy\UserPreference\Handler */
    private $handler;

    protected function setUp()
    {
        $this->gateway = $this->createMock(Gateway::class);
        $this->mapper = $this->createMock(Mapper::class);
        $this->handler = new Handler($this->gateway, $this->mapper);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\UserPreference\Handler::setUserPreference()
     */
    public function testSetUserPreference()
    {
        $setStruct = new UserPreferenceSetStruct([
            'userId' => 5,
            'name' => 'setting',
            'value' => 'value',
        ]);

        $this->gateway
            ->expects($this->once())
            ->method('setUserPreference')
            ->with($setStruct)
            ->willReturn(self::USER_PREFERENCE_ID);

        $this->mapper
            ->expects($this->once())
            ->method('extractUserPreferencesFromRows')
            ->willReturn([new UserPreference([
                'id' => self::USER_PREFERENCE_ID,
            ])]);

        $userPreference = $this->handler->setUserPreference($setStruct);

        $this->assertEquals($userPreference->id, self::USER_PREFERENCE_ID);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\UserPreference\Handler::countUserPreferences
     */
    public function testCountUserPreferences()
    {
        $ownerId = 10;
        $expectedCount = 12;

        $this->gateway
            ->expects($this->once())
            ->method('countUserPreferences')
            ->with($ownerId)
            ->willReturn($expectedCount);

        $this->assertEquals($expectedCount, $this->handler->countUserPreferences($ownerId));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\UserPreference\Handler::loadUserPreferences
     */
    public function testLoadUserPreferences()
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
            new UserPreference(['id' => 1/* ... */]),
            new UserPreference(['id' => 2/* ... */]),
            new UserPreference(['id' => 3/* ... */]),
        ];

        $this->gateway
            ->expects($this->once())
            ->method('loadUserPreferences')
            ->with($ownerId, $offset, $limit)
            ->willReturn($rows);

        $this->mapper
            ->expects($this->once())
            ->method('extractUserPreferencesFromRows')
            ->with($rows)
            ->willReturn($objects);

        $this->assertEquals($objects, $this->handler->loadUserPreferences($ownerId, $offset, $limit));
    }
}
