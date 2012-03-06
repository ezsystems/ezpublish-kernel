<?php
/**
 * File containing the TrashServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the TrashService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\TrashService
 */
class TrashServiceTest extends BaseTest
{

    /**
     * Test for the loadTrashItem() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::loadTrashItem()
     * @depends eZ\Publish\
     */
    public function testLoadTrashItem()
    {
        $this->markTestIncomplete( "@TODO: Test for TrashService::loadTrashItem() is not implemented." );
    }

    /**
     * Test for the loadTrashItem() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::loadTrashItem()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadTrashItemThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for TrashService::loadTrashItem() is not implemented." );
    }

    /**
     * Test for the loadTrashItem() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::loadTrashItem()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadTrashItemThrowsNotFoundException()
    {
        $this->markTestIncomplete( "@TODO: Test for TrashService::loadTrashItem() is not implemented." );
    }

    /**
     * Test for the trash() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::trash()
     * 
     */
    public function testTrash()
    {
        $this->markTestIncomplete( "@TODO: Test for TrashService::trash() is not implemented." );
    }

    /**
     * Test for the trash() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::trash()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testTrashThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for TrashService::trash() is not implemented." );
    }

    /**
     * Test for the recover() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::recover()
     * 
     */
    public function testRecover()
    {
        $this->markTestIncomplete( "@TODO: Test for TrashService::recover() is not implemented." );
    }

    /**
     * Test for the recover() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::recover($trashItem, $newParentLocation)
     * 
     */
    public function testRecoverWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for TrashService::recover() is not implemented." );
    }

    /**
     * Test for the recover() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::recover()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testRecoverThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for TrashService::recover() is not implemented." );
    }

    /**
     * Test for the recover() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::recover($trashItem, $newParentLocation)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testRecoverThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for TrashService::recover() is not implemented." );
    }

    /**
     * Test for the emptyTrash() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::emptyTrash()
     * 
     */
    public function testEmptyTrash()
    {
        $this->markTestIncomplete( "@TODO: Test for TrashService::emptyTrash() is not implemented." );
    }

    /**
     * Test for the emptyTrash() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::emptyTrash()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testEmptyTrashThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for TrashService::emptyTrash() is not implemented." );
    }

    /**
     * Test for the deleteTrashItem() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::deleteTrashItem()
     * 
     */
    public function testDeleteTrashItem()
    {
        $this->markTestIncomplete( "@TODO: Test for TrashService::deleteTrashItem() is not implemented." );
    }

    /**
     * Test for the deleteTrashItem() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::deleteTrashItem()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteTrashItemThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for TrashService::deleteTrashItem() is not implemented." );
    }

    /**
     * Test for the findTrashItems() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::findTrashItems()
     * 
     */
    public function testFindTrashItems()
    {
        $this->markTestIncomplete( "@TODO: Test for TrashService::findTrashItems() is not implemented." );
    }

}
