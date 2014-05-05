<?php
/**
 * File contains Test class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests;

/**
 * Test case for Persistence\Cache\TransactionHandler
 */
class TransactionHandlerTest extends HandlerTest
{
    /**
     * @covers eZ\Publish\Core\Persistence\Cache\TransactionHandler::beginTransaction
     */
    public function testBeginTransaction()
    {
        $this->loggerMock
            ->expects( $this->once() )
            ->method( 'logCall' );

        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $this->transactionHandlerMock
            ->expects( $this->once() )
            ->method( 'beginTransaction' );

        $handler = $this->persistenceCacheHandler->transactionHandler();
        $handler->beginTransaction();
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\TransactionHandler::commit
     */
    public function testCommit()
    {
        $this->loggerMock
            ->expects( $this->once() )
            ->method( 'logCall' );

        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $this->transactionHandlerMock
            ->expects( $this->once() )
            ->method( 'commit' );

        $handler = $this->persistenceCacheHandler->transactionHandler();
        $handler->commit();
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\TransactionHandler::rollback
     */
    public function testRollback()
    {
        $this->loggerMock
            ->expects( $this->once() )
            ->method( 'logCall' );

        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' );

        $this->transactionHandlerMock
            ->expects( $this->once() )
            ->method( 'rollback' );

        $handler = $this->persistenceCacheHandler->transactionHandler();
        $handler->rollback();
    }
}
