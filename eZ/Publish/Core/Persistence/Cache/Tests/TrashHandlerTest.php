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

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content\Location\Trashed as SPITrashed;

/**
 * Test case for Persistence\Cache\SectionHandler.
 */
class TrashHandlerTest extends HandlerTest
{
    /**
     * @covers eZ\Publish\Core\Persistence\Cache\TrashHandler::loadTrashItem
     */
    public function testLoadTrashItem()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Trash\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('trashHandler')
            ->will($this->returnValue($innerHandlerMock));

        $trashed = new SPITrashed(
            array('id' => 33, 'contentId' => 31)
        );

        $innerHandlerMock
            ->expects($this->once())
            ->method('loadTrashItem')
            ->with(33)
            ->will(
                $this->returnValue(
                    $trashed
                )
            );

        $handler = $this->persistenceCacheHandler->trashHandler();
        $this->assertSame($trashed, $handler->loadTrashItem(33));
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\TrashHandler::trashSubtree
     */
    public function testTrashSubtree()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Trash\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('trashHandler')
            ->will($this->returnValue($innerHandlerMock));

        $trashed = new SPITrashed(
            array('id' => 33, 'contentId' => 31)
        );

        $innerHandlerMock
            ->expects($this->once())
            ->method('trashSubtree')
            ->with(33)
            ->will(
                $this->returnValue($trashed)
            );

        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('location')
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('content')
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(2))
            ->method('clear')
            ->with('user', 'role', 'assignments', 'byGroup')
            ->will($this->returnValue(true));

        $handler = $this->persistenceCacheHandler->trashHandler();
        $this->assertSame($trashed, $handler->trashSubtree(33));
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\TrashHandler::recover
     */
    public function testRecover()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Trash\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('trashHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('recover')
            ->with(33, 66)
            ->will(
                $this->returnValue(99)
            );

        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('location', 'subtree')
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('content')
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(2))
            ->method('clear')
            ->with('user', 'role', 'assignments', 'byGroup')
            ->will($this->returnValue(true));

        $handler = $this->persistenceCacheHandler->trashHandler();
        $this->assertEquals(99, $handler->recover(33, 66));
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\TrashHandler::findTrashItems
     */
    public function testFindTrashItems()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Trash\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('trashHandler')
            ->will($this->returnValue($innerHandlerMock));

        $criterion = new Criterion\ContentId(33);

        $innerHandlerMock
            ->expects($this->once())
            ->method('findTrashItems')
            ->with($criterion, 10, 11, array())
            ->will(
                $this->returnValue(array())
            );

        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $handler = $this->persistenceCacheHandler->trashHandler();
        $this->assertEquals(array(), $handler->findTrashItems($criterion, 10, 11, array()));
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\TrashHandler::emptyTrash
     */
    public function testEmptyTrash()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Trash\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('trashHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('emptyTrash')
            ->with();

        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $handler = $this->persistenceCacheHandler->trashHandler();
        $handler->emptyTrash();
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\TrashHandler::deleteTrashItem
     */
    public function testDeleteTrashItem()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Trash\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('trashHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('deleteTrashItem')
            ->with(33);

        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $handler = $this->persistenceCacheHandler->trashHandler();
        $handler->deleteTrashItem(33);
    }
}
