<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

/**
 * Test case for Persistence\Cache\TransactionHandler.
 */
class TransactionHandlerTest extends HandlerTest
{
    /**
     * @covers eZ\Publish\Core\Persistence\Cache\TransactionHandler::beginTransaction
     */
    public function testBeginTransaction()
    {
        $this->loggerMock
            ->expects($this->once())
            ->method('logCall');

        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\TransactionHandler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('transactionHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('beginTransaction');

        $handler = $this->persistenceCacheHandler->transactionHandler();
        $handler->beginTransaction();
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\TransactionHandler::commit
     */
    public function testCommit()
    {
        $this->loggerMock
            ->expects($this->once())
            ->method('logCall');

        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\TransactionHandler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('transactionHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('commit');

        $handler = $this->persistenceCacheHandler->transactionHandler();
        $handler->commit();
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\TransactionHandler::rollback
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
