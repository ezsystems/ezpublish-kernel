<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\TransactionHandler;

/**
 * Test case for Persistence\Cache\TransactionHandler.
 */
class TransactionHandlerTest extends AbstractCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'transactionHandler';
    }

    public function getHandlerClassName(): string
    {
        return TransactionHandler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tags, string? $key
        return [
            ['beginTransaction', []],
            ['commit', []],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        // string $method, array $arguments, string $key, mixed? $data
        return [
        ];
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\TransactionHandler::rollback
     */
    public function testRollback()
    {
        $this->loggerMock
            ->expects($this->once())
            ->method('logCall');

        $this->cacheMock
            ->expects($this->once())
            ->method('clear');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\TransactionHandler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('transactionHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('rollback');

        $handler = $this->persistenceCacheHandler->transactionHandler();
        $handler->rollback();
    }
}
