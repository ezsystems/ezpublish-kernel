<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\User\PasswordBlacklist;

use eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Gateway;
use eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Handler;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Gateway|\PHPUnit\Framework\MockObject\MockObject */
    private $gateway;

    /** @var \eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Handler */
    private $handler;

    protected function setUp()
    {
        $this->gateway = $this->createMock(Gateway::class);
        $this->handler = new Handler($this->gateway);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Handler::isBlacklisted
     */
    public function testIsBlacklisted()
    {
        $password = 'password';

        $this->gateway
            ->expects($this->once())
            ->method('isBlacklisted')
            ->with($password)
            ->willReturn(true);

        $this->assertTrue($this->handler->isBlacklisted($password));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Handler::removeAll
     */
    public function testRemoveAll()
    {
        $this->gateway
            ->expects($this->once())
            ->method('removeAll');

        $this->handler->removeAll();
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Handler::insert
     */
    public function testInsert()
    {
        $passwords = ['123456', 'qwerty', 'password'];

        $this->gateway
            ->expects($this->once())
            ->method('insert')
            ->with($passwords);

        $this->handler->insert($passwords);
    }
}
