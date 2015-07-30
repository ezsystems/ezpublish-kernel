<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\TransactionHandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests;

use eZ\Publish\Core\Persistence\Legacy\TransactionHandler;
use PHPUnit_Framework_TestCase;
use Exception;

/**
 * Test case for TransactionHandler.
 */
class TransactionHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Transaction handler to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\TransactionHandler
     */
    protected $transactionHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dbHandlerMock;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeHandlerMock;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $languageHandlerMock;

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\TransactionHandler::__construct
     */
    public function testConstruct()
    {
        $handler = $this->getTransactionHandler();

        $this->assertAttributeSame(
            $this->getDatabaseHandlerMock(),
            'dbHandler',
            $handler
        );
        $this->assertAttributeSame(
            $this->getContentTypeHandlerMock(),
            'contentTypeHandler',
            $handler
        );
        $this->assertAttributeSame(
            $this->getLanguageHandlerMock(),
            'languageHandler',
            $handler
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\TransactionHandler::beginTransaction
     */
    public function testBeginTransaction()
    {
        $handler = $this->getTransactionHandler();
        $this->getDatabaseHandlerMock()
            ->expects($this->once())
            ->method('beginTransaction');
        $this->getContentTypeHandlerMock()
            ->expects($this->never())
            ->method($this->anything());
        $this->getLanguageHandlerMock()
            ->expects($this->never())
            ->method($this->anything());

        $handler->beginTransaction();
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\TransactionHandler::commit
     */
    public function testCommit()
    {
        $handler = $this->getTransactionHandler();
        $this->getDatabaseHandlerMock()
            ->expects($this->once())
            ->method('commit');
        $this->getContentTypeHandlerMock()
            ->expects($this->never())
            ->method($this->anything());
        $this->getLanguageHandlerMock()
            ->expects($this->never())
            ->method($this->anything());

        $handler->commit();
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\TransactionHandler::commit
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage test
     */
    public function testCommitException()
    {
        $handler = $this->getTransactionHandler();
        $this->getDatabaseHandlerMock()
            ->expects($this->once())
            ->method('commit')
            ->will($this->throwException(new Exception('test')));
        $this->getContentTypeHandlerMock()
            ->expects($this->never())
            ->method($this->anything());
        $this->getLanguageHandlerMock()
            ->expects($this->never())
            ->method($this->anything());

        $handler->commit();
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\TransactionHandler::rollback
     */
    public function testRollback()
    {
        $handler = $this->getTransactionHandler();
        $this->getDatabaseHandlerMock()
            ->expects($this->once())
            ->method('rollback');
        $this->getContentTypeHandlerMock()
            ->expects($this->once())
            ->method('clearCache');
        $this->getLanguageHandlerMock()
            ->expects($this->once())
            ->method('clearCache');

        $handler->rollback();
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\TransactionHandler::rollback
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage test
     */
    public function testRollbackException()
    {
        $handler = $this->getTransactionHandler();
        $this->getDatabaseHandlerMock()
            ->expects($this->once())
            ->method('rollback')
            ->will($this->throwException(new Exception('test')));
        $this->getContentTypeHandlerMock()
            ->expects($this->never())
            ->method($this->anything());
        $this->getLanguageHandlerMock()
            ->expects($this->never())
            ->method($this->anything());

        $handler->rollback();
    }

    /**
     * Returns a mock object for the Content Gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\TransactionHandler
     */
    protected function getTransactionHandler()
    {
        if (!isset($this->transactionHandler)) {
            $this->transactionHandler = new TransactionHandler(
                $this->getDatabaseHandlerMock(),
                $this->getContentTypeHandlerMock(),
                $this->getLanguageHandlerMock()
            );
        }

        return $this->transactionHandler;
    }

    /**
     * Returns a mock object for the Content Gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDatabaseHandlerMock()
    {
        if (!isset($this->dbHandlerMock)) {
            $this->dbHandlerMock = $this->getMockForAbstractClass(
                'eZ\\Publish\\Core\\Persistence\\Database\\DatabaseHandler'
            );
        }

        return $this->dbHandlerMock;
    }

    /**
     * Returns a mock object for the Content Type Handler.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\MemoryCachingHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContentTypeHandlerMock()
    {
        if (!isset($this->contentTypeHandlerMock)) {
            $this->contentTypeHandlerMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\MemoryCachingHandler',
                array(),
                array(),
                '',
                false
            );
        }

        return $this->contentTypeHandlerMock;
    }

    /**
     * Returns a mock object for the Content Language Gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLanguageHandlerMock()
    {
        if (!isset($this->languageHandlerMock)) {
            $this->languageHandlerMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\CachingHandler',
                array(),
                array(),
                '',
                false
            );
        }

        return $this->languageHandlerMock;
    }
}
