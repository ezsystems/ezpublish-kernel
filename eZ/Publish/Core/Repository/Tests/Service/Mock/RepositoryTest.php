<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\RepositoryTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;

/**
 * Mock test case for Repository
 */
class RepositoryTest extends BaseServiceMockTest
{
    /**
     * Test for the beginTransaction() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::beginTransaction
     */
    public function testBeginTransaction()
    {
        $mockedRepository = $this->getRepository();
        $persistenceHandlerMock = $this->getPersistenceMock();

        $persistenceHandlerMock->expects(
            $this->once()
        )->method(
            "beginTransaction"
        );

        $mockedRepository->beginTransaction();
    }

    /**
     * Test for the commit() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::commit
     */
    public function testCommit()
    {
        $mockedRepository = $this->getRepository();
        $persistenceHandlerMock = $this->getPersistenceMock();

        $persistenceHandlerMock->expects(
            $this->once()
        )->method(
            "commit"
        );

        $mockedRepository->commit();
    }

    /**
     * Test for the commit() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::commit
     * @expectedException \RuntimeException
     */
    public function testCommitThrowsRuntimeException()
    {
        $mockedRepository = $this->getRepository();
        $persistenceHandlerMock = $this->getPersistenceMock();

        $persistenceHandlerMock->expects(
            $this->once()
        )->method(
            "commit"
        )->will(
            $this->throwException( new \Exception() )
        );

        $mockedRepository->commit();
    }

    /**
     * Test for the rollback() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::rollback
     */
    public function testRollback()
    {
        $mockedRepository = $this->getRepository();
        $persistenceHandlerMock = $this->getPersistenceMock();

        $persistenceHandlerMock->expects(
            $this->once()
        )->method(
            "rollback"
        );

        $mockedRepository->rollback();
    }

    /**
     * Test for the rollback() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::rollback
     * @expectedException \RuntimeException
     */
    public function testRollbackThrowsRuntimeException()
    {
        $mockedRepository = $this->getRepository();
        $persistenceHandlerMock = $this->getPersistenceMock();

        $persistenceHandlerMock->expects(
            $this->once()
        )->method(
            "rollback"
        )->will(
            $this->throwException( new \Exception() )
        );

        $mockedRepository->rollback();
    }
}
